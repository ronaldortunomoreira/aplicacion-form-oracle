<?php

namespace App\Services;

use App\Mail\ReservationConfirmation;
use App\Models\RoomUnit;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Reservas de alojamiento y de paquete.
 *
 * El precio se calcula siempre aquí, nunca con datos que llegue a mandar el
 * cliente: el importe, la moneda y el alojamiento se derivan de la unidad de
 * habitación y de su país. Eso evita que una petición a mano fije un precio o
 * una moneda distintos de los que rigen en el alojamiento.
 *
 * Impuestos: un 10% fijo sobre el subtotal (estancia + limpieza), el mismo
 * criterio que usa AccommodationController para el estimado de la ficha. Es una
 * simplificación conocida: cada país tiene su tipo (España 21%, Bolivia 16%) y
 * queda pendiente modelarlo por país.
 */
class ReservationService
{
    /** Tipo de impuesto aplicado al subtotal de la estancia. */
    private const TIPO_IMPUESTO = 0.10;

    /**
     * Desglose de precio para las fechas pedidas, sin guardar nada.
     *
     * Devuelve las mismas claves que pinta el modal de reserva:
     * price_per_night, nights, stay_subtotal, cleaning_fee, taxes y total_price.
     */
    public function previewReservation(array $data): array
    {
        $unit = RoomUnit::with('accommodation.pais')->findOrFail($data['room_unit_id']);

        $this->asegurarDisponibilidad($unit, $data['check_in_date'], $data['check_out_date']);

        return $this->desglose($unit, $data['check_in_date'], $data['check_out_date']);
    }

