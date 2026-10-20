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
        "empresa_id",
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

    /**
     * Empresa que publica el paquete.
     *
     * Es obligatoria: la columna no admite null, así que no se publica un tour
     * sin una empresa detrás, y la base de datos no deja borrar una empresa que
     * tenga paquetes (RESTRICT).
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, "empresa_id");
    }

    /**
     * País del paquete, que no es suyo: lo hereda de la empresa que lo publica.
     *
     * Una empresa de un país no publicable no tiene país, y su paquete tampoco,
     * así que este valor puede ser null.
     *
     * Se llama como método, `$paquete->paisDeEmpresa()`, y no como atributo:
     * Eloquent trata cualquier método sin argumentos al que se accede con `$p->x`
     * como una relación, y exige que devuelva un Relation. Como aquí se devuelve
     * un modelo suelto, `$p->paisDeEmpresa` explota con "must return a
     * relationship instance". Es el mismo motivo por el que `presentacion()` o
     * `incluye()` también se llaman como métodos.
     */
    public function paisDeEmpresa(): ?Pais
    {
        return $this->empresa?->pais;
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
     * Carpeta pública que guarda las fotos de este paquete, relativa a public/.
     *
     * Es una carpeta por paquete, con su slug como nombre.
     */
    public function carpetaFotos(): string
    {
        return "images/paquetes/" . $this->slug;
    }

    /**
     * Fotos del paquete turístico.
     *
     * La única verdad sobre la galería es la tabla `imagenes`. La carpeta
     * pública public/images/paquetes/{slug}/ es de donde salen los archivos, y
     * las columnas `imagen` e `imagenes` quedan como red de seguridad para las
     * URLs que `imagenes:sincronizar` no ha podido descargar.
     *
     * Devuelve rutas relativas ("/images/..."); galeriaAbsoluta() las convierte
     * en absolutas con el host real de la petición para que el frontend (que
     * corre en otro puerto) pueda cargarlas.
     *
     * @return array<int, string>
     */
    public function galeria(): array
    {
        $carpeta = $this->carpetaFotos();

        $fotos = $this->fotos()
            ->orderBy("orden")
            ->orderBy("id")
            ->get()
            ->map(fn (Imagen $imagen) => $imagen->rutaEn($carpeta))
            ->values()
            ->all();

        if (empty($fotos)) {
            $guardadas = array_merge(array_filter([$this->imagen]), $this->imagenes ?? []);
            $fotos = array_values(array_filter($guardadas, fn ($url) => is_string($url) && $url !== ""));
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
