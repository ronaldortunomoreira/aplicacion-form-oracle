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
        'status',
    ];

    protected $casts = [
        'amenidades' => 'array',
        'politicas' => 'array',
        'valoracion_huesped' => 'float',
        'estrellas' => 'integer',
        'numero_resenas' => 'integer',
        'latitud' => 'float',
        'longitud' => 'float',
        'estado' => 'boolean',
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
     * Carpeta pública que guarda las fotos de este alojamiento, relativa a
     * public/: su subcarpeta propia, dentro de la carpeta de su tipo.
     *
     * Devuelve null si no tiene tipo asignado, porque entonces no hay carpeta
     * posible y la galería queda vacía.
     */
    public function carpetaFotos(): ?string
    {
        $tipo = $this->accommodationType;

        if (! $tipo) {
            return null;
        }

        return $tipo->carpetaFotos() . '/' . $this->slug;
    }

    /**
     * Fotos del alojamiento.
     *
     * La única verdad sobre la galería es la tabla `imagenes`. Las carpetas
     * públicas (public/images/alojamientos/<carpeta>/<slug>/ y la carpeta del
     * tipo, ver el LEEME.txt) son de donde salen los archivos, que el comando
     * imagenes:sincronizar vuelca a la tabla. Así la galería nunca mezcla
     * archivos con textos de la base de datos.
     *
     * Si el alojamiento no tiene filas propias, se usan las del tipo, cuyas
     * fotos son comunes a todos los alojamientos de ese tipo.
     *
     * Devuelve rutas relativas ("/images/..."); galeriaAbsoluta() las convierte
     * en absolutas con el host real de la petición para que el frontend (que
     * corre en otro puerto) pueda cargarlas.
     *
     * @return array<int, string>
     */
    public function galeria(): array
    {
        [$carpeta, $filas] = $this->filasDeGaleria();

        return $carpeta
            ? $filas->map(fn (Imagen $imagen) => $imagen->rutaEn($carpeta))->values()->all()
            : [];
    }

    /**
     * Filas de `imagenes` que pintan la galería, junto con la carpeta en la que
     * viven sus archivos: las propias del alojamiento, y si no tiene, las de su
     * tipo.
     *
     * La carpeta viaja con las filas porque no es siempre la misma: las fotos
     * propias están en la subcarpeta del alojamiento y las del tipo en la
     * carpeta compartida de todos los de ese tipo.
     *
     * @return array{0: ?string, 1: \Illuminate\Support\Collection<int, Imagen>}
     */
    private function filasDeGaleria(): array
    {
        $propias = $this->fotos()
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        if ($propias->isNotEmpty()) {
            return [$this->carpetaFotos(), $propias];
        }

        $tipo = $this->accommodationType;

        if (! $tipo) {
            return [null, collect()];
        }

        $filas = Imagen::where('recurso_tipo', 'tipo_alojamiento')
            ->where('recurso_id', $tipo->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return [$filas->isEmpty() ? null : $tipo->carpetaFotos(), $filas];
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
