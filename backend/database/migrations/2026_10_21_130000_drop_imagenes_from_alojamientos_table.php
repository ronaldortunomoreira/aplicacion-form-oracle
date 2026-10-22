<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los alojamientos tenían una columna `imagenes` con URLs que el comando
 * `imagenes:sincronizar` bajaba a la carpeta pública la primera vez. Ahora la
 * carpeta es la única verdad de la galería: las fotos ya viven en disco y la
 * tabla `imagenes` guarda solo el archivo, así que la columna sobra.
 *
 * Se crea una migración aparte en vez de editar la que creó la tabla, que ya se
 * ejecutó en desarrollo y en la base de pruebas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alojamientos', function (Blueprint $table) {
            $table->dropColumn('imagenes');
        });
    }

    public function down(): void
    {
        Schema::table('alojamientos', function (Blueprint $table) {
            $table->json('imagenes')->nullable()->after('politicas');
        });
    }
};
