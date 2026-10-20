<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\File;

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
     * Carpeta pública de fotos de cada tipo de alojamiento. El slug es el mismo
     * que viaja en la URL (/alojamientos?type_slug=hotel) y el nombre de carpeta
     * es la forma legible que ve el administrador al arrastrar las imágenes:
     * public/images/alojamientos/<carpeta>/.
     */
    protected static $carpetasTipo = [
        'hotel' => 'hoteles',
        'hostal' => 'hostales',
        'cabana' => 'cabanas',
        'apartamento' => 'apartamentos',
        'glamping' => 'glamping',
    ];

    /** Extensiones consideradas imagen al leer las carpetas públicas. */
    private const EXTENSIONES_FOTO = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

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
     * El administrador coloca las fotos en la carpeta pública de su tipo,
     * public/images/alojamientos/<carpeta>/ (ver el LEEME.txt de esa carpeta).
     * Dentro puede haber una subcarpeta por alojamiento, con el slug como nombre
     * (public/images/alojamientos/<carpeta>/<slug>/), para que cada alojamiento
     * tenga sus propias fotos. Si esa subcarpeta no aporta ninguna imagen, se usa
     * la carpeta del tipo, cuyas fotos son comunes a todos los alojamientos de
     * ese tipo.
     *
     * En ambos casos las fotos se listan ordenadas por nombre y después se
     * añaden las URLs ya guardadas en la columna `imagenes`, sin repetir.
     *
     * Devuelve rutas relativas ("/images/..."); galeriaAbsoluta() las convierte
     * en absolutas con el host real de la petición para que el frontend (que
     * corre en otro puerto) pueda cargarlas.
     *
     * @return array<int, string>
     */
    public function galeria(): array
    {
        $fotos = [];

        $carpetaTipo = static::$carpetasTipo[$this->accommodationType?->slug] ?? null;
        if ($carpetaTipo) {
            $relativaTipo = 'images/alojamientos/' . $carpetaTipo;

            // La carpeta propia del alojamiento manda; si no tiene fotos, se cae
            // a la del tipo (compartida por todos los alojamientos de ese tipo).
            $relativa = $relativaTipo . '/' . $this->slug;
            if ($this->fotosEnCarpeta(public_path($relativa))->isEmpty()) {
                $relativa = $relativaTipo;
            }

            $fotos = $this->fotosEnCarpeta(public_path($relativa))
                ->map(fn (string $nombre) => '/' . $relativa . '/' . rawurlencode($nombre))
                ->all();
        }

        foreach ($this->imagenes ?? [] as $url) {
            if (is_string($url) && $url !== '' && ! in_array($url, $fotos, true)) {
                $fotos[] = $url;
            }
        }

        return $fotos;
    }

    /**
     * Nombres de los archivos de imagen de una carpeta, ordenados de forma
     * natural (01, 02, ... 10) para que la portada sea predecible. Devuelve una
     * colección vacía si la carpeta no existe.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function fotosEnCarpeta(string $carpeta)
    {
        if (! File::isDirectory($carpeta)) {
            return collect();
        }

        return collect(File::files($carpeta))
            ->filter(fn ($archivo) => in_array(strtolower($archivo->getExtension()), self::EXTENSIONES_FOTO, true))
            ->sortBy(fn ($archivo) => $archivo->getFilename(), SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn ($archivo) => $archivo->getFilename())
            ->values();
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
