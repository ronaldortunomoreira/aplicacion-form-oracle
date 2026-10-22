<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use TraduceColumnas;

    protected $table = 'empresas';

    protected static $columnas = [
        'name' => 'nombre',
        'company_name' => 'nombre_empresa',
        'email' => 'correo',
        'country_code' => 'codigo_pais',
        'detail' => 'detalle',
        'status' => 'estado',
        // whatsapp ya coincide en ambos idiomas, y tampoco id ni timestamps.
    ];

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'country_code',
        'whatsapp',
        'detail',
        'pais_id',
        'status',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * País de la empresa, de el que después dependen sus paquetes.
     *
     * Nullable a propósito: el registro acepta el ISO2 de cualquier región del
     * mundo, y `paises` solo tiene los países publicables del proyecto. Una
     * empresa de un país no publicable no tiene fila a la que apuntar.
     */
    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    /** Alojamientos que pertenecen a esta empresa. */
    public function alojamientos(): HasMany
    {
        return $this->hasMany(Accommodation::class, 'empresa_id');
    }

    /** Paquetes turísticos que publica esta empresa. */
    public function paquetes(): HasMany
    {
        return $this->hasMany(PaqueteTuristico::class, 'empresa_id');
    }
}