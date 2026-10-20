<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccommodationType extends Model
{
    use HasFactory;
    use TraduceColumnas;

    protected $table = 'tipos_alojamiento';

    protected static $columnas = [
        'name' => 'nombre',
        'icon' => 'icono',
        'description' => 'descripcion',
    ];

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
    ];

    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class, 'tipo_alojamiento_id');
    }
}
