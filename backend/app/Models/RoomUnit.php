<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomUnit extends Model
{
    use HasFactory;
    use TraduceColumnas;

    protected $table = 'unidades_habitacion';

    protected static $columnas = [
        'accommodation_id' => 'alojamiento_id',
        'name' => 'nombre',
        'unit_type' => 'tipo_unidad',
        'capacity_adults' => 'capacidad_adultos',
        'capacity_children' => 'capacidad_ninos',
        'total_units' => 'total_unidades',
        'base_price_per_night' => 'precio_base_noche',
        'cleaning_fee' => 'tarifa_limpieza',
        'bathroom_type' => 'tipo_bano',
        'bed_distribution' => 'distribucion_camas',
        'amenities' => 'amenidades',
        'status' => 'estado',
    ];

    protected $fillable = [
        'accommodation_id',
        'name',
        'unit_type',
        'capacity_adults',
        'capacity_children',
        'total_units',
        'base_price_per_night',
        'cleaning_fee',
        'bathroom_type',
        'bed_distribution',
        'amenities',
        'status',
    ];

    protected $casts = [
        'distribucion_camas' => 'array',
        'amenidades' => 'array',
        'precio_base_noche' => 'float',
        'tarifa_limpieza' => 'float',
        'capacidad_adultos' => 'integer',
        'capacidad_ninos' => 'integer',
        'total_unidades' => 'integer',
        'estado' => 'boolean',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class, 'alojamiento_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'unidad_habitacion_id');
    }

    /**
     * Check how many units are free between $checkIn and $checkOut dates.
     */
    public function availableUnitsCount(string $checkIn, string $checkOut): int
    {
        // Count active/confirmed reservations overlapping the requested interval:
        // Overlap condition: check_in_date < $checkOut AND check_out_date > $checkIn
        // Ocupan inventario las reservas activas (1) y confirmadas (2); las
        // anuladas (0) y canceladas (-1) lo dejan libre.
        $overlappingReservationsCount = $this->reservations()
            ->whereIn('estado', [1, 2])
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('fecha_entrada', '<', $checkOut)
                      ->where('fecha_salida', '>', $checkIn);
            })
            ->count();

        return max(0, $this->total_units - $overlappingReservationsCount);
    }

    /**
     * Whether at least $quantity units are available for requested dates.
     */
    public function isAvailableForDates(string $checkIn, string $checkOut, int $quantity = 1): bool
    {
        return $this->availableUnitsCount($checkIn, $checkOut) >= $quantity;
    }
}
