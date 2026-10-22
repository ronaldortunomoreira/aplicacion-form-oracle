<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;
    use TraduceColumnas;

    protected $table = 'reservas';

    protected static $columnas = [
        'reservation_code' => 'codigo_reserva',
        'room_unit_id' => 'unidad_habitacion_id',
        // Desnormalizado a propósito: sin él habría que cruzar
        // unidades_habitacion -> alojamientos -> paises para saber en qué moneda
        // está el importe o para cualquier informe por alojamiento.
        'accommodation_id' => 'alojamiento_id',
        'guest_name' => 'nombre_huesped',
        'guest_email' => 'correo_huesped',
        'guest_phone' => 'telefono_huesped',
        // Prefijo internacional del teléfono del huésped ("+591"), no ISO2.
        'guest_country_code' => 'codigo_pais',
        'guest_document_id' => 'documento_huesped',
        'check_in_date' => 'fecha_entrada',
        'check_out_date' => 'fecha_salida',
        'adults_count' => 'adultos',
        'children_count' => 'ninos',
        'nights' => 'noches',
        'price_per_night' => 'precio_noche',
        'cleaning_fee' => 'tarifa_limpieza',
        'taxes' => 'impuestos',
        'total_price' => 'precio_total',
        // Copia congelada el día de la reserva. Nullable porque
        // `alojamientos.pais_id` también lo es: un alojamiento sin país deja la
        // reserva sin moneda en vez de inventar una.
        'currency' => 'moneda_iso',
        'payment_method' => 'metodo_pago',
        'payment_status' => 'estado_pago',
        'special_requests' => 'solicitudes_especiales',
        'cancellation_reason' => 'motivo_cancelacion',
        'cancelled_at' => 'cancelada_en',
        'travel_package_id' => 'paquete_turistico_id',
    ];

    protected $fillable = [
        'reservation_code',
        'unidad_habitacion_id',
        'alojamiento_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_country_code',
        'guest_document_id',
        'check_in_date',
        'check_out_date',
        'adults_count',
        'children_count',
        'nights',
        'price_per_night',
        'cleaning_fee',
        'taxes',
        'total_price',
        'currency',
        'payment_method',
        'payment_status',
        'estado',
        'special_requests',
        'cancellation_reason',
        'cancelled_at',
        'travel_package_id',
        'unidad_habitacion_id',
    ];

    protected $casts = [
        'fecha_entrada' => 'date:Y-m-d',
        'fecha_salida' => 'date:Y-m-d',
        'adultos' => 'integer',
        'ninos' => 'integer',
        'noches' => 'integer',
        'precio_noche' => 'float',
        'tarifa_limpieza' => 'float',
        'impuestos' => 'float',
        'precio_total' => 'float',
        'estado' => 'integer',
        'cancelada_en' => 'datetime',
    ];

    public function roomUnit(): BelongsTo
    {
        return $this->belongsTo(RoomUnit::class, 'unidad_habitacion_id');
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class, 'alojamiento_id');
    }

    /**
     * Reserva de paquete turístico: `unidad_habitacion_id`, `alojamiento_id` y
     * las fechas quedan vacías. El paquete fija el precio de una vez por todas.
     */
    public function travelPackage(): BelongsTo
    {
        return $this->belongsTo(PaqueteTuristico::class, 'paquete_turistico_id');
    }

    /** ¿Es una reserva de paquete turístico en vez de de alojamiento? */
    public function esPaquete(): bool
    {
        return $this->travel_package_id !== null;
    }

    /**
     * Generate unique, clean alphanumeric reservation code.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'BK-' . strtoupper(Str::random(7));
        } while (self::where('codigo_reserva', $code)->exists());

        return $code;
    }
}
