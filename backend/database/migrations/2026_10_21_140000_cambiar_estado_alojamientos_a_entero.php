<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `alojamientos.estado` deja de ser el enum `active|maintenance|inactive` y pasa
 * a ser un entero: 1 = activo, 0 = inactivo.
 *
 * No se usa `change()` porque el enum de MySQL no acepta un 0 (sus índices
 * empiezan en 1) y los valores se malinterpretarían. Se recrea la columna: se
 * añade una nueva, se traducen los valores leyendo el enum como texto y se
 * sustituye la vieja. `maintenance` cuenta como activo porque el alojamiento
 * sigue publicado.
 *
 * La columna formaba parte del índice de búsqueda, así que este se recrea con la
 * columna nueva.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alojamientos', function (Blueprint $table) {
            $table->dropIndex('idx_alojamientos_busqueda');
            $table->boolean('estado_nuevo')->default(true)->after('politicas');
        });

        DB::table('alojamientos')->where('estado', 'inactive')->update(['estado_nuevo' => 0]);
        DB::table('alojamientos')->whereIn('estado', ['active', 'maintenance'])->update(['estado_nuevo' => 1]);

        Schema::table('alojamientos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        Schema::table('alojamientos', function (Blueprint $table) {
            $table->renameColumn('estado_nuevo', 'estado');
            $table->index(['ciudad', 'tipo_alojamiento_id', 'estado'], 'idx_alojamientos_busqueda');
        });
    }

    public function down(): void
    {
        Schema::table('alojamientos', function (Blueprint $table) {
            $table->dropIndex('idx_alojamientos_busqueda');
            $table->enum('estado_nuevo', ['active', 'maintenance', 'inactive'])->default('active')->after('politicas');
        });

        DB::table('alojamientos')->where('estado', 0)->update(['estado_nuevo' => 'inactive']);
        DB::table('alojamientos')->where('estado', 1)->update(['estado_nuevo' => 'active']);

        Schema::table('alojamientos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        Schema::table('alojamientos', function (Blueprint $table) {
            $table->renameColumn('estado_nuevo', 'estado');
            $table->index(['ciudad', 'tipo_alojamiento_id', 'estado'], 'idx_alojamientos_busqueda');
        });
    }
};
