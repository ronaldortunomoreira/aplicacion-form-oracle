<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use TraduceColumnas;

    protected $table = 'empresas';

    protected static $columnas = [
        'name' => 'nombre',
        'email' => 'correo',
        'country_code' => 'codigo_pais',
        'detail' => 'detalle',
        // whatsapp ya coincide en ambos idiomas, y tampoco id ni timestamps.
    ];

    protected $fillable = [
        'name',
        'email',
        'country_code',
        'whatsapp',
        'detail',
    ];

    /** Alojamientos que pertenecen a esta empresa. */
    public function alojamientos(): HasMany
    {
        return $this->hasMany(Accommodation::class, 'empresa_id');
    }
}