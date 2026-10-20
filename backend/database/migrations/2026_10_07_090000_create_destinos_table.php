<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de destinos turísticos.
 *
 * Es la capa editorial que faltaba: los alojamientos ya tenían ciudad y país
 * como atributos, pero no había una entidad propia para "Salar de Uyuni" o
 * "Cartagena", con su imagen, su presentación por idioma y un orden de
 * destacados. La página /es/destinos y su detalle cuelgan de aquí.
 *
 * `ciudad` enlaza con `alojamientos.ciudad` para poder listar las propiedades
 * del destino sin duplicar la relación. Es nullable a propósito: un destino
 * puede ser un paraje (el Salar) y no tener alojamientos asociados.
 *
 * La tabla nace ya en español, conforme a la convención del proyecto: las
 * columnas del dominio están en español y el modelo las traduce al inglés.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pais_id')
                  ->constrained('paises')
                  ->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('slug', 170)->unique();
            // Ciudad a la que apunta el destino, para poder cruzar con
            // `alojamientos.ciudad`. Null en destinos que no son ciudades.
            $table->string('ciudad', 150)->nullable();
            // Mapa idioma -> texto, igual que `paises.texto_presentacion`.
            $table->json('texto_presentacion')->nullable();
            $table->string('imagen', 500)->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->boolean('destacado')->default(false);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Orden editorial dentro de un país: la consulta típica filtra por
            // país y ordena por destacado y posición.
            $table->index(['pais_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinos');
    }
};
