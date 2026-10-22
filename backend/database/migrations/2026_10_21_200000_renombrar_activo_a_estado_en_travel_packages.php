<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renombra `travel_packages.activo` a `travel_packages.estado` para unificar el
 * nombre del campo con el resto de tablas. Sigue siendo un entero: 1 = activo,
 * 0 = inactivo.
 *
 * Solo se renombra: el tipo (boolean) y los valores se conservan tal cual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_packages', function (Blueprint $table) {
            $table->renameColumn('activo', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('travel_packages', function (Blueprint $table) {
            $table->renameColumn('estado', 'activo');
        });
    }
};
