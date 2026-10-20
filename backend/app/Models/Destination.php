<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Destino turístico (Salar de Uyuni, Cartagena, Bariloche...).
 *
 * La tabla vive en español (`destinos`) y la API la expone en inglés, como el
 * resto del dominio, mediante el trait TraduceColumnas.
 */
class Destination extends Model
{
    use TraduceColumnas;

    protected $table = 'destinos';

    protected static $columnas = [
        'country_id' => 'pais_id',
        'name' => 'nombre',
        'city' => 'ciudad',
        'presentation_text' => 'texto_presentacion',
        'image' => 'imagen',
        'latitude' => 'latitud',
        'longitude' => 'longitud',
        'featured' => 'destacado',
        'order' => 'orden',
        'active' => 'activo',
        // slug coincide en ambos idiomas: es un código, no texto traducible.
    ];

    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'city',
        'presentation_text',
        'image',
        'latitude',
        'longitude',
        'featured',
        'order',
        'active',
    ];

    protected $casts = [
        'texto_presentacion' => 'array',
        'destacado' => 'boolean',
        'activo' => 'boolean',
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    /**
     * Texto de presentación en el idioma pedido, con respaldo al español.
     *
     * El respaldo importa: si un destino solo está traducido a medias, la
     * página en inglés enseña el español en lugar de quedarse vacía.
     */
    public function presentacion(string $idioma = 'es'): ?string
    {
        $textos = $this->texto_presentacion ?: [];

        return $textos[$idioma] ?? $textos['es'] ?? null;
    }
}
