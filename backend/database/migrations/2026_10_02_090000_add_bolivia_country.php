<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Añade Bolivia como país publicable.
 *
 * Bolivia no estaba en el catálogo inicial, así que sus alojamientos no tenían
 * `pais_id` válido ni página de país. Se añade como migración aparte y no
 * editando la de creación del catálogo, porque esa ya se ejecutó en desarrollo y
 * en la base de datos de pruebas: volver a correrla no insertaría nada nuevo.
 *
 * `iso2` es `BO` según ISO 3166-1 alfa-2, que es lo que schema.org espera en
 * `PostalAddress.addressCountry` y lo que usa `Pais::locale()` para construir el
 * `og:locale` (`es_BO`).
 */
return new class extends Migration
{
    public function up(): void
    {
        // `insertOrIgnore` en vez de `insert`: la migración debe poder
        // reejecutarse sin romper, y el slug e iso2 son únicos.
        DB::table('paises')->insertOrIgnore([
            'nombre' => 'Bolivia',
            'slug' => 'bolivia',
            'iso2' => 'BO',
            'idioma_principal' => 'es',
            'texto_presentacion' => json_encode([
                'es' => 'Bolivia es una encrucijada de paisajes y culturas: la selva amazónica al norte, el altiplano y el Salar de Uyuni al sur, y valles templados como el de Cochabamba en el centro. En Cochabamba conviven los hoteles de negocios con las casas de campo del valle, todo a pocos kilómetros.',
                'en' => 'Bolivia is a crossroads of landscapes and cultures: the Amazon jungle in the north, the high plains and Salar de Uyuni in the south, and temperate valleys like Cochabamba in the centre. In Cochabamba, business hotels sit alongside countryside retreats in the valley, all within a few kilometres.',
            ], JSON_UNESCAPED_UNICODE),
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Solo se elimina Bolivia. Los alojamientos que lo referencian tienen
        // `pais_id` con ON DELETE SET NULL, así que sobreviven sin país y
        // quedan fuera del sitemap, que es el comportamiento correcto al
        // revertir: no se borra trabajo del usuario.
        DB::table('paises')->where('slug', 'bolivia')->delete();
    }
};