<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade `unidades_habitacion.estado` como entero: 1 = activa, 0 = inactiva.
 *
 * Se usa el mismo criterio que `alojamientos.estado`, para poder retirar una
 * unidad del inventario sin borrarla. Las unidades existentes quedan activas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades_habitacion', function (Blueprint $table) {
            $table->boolean('estado')->default(true)->after('total_unidades');
        });
    }

    public function down(): void
    {
        Schema::table('unidades_habitacion', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
