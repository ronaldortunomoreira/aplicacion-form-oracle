<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;

class TravelPackage extends Model
{
    use HasFactory;

    protected $table = "travel_packages";

    protected $fillable = [
        "pais_id",
        "destino_id",
        "nombre",
        "slug",
        "ciudad",
        "texto_presentacion",
        "descripcion",
        "incluye",
        "no_incluye",
        "itinerario",
        "duracion",
        "min_personas",
        "max_personas",
        "precio_desde",
        "moneda_iso",
        "imagen",
        "imagenes",
        "latitud",
        "longitud",
        "destacado",
        "orden",
        "activo",
    ];

    protected $casts = [
        "texto_presentacion" => "array",
        "imagenes" => "array",
        "incluye" => "array",
        "no_incluye" => "array",
        "itinerario" => "array",
        "destacado" => "boolean",
        "activo" => "boolean",
        "precio_desde" => "decimal:2",
        "latitud" => "decimal:8",
        "longitud" => "decimal:11",
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Pais::class, "pais_id");
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, "destino_id");
    }

    public function presentacion($idioma = null)
    {
        if (is_array($this->texto_presentacion)) {
            return $this->texto_presentacion[$idioma] ?? $this->texto_presentacion['es'] ?? null;
        }
        return $this->texto_presentacion;
    }

    public function incluye($idioma = null)
    {
        return is_array($this->incluye) ? $this->incluye : (json_decode($this->incluye, true) ?? []);
    }

    public function noIncluye($idioma = null)
    {
        return is_array($this->no_incluye) ? $this->no_incluye : (json_decode($this->no_incluye, true) ?? []);
    }

    public function itinerario($idioma = null)
    {
        return is_array($this->itinerario) ? $this->itinerario : (json_decode($this->itinerario, true) ?? []);
    }

    public function getIncluyeLista($idioma = null)
    {
        return $this->incluye($idioma);
    }

    public function getNoIncluyeLista($idioma = null)
    {
        return $this->noIncluye($idioma);
    }

    public function getItinerarioLista($idioma = null)
    {
        return $this->itinerario($idioma);
    }

    /**
     * Fotos del paquete turístico.
     *
     * El administrador coloca las fotos en la carpeta pública
     * public/images/paquetes/{slug}/ (basta con arrastrar los archivos). Aquí se
     * listan esas fotos, ordenadas por nombre, y se añaden después la portada
     * (`imagen`) y las URLs ya guardadas en la columna `imagenes`, sin repetir.
     *
     * Devuelve rutas relativas ("/images/..."); el controlador las convierte en
     * absolutas con el host real de la petición para que el frontend (que corre
     * en otro puerto) pueda cargarlas.
     *
     * @return array<int, string>
     */
    public function galeria(): array
    {
        $extensiones = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];
        $fotos = [];

        $carpeta = public_path('images/paquetes/' . $this->slug);
        if (File::isDirectory($carpeta)) {
            $fotos = collect(File::files($carpeta))
                ->filter(fn ($archivo) => in_array(strtolower($archivo->getExtension()), $extensiones, true))
                ->sortBy(fn ($archivo) => $archivo->getFilename(), SORT_NATURAL | SORT_FLAG_CASE)
                ->map(fn ($archivo) => '/images/paquetes/' . $this->slug . '/' . rawurlencode($archivo->getFilename()))
                ->values()
                ->all();
        }

        $guardadas = array_merge(
            array_filter([$this->imagen]),
            $this->imagenes ?? []
        );

        foreach ($guardadas as $url) {
            if (is_string($url) && $url !== '' && ! in_array($url, $fotos, true)) {
                $fotos[] = $url;
            }
        }

        return $fotos;
    }
}
