<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
     * La única verdad sobre la galería es la tabla `imagenes`. La carpeta
     * pública public/images/paquetes/{slug}/ y las columnas `imagen` e
     * `imagenes` quedan como fuentes que el comando `imagenes:sincronizar` vuelca
     * a filas.
     *
     * Si no hay ninguna fila, se cae a esas columnas para no dejar la galería
     * vacía en instalaciones que aún no han sincronizado.
     *
     * Devuelve rutas relativas ("/images/..."); galeriaAbsoluta() las convierte
     * en absolutas con el host real de la petición para que el frontend (que
     * corre en otro puerto) pueda cargarlas.
     *
     * @return array<int, string>
     */
    public function galeria(): array
    {
        $fotos = $this->fotos()
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(fn (Imagen $imagen) => $imagen->ruta)
            ->values()
            ->all();

        if (empty($fotos)) {
            $guardadas = array_merge(array_filter([$this->imagen]), $this->imagenes ?? []);
            $fotos = array_values(array_filter($guardadas, fn ($url) => is_string($url) && $url !== ''));
        }

        return $fotos;
    }

    /** Fotos registradas en la tabla para este paquete. */
    public function fotos(): MorphMany
    {
        return $this->morphMany(Imagen::class, 'recurso', 'recurso_tipo', 'recurso_id', 'id');
    }

    /**
     * Igual que galeria(), pero con las rutas de carpeta ya resueltas a URLs
     * absolutas con el host de la petición. Lo usan el listado y el detalle del
     * paquete por igual, de modo que la foto salga igual en todas las
     * pantallas. Las URLs que ya vienen absolutas (p. ej. de la base de datos)
     * se dejan tal cual.
     *
     * @return array<int, string>
     */
    public function galeriaAbsoluta(string $host): array
    {
        return array_map(
            fn (string $url) => preg_match('#^(https?:)?//#i', $url) ? $url : $host . '/' . ltrim($url, '/'),
            $this->galeria()
        );
    }
}
