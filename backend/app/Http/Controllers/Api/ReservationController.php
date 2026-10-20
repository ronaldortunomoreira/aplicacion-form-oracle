<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\CatalogoPaises;
use App\Services\ReservationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(
        private CatalogoPaises $catalogoPaises,
        private ReservationService $reservationService,
    ) {
    }

    /**
     * Desglose de precio de una estancia, sin crear la reserva.
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_unit_id' => 'required|exists:unidades_habitacion,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults_count' => 'nullable|integer|min:1',
            'children_count' => 'nullable|integer|min:0',
        ]);

        $desglose = $this->reservationService->previewReservation($validated);

        return response()->json([
            'success' => true,
            'data' => $desglose,
        ]);
    }

    /**
     * Confirma una reserva de alojamiento.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_unit_id' => 'required|exists:unidades_habitacion,id',
            'guest_name' => 'required|string|max:150',
            'guest_email' => 'required|email|max:150',
            'guest_phone' => 'required|string|max:50',
            'guest_country_code' => ['required', 'string', Rule::in($this->catalogoPaises->codigos())],
            'guest_document_id' => 'nullable|string|max:50',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults_count' => 'nullable|integer|min:1',
            'children_count' => 'nullable|integer|min:0',
            'payment_method' => 'nullable|in:pay_at_property,credit_card,transfer',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        // El formulario manda el ISO2 (distingue las regiones del +1) y se guarda
        // el prefijo, que es lo que hace falta para marcar el teléfono.
        $iso2 = $validated['guest_country_code'];
        $telefono = $this->catalogoPaises->normalizar($validated['guest_phone'], $iso2);
        if ($telefono === null) {
            throw ValidationException::withMessages([
                'guest_phone' => 'El número de teléfono no es válido para el país seleccionado.',
            ]);
        }

        $validated['guest_phone'] = $telefono;
        $validated['guest_country_code'] = $this->catalogoPaises->dialDe($iso2);

        try {
            $reservation = $this->reservationService->createReservation($validated);
            $reservation = $this->conFotosDelAlojamiento($reservation, $request);

            return response()->json([
                'success' => true,
                'message' => 'Reserva confirmada con éxito!',
                'data' => $reservation,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store and confirm a new travel package reservation.
     */
    public function storePackage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'travel_package_id' => 'required|exists:travel_packages,id',
            'guest_name' => 'required|string|max:150',
            'guest_email' => 'required|email|max:150',
            'guest_phone' => 'required|string|max:50',
            'guest_country_code' => ['required', 'string', Rule::in($this->catalogoPaises->codigos())],
            'guest_document_id' => 'nullable|string|max:50',
            'payment_method' => 'nullable|in:pay_at_property,credit_card,transfer',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $iso2 = $validated['guest_country_code'];
        $telefono = $this->catalogoPaises->normalizar($validated['guest_phone'], $iso2);
        if ($telefono === null) {
            throw ValidationException::withMessages([
                'guest_phone' => 'El número de teléfono no es válido para el país seleccionado.',
            ]);
        }

        $validated['guest_phone'] = $telefono;
        $validated['guest_country_code'] = $this->catalogoPaises->dialDe($iso2);

        try {
            $reservation = $this->reservationService->createPackageReservation($validated);

            return response()->json([
                'success' => true,
                'message' => 'Reserva de paquete confirmada con éxito!',
                'data' => $reservation,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Consulta una reserva por código y correo.
     */
    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'email' => 'required|email',
        ]);

        $reservation = $this->reservationService->findReservation($validated['code'], $validated['email']);

        if ($reservation === null) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ninguna reserva con ese código y correo electrónico.',
            ], 404);
        }

        $reservation = $this->conFotosDelAlojamiento($reservation, $request);

        return response()->json([
            'success' => true,
            'data' => $reservation,
        ]);
    }

    /**
     * Cancela una reserva si el código y el correo coinciden.
     */
    public function cancel(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'reason' => 'nullable|string|max:1000',
        ]);

        $reservation = $this->reservationService->cancelReservation(
            $code,
            $validated['email'],
            $validated['reason'] ?? null,
        );

        if ($reservation === null) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ninguna reserva con ese código y correo electrónico.',
            ], 404);
        }

        $reservation = $this->conFotosDelAlojamiento($reservation, $request);

        return response()->json([
            'success' => true,
            'data' => $reservation,
        ]);
    }

    /**
     * Sustituye las fotos del alojamiento de una reserva por su galería
     * completa, incluidas las de la carpeta pública de su tipo. Así "Mis
     * Reservas" pinta la misma foto que el buscador. Las reservas de paquete no
     * tienen alojamiento, así que se dejan intactas.
     */
    private function conFotosDelAlojamiento(?Reservation $reservation, Request $request): ?Reservation
    {
        $accommodation = $reservation?->roomUnit?->accommodation;

        if ($accommodation) {
            $accommodation->images = $accommodation->galeriaAbsoluta($request->getSchemeAndHttpHost());
        }

        return $reservation;
    }
}
