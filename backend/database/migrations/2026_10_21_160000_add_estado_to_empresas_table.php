<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade `empresas.estado` como entero: 1 = activa, 0 = inactiva.
 *
 * Se usa el mismo criterio que `alojamientos.estado`. Las empresas existentes,
 * y las que se registren, quedan activas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('estado')->default(true)->after('pais_id');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
