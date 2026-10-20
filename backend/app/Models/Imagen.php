<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Una foto de un recurso (alojamiento, tipo de alojamiento o paquete).
 *
 * Es la única verdad sobre las galerías. Las carpetas públicas y las columnas
 * JSON que había antes quedan como fuentes de las que el comando
 * `imagenes:sincronizar` vuelca filas aquí.
 */
class Imagen extends Model
{
    protected $table = 'imagenes';

    protected $fillable = [
        'recurso_tipo',
        'recurso_id',
        'ruta',
        'orden',
        'es_portada',
        'origen',
        'texto_alt',
    ];

    protected $casts = [
        'orden' => 'integer',
        'es_portada' => 'boolean',
    ];

    /** Dueño de la foto: alojamiento, tipo de alojamiento o paquete. */
    public function recurso(): MorphTo
    {
        return $this->morphTo('recurso', 'recurso_tipo', 'recurso_id');
    }
}
