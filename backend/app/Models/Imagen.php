<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Una foto de un recurso (alojamiento, tipo de alojamiento o paquete).
 *
 * Es la única verdad sobre las galerías, y solo indexa archivos que viven en
 * una carpeta pública: la tabla guarda el nombre del archivo y la ruta se arma
 * al leerla con la carpeta que le corresponde al dueño (ver `carpetaFotos()` en
 * Accommodation, AccommodationType y PaqueteTuristico).
 *
 * Las carpetas se llenan a mano (el administrador arrastra archivos) o con
 * `imagenes:sincronizar`, que además descarga a ellas las URLs que había en las
 * columnas JSON de cada recurso.
 */
class Imagen extends Model
{
    protected $table = 'imagenes';

    protected $fillable = [
        'recurso_tipo',
        'recurso_id',
        'archivo',
        'orden',
        'texto_alt',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    /** Dueño de la foto: alojamiento, tipo de alojamiento o paquete. */
    public function recurso(): MorphTo
    {
        return $this->morphTo('recurso', 'recurso_tipo', 'recurso_id');
    }

    /**
     * Ruta relativa de la foto dentro de la carpeta que se le pase.
     *
     * El nombre va con rawurlencode porque puede traer caracteres que una URL no
     * admite (espacios, acentos); los nombres habituales ("01.jpg") salen igual.
     */
    public function rutaEn(string $carpeta): string
    {
        return rtrim($carpeta, '/') . '/' . rawurlencode($this->archivo);
    }
}
