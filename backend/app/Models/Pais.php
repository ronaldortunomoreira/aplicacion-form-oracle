<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pais extends Model
{
    use TraduceColumnas;

    protected $table = 'paises';

    protected static $columnas = [
        'name' => 'nombre',
        'primary_language' => 'idioma_principal',
        'presentation_text' => 'texto_presentacion',
        'active' => 'estado',
        // slug, iso2 y moneda_iso ya coinciden en ambos idiomas: son códigos, no
        // texto traducible.
    ];

    protected $fillable = [
        'name',
        'slug',
        'iso2',
        'primary_language',
        'presentation_text',
        'active',
        // ISO 4217. Fuente de verdad de la moneda: de aquí la heredan los
        // alojamientos y, al crear una reserva, queda congelada en la reserva.
        'moneda_iso',
    ];

    protected $casts = [
        'texto_presentacion' => 'array',
        'estado' => 'boolean',
    ];

    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class, 'pais_id');
    }

    /** Destinos turísticos publicados dentro del país. */
    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class, 'pais_id');
    }

    /**
     * Texto de introducción de la página del país en el idioma pedido, con
     * respaldo al español.
     *
     * El respaldo importa: hoy solo existe el texto en español, y una página en
     * otro idioma debe seguir mostrando contenido en vez de quedarse vacía.
     */
    public function presentacion(string $idioma = 'es'): ?string
    {
        $textos = $this->texto_presentacion ?: [];

        return $textos[$idioma] ?? $textos['es'] ?? null;
    }

    /**
     * Etiqueta de Open Graph para la región, p. ej. "es_ES". Google la espera en
     * formato idioma_REGION, no en el ISO geográfico de la página.
     */
    public function locale(string $idioma = 'es'): string
    {
        return $idioma . '_' . strtoupper($this->iso2);
    }
}
