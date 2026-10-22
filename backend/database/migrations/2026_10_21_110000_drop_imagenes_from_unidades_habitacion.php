<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las unidades de habitación tenían su propia columna `imagenes` con URLs de
     * Unsplash, pero el frontend nunca la usó: la galería que se ve es siempre la
     * del alojamiento, que vive en la tabla `imagenes` y en la carpeta pública de
     * su tipo. Se elimina para no mantener dos fuentes de fotos.
     */
    public function up(): void
    {
        Schema::table('unidades_habitacion', function (Blueprint $table) {
            $table->dropColumn('imagenes');
        });
    }

    public function down(): void
    {
        Schema::table('unidades_habitacion', function (Blueprint $table) {
            $table->json('imagenes')->nullable()->after('amenidades');
        });
    }
};