    /**
     * Crea la reserva con el precio congelado y avisa a la empresa propietaria.
     */
    public function createReservation(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $unit = RoomUnit::with('accommodation.pais')->findOrFail($data['room_unit_id']);

            $this->asegurarDisponibilidad($unit, $data['check_in_date'], $data['check_out_date']);

            $desglose = $this->desglose($unit, $data['check_in_date'], $data['check_out_date']);

            $reservation = new Reservation();
            $reservation->reservation_code = Reservation::generateCode();
            $reservation->room_unit_id = $unit->id;
            // Desnormalizado: la reserva recuerda en qué alojamiento (y por tanto
            // en qué país y moneda) se hizo, aunque la unidad cambie después.
            $reservation->accommodation_id = $unit->accommodation_id;
            $reservation->guest_name = $data['guest_name'];
            $reservation->guest_email = $data['guest_email'];
            $reservation->guest_phone = $data['guest_phone'];
            $reservation->guest_country_code = $data['guest_country_code'];
            $reservation->guest_document_id = $data['guest_document_id'] ?? null;
            $reservation->check_in_date = $data['check_in_date'];
            $reservation->check_out_date = $data['check_out_date'];
            $reservation->adults_count = $data['adults_count'] ?? 1;
            $reservation->children_count = $data['children_count'] ?? 0;
            $reservation->nights = $desglose['nights'];
            $reservation->price_per_night = $desglose['price_per_night'];
            $reservation->cleaning_fee = $desglose['cleaning_fee'];
            $reservation->taxes = $desglose['taxes'];
            $reservation->total_price = $desglose['total_price'];
            // Moneda del país del alojamiento, congelada el día de la reserva.
            $reservation->currency = $unit->accommodation?->currency;
            $reservation->payment_method = $data['payment_method'] ?? 'pay_at_property';
            $reservation->payment_status = 'pending';
            $reservation->estado = 1;
            $reservation->special_requests = $data['special_requests'] ?? null;
            $reservation->save();

            $this->notificarEmpresa($reservation);

            return $reservation->load(['roomUnit.accommodation.accommodationType']);
        });
    }

    /**
     * Store and confirm a new travel package reservation.
     */
    public function createPackageReservation(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $package = \App\Models\PaqueteTuristico::findOrFail($data['travel_package_id']);
            $reservation = new Reservation();
            $reservation->reservation_code = Reservation::generateCode();
            $reservation->travel_package_id = $package->id;
            $reservation->guest_name = $data['guest_name'];
            $reservation->guest_email = $data['guest_email'];
            $reservation->guest_phone = $data['guest_phone'];
            $reservation->guest_country_code = $data['guest_country_code'];
            $reservation->guest_document_id = $data['guest_document_id'] ?? null;
            $reservation->check_in_date = null;
            $reservation->check_out_date = null;
            $reservation->adults_count = $data['adults_count'] ?? 1;
            $reservation->children_count = $data['children_count'] ?? 0;
            $reservation->nights = 0;
            $reservation->price_per_night = (float) $package->precio_desde;
            $reservation->cleaning_fee = 0.00;
            $reservation->taxes = 0.00;
            $reservation->total_price = (float) $package->precio_desde;
            $reservation->currency = $package->moneda_iso ?? 'USD';
            $reservation->payment_method = $data['payment_method'] ?? 'pay_at_property';
            $reservation->payment_status = 'pending';
            $reservation->estado = 1;
            $reservation->special_requests = $data['special_requests'] ?? null;
            $reservation->save();

            try {
                Mail::to($reservation->guest_email)->send(new ReservationConfirmation($reservation));
            } catch (\Throwable $e) {
                // La reserva ya está guardada: un fallo de correo no debe tumbarla.
            }

            return $reservation->load(['roomUnit.accommodation.accommodationType', 'travelPackage']);
        });
    }

    /**
     * Busca una reserva por código y correo, con las relaciones que pinta la
     * página de "Mis Reservas". Devuelve null si no existe.
     */
    public function findReservation(string $code, string $email): ?Reservation
    {
        return Reservation::with([
            'roomUnit.accommodation.accommodationType',
            'roomUnit.accommodation.pais',
            'travelPackage',
        ])
            ->where('codigo_reserva', strtoupper(trim($code)))
            ->where('correo_huesped', $email)
            ->first();
    }

    /**
     * Cancela una reserva si el código y el correo coinciden. Devuelve null si
     * no se encuentra.
     */
    public function cancelReservation(string $code, string $email, ?string $reason = null): ?Reservation
    {
        $reservation = $this->findReservation($code, $email);

        if ($reservation === null) {
            return null;
        }

        $reservation->estado = -1;
        $reservation->cancellation_reason = $reason;
        $reservation->cancelled_at = now();
        $reservation->save();

        return $reservation;
    }

    /**
     * Desglose de precio de una estancia. `stay_subtotal` es solo las noches; la
     * limpieza va aparte y el impuesto se aplica sobre la suma de ambas, igual
     * que el estimado de la ficha del alojamiento.
     */
    private function desglose(RoomUnit $unit, string $checkIn, string $checkOut): array
    {
        $noches = $this->noches($checkIn, $checkOut);
        $precioNoche = (float) $unit->base_price_per_night;
        $subtotal = round($precioNoche * $noches, 2);
        $limpieza = (float) $unit->cleaning_fee;
        $impuestos = round(($subtotal + $limpieza) * self::TIPO_IMPUESTO, 2);
        $total = round($subtotal + $limpieza + $impuestos, 2);

        return [
            'price_per_night' => $precioNoche,
            'nights' => $noches,
            'stay_subtotal' => $subtotal,
            'cleaning_fee' => $limpieza,
            'taxes' => $impuestos,
            'total_price' => $total,
        ];
    }

    /** Número de noches entre dos fechas. Nunca menor que 1. */
    private function noches(string $checkIn, string $checkOut): int
    {
        $entrada = Carbon::parse($checkIn);
        $salida = Carbon::parse($checkOut);

        if (!$salida->gt($entrada)) {
            return 1;
        }

        return (int) $entrada->diffInDays($salida);
    }

    private function asegurarDisponibilidad(RoomUnit $unit, string $checkIn, string $checkOut): void
    {
        if (!$unit->isAvailableForDates($checkIn, $checkOut)) {
            throw ValidationException::withMessages([
                'room_unit_id' => 'No queda disponibilidad para las fechas seleccionadas.',
            ]);
        }
    }

    /**
     * Avisa a la empresa propietaria y manda copia de respaldo a HospedaYa. Si el
     * alojamiento no tiene empresa vinculada, el aviso sale igualmente al
     * respaldo: la reserva no debe perderse por falta de vínculo.
     */
    private function notificarEmpresa(Reservation $reservation): void
    {
        $reservation->loadMissing(['roomUnit.accommodation.empresa', 'roomUnit.accommodation.accommodationType']);

        $empresa = $reservation->roomUnit?->accommodation?->empresa;
        $respaldo = config('mail.hospedaya');

        try {
            if ($empresa && $empresa->email) {
                // Destinatario principal la empresa; HospedaYa recibe copia como
                // respaldo (en el mismo envío, por eso van juntos).
                Mail::to([$empresa->email, $respaldo])->send(new ReservationConfirmation($reservation));
            } else {
                Mail::to($respaldo)->send(new ReservationConfirmation($reservation));
            }
        } catch (\Throwable $e) {
            // La reserva ya está guardada: un fallo de correo no debe tumbarla.
        }
    }
}
