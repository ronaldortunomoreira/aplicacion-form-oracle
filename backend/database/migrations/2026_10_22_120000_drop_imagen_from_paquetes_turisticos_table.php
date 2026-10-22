<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los paquetes turísticos tenían una columna `imagen` (URL de portada de
 * respaldo) que el comando `imagenes:sincronizar` bajaba a la carpeta pública
 * la primera vez. Ahora la carpeta es la única verdad de la galería: las fotos
 * viven en disco y la tabla `imagenes` guarda solo el archivo, así que la
 * columna sobra. Es la última del paquete que apuntaba a URLs externas.
 *
 * Se crea una migración aparte en vez de editar la que creó la tabla, que ya se
 * ejecutó en desarrollo y en la base de pruebas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paquetes_turisticos', function (Blueprint $table) {
            $table->dropColumn('imagen');
        });
    }

    public function down(): void
    {
        Schema::table('paquetes_turisticos', function (Blueprint $table) {
            $table->string('imagen')->nullable()->after('moneda_iso');
        });
    }
};
