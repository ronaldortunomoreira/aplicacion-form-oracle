<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Añade la versión en inglés de `paises.texto_presentacion`.
 *
 * El campo ya era un mapa idioma -> texto desde la migración que creó la tabla,
 * precisamente para que añadir un idioma no exigiera cambiar el esquema. Esta
 * migración solo rellena la clave `en` de los cuatro países existentes.
 *
 * Se hace con UPDATE y no se reescribe el JSON entero a propósito: si más
 * adelante hay un tercer idioma con traducciones ya cargadas, un
 * `json_encode` del valor leído las borraría. Solo se toca la clave concreta.
 *
 * El nombre del país se mantiene en español a propósito. Es un nombre propio
 * ("España", "México") y la API lo expone como `country`; la traducción del
 * topónimo la aporta el copy de la interfaz cuando el idioma lo requiere, no la
 * base de datos.
 */
return new class extends Migration
{
    /** Traducciones al inglés de los textos de presentación. */
    private function traducciones(): array
    {
        return [
            'espana' => 'Spain offers a wide range of places to stay: historic hotels in Madrid and Barcelona, hostels in lively neighbourhoods, rural cabins in the Sierra and holiday apartments on the coast. Choose the option that fits your trip and book with instant confirmation.',
            'mexico' => 'Mexico combines Caribbean and Pacific beach resorts with mountain escapes and characterful towns. From Cancun to Valle de Bravo, you will find stays designed for every kind of trip.',
            'argentina' => 'Argentina runs from the glaciers of Patagonia to the city that never sleeps. Find a boutique hotel in Buenos Aires, a mountain lodge in Bariloche or a cabin in the woods for your next trip.',
            'colombia' => 'Colombia rewards travellers who look beyond the obvious. Discover boutique hotels in Bogota, colonial houses in Medellin and coastal stays in Cartagena and the Caribbean.',
        ];
    }

    public function up(): void
    {
        foreach ($this->traducciones() as $slug => $texto) {
            $pais = DB::table('paises')->where('slug', $slug)->first();

            if (! $pais) {
                continue;
            }

            $textos = json_decode($pais->texto_presentacion, true) ?: [];

            // Se conserva cualquier otro idioma ya presente.
            $textos['en'] = $texto;

            DB::table('paises')
                ->where('slug', $slug)
                ->update([
                    'texto_presentacion' => json_encode($textos, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->traducciones()) as $slug) {
            $pais = DB::table('paises')->where('slug', $slug)->first();

            if (! $pais) {
                continue;
            }

            $textos = json_decode($pais->texto_presentacion, true) ?: [];

            unset($textos['en']);

            DB::table('paises')
                ->where('slug', $slug)
                ->update([
                    'texto_presentacion' => json_encode($textos, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
        }
    }
};
