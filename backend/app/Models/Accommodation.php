<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Accommodation extends Model
{
    use HasFactory;
    use TraduceColumnas;

    protected $table = 'alojamientos';

    protected static $columnas = [
        'accommodation_type_id' => 'tipo_alojamiento_id',
        'name' => 'nombre',
        'headline' => 'titulo',
        'description' => 'descripcion',
        'address' => 'direccion',
        'city' => 'ciudad',
        'state' => 'provincia',
        // El país es una relación, ya no una columna de texto. El nombre legible
        // se expone como atributo calculado 'country' más abajo.
        'country_id' => 'pais_id',
        'company_id' => 'empresa_id',
        'latitude' => 'latitud',
        'longitude' => 'longitud',
        'star_rating' => 'estrellas',
        'guest_rating' => 'valoracion_huesped',
        'reviews_count' => 'numero_resenas',
        'check_in_time' => 'hora_entrada',
        'check_out_time' => 'hora_salida',
        'amenities' => 'amenidades',
        'policies' => 'politicas',
        'images' => 'imagenes',
        'status' => 'estado',
    ];

    /**
     * 'country' sigue siendo una cadena con el nombre del país, igual que antes
     * del cambio, para no romper el contrato JSON. El slug, el código ISO y el
     * locale viajan como campos aparte porque el frontend los necesita para
     * armar las URLs por país, el JSON-LD y el og:locale.
     *
     * 'currency' es el código ISO 4217 heredado del país. El precio no lo lleva:
     * la unidad de habitación no tiene columna de moneda a propósito, y el
     * frontend formatea con este valor.
     */
    protected $appends = ['country', 'country_slug', 'country_iso2', 'country_locale', 'currency'];

    protected $fillable = [
        'accommodation_type_id',
        'name',
        'slug',
        'headline',
        'description',
        'address',
        'city',
        'state',
        'country_id',
        'company_id',
        'latitude',
        'longitude',
        'star_rating',
        'guest_rating',
        'reviews_count',
        'check_in_time',
        'check_out_time',
        'amenities',
        'policies',
        'images',
        'status',
    ];

    protected $casts = [
        'amenidades' => 'array',
        'politicas' => 'array',
        'imagenes' => 'array',
        'valoracion_huesped' => 'float',
        'estrellas' => 'integer',
        'numero_resenas' => 'integer',
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public function accommodationType(): BelongsTo
    {
        return $this->belongsTo(AccommodationType::class, 'tipo_alojamiento_id');
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    /** Empresa propietaria del alojamiento, o null si no está vinculado. */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /** Nombre legible del país, o null si el alojamiento no tiene país asignado. */
    public function getCountryAttribute(): ?string
    {
        return $this->pais?->nombre;
    }

    public function getCountrySlugAttribute(): ?string
    {
        return $this->pais?->slug;
    }

    /** Código ISO 3166-1 alfa-2, que es lo que espera schema.org. */
    public function getCountryIso2Attribute(): ?string
    {
        return $this->pais?->iso2;
    }

    public function getCountryLocaleAttribute(): ?string
    {
        return $this->pais?->locale($this->pais?->idioma_principal ?? 'es');
    }

    /**
     * Moneda del país donde está el alojamiento, o null si no tiene país. El
     * precio vive en las unidades de habitación, así que este es el único punto
     * donde el frontend puede saber en qué está expresado.
     */
    public function getCurrencyAttribute(): ?string
    {
        return $this->pais?->moneda_iso;
    }

    public function roomUnits(): HasMany
    {
        return $this->hasMany(RoomUnit::class, 'alojamiento_id');
    }

    public function reservations(): HasManyThrough
    {
        return $this->hasManyThrough(
            Reservation::class,
            RoomUnit::class,
            'alojamiento_id',
            'unidad_habitacion_id'
        );
    }

    /**
     * Fotos del alojamiento.
     *
     * La única verdad sobre la galería es la tabla `imagenes`. Las carpetas
     * públicas (public/images/alojamientos/<carpeta>/<slug>/ y la carpeta del
     * tipo, ver el LEEME.txt) y la columna `imagenes` quedan como fuentes que el
     * comando `imagenes:sincronizar` vuelca a filas.
     *
     * Si el alojamiento no tiene filas propias, se usan las del tipo, cuyas
     * fotos son comunes a todos los alojamientos de ese tipo. Si no hay ninguna
     * fila, se cae a las URLs de la columna `imagenes` para no dejar la galería
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
        $fotos = $this->filasDeGaleria()
            ->map(fn (Imagen $imagen) => $imagen->ruta)
            ->values()
            ->all();

        if (empty($fotos)) {
            $fotos = array_values(array_filter($this->imagenes ?? [], fn ($url) => is_string($url) && $url !== ''));
        }

        return $fotos;
    }

    /**
     * Filas de `imagenes` que pintan la galería: las propias del alojamiento, y
     * si no tiene, las de su tipo.
     *
     * @return \Illuminate\Support\Collection<int, Imagen>
     */
    private function filasDeGaleria()
    {
        $propias = $this->fotos()
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        if ($propias->isNotEmpty()) {
            return $propias;
        }

        $tipo = $this->accommodationType;

        if (! $tipo) {
            return collect();
        }

        return Imagen::where('recurso_tipo', 'tipo_alojamiento')
            ->where('recurso_id', $tipo->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    /**
     * Fotos registradas en la tabla para este alojamiento.
     *
     * La local key va explícita porque el trait TraduceColumnas traduce la clave
     * que Eloquent resuelve por defecto (accommodation_id) y esa columna no
     * existe en `alojamientos`: la clave real siempre es `id`.
     */
    public function fotos(): MorphMany
    {
        return $this->morphMany(Imagen::class, 'recurso', 'recurso_tipo', 'recurso_id', 'id');
    }

    /**
     * Igual que galeria(), pero con las rutas de carpeta ya resueltas a URLs
     * absolutas con el host de la petición. Lo usan el buscador y las reservas
     * por igual, de modo que la foto del alojamiento salga igual en todas las
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
