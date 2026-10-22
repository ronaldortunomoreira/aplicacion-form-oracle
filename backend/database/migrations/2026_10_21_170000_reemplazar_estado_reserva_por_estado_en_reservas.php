<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sustituye `reservas.estado_reserva` (enum de texto) por `reservas.estado`, un
 * entero: 1 = activo, 2 = confirmado, 0 = anulado, -1 = cancelado.
 *
 * La columna formaba parte del índice de disponibilidad, así que se recrea con
 * la nueva. Se traduce el valor de cada reserva existente: `confirmed` y
 * `completed` pasan a 2 (confirmado) y `cancelled` a -1 (cancelado).
 *
 * No se usa `change()` porque el enum de MySQL no acepta valores fuera de su
 * lista y los -1 se malinterpretarían: se recrea la columna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // `unidad_habitacion_id` es la primera columna del índice de
            // disponibilidad y su clave foránea lo necesita como apoyo, así que
            // MySQL no deja quitar el índice sin otro que lo sustituya. Se crea
            // uno provisional sobre esa columna antes de tocar el resto.
            $table->index('unidad_habitacion_id', 'reservas_unidad_habitacion_id_aux');
            $table->smallInteger('estado')->default(1)->after('estado_pago');
        });

        DB::table('reservas')->whereIn('estado_reserva', ['confirmed', 'completed'])->update(['estado' => 2]);
        DB::table('reservas')->where('estado_reserva', 'cancelled')->update(['estado' => -1]);

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex('idx_disponibilidad_reserva');
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('estado_reserva');
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->index(
                ['unidad_habitacion_id', 'estado', 'fecha_entrada', 'fecha_salida'],
                'idx_disponibilidad_reserva'
            );
            // El índice nuevo ya cubre la clave foránea, así que el provisional
            // sobra.
            $table->dropIndex('reservas_unidad_habitacion_id_aux');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->index('unidad_habitacion_id', 'reservas_unidad_habitacion_id_aux');
            $table->enum('estado_reserva', ['confirmed', 'cancelled', 'completed'])->default('confirmed')->after('estado_pago');
        });

        DB::table('reservas')->where('estado', -1)->update(['estado_reserva' => 'cancelled']);
        DB::table('reservas')->whereIn('estado', [0, 1, 2])->update(['estado_reserva' => 'confirmed']);

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex('idx_disponibilidad_reserva');
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->index(
                ['unidad_habitacion_id', 'estado_reserva', 'fecha_entrada', 'fecha_salida'],
                'idx_disponibilidad_reserva'
            );
            $table->dropIndex('reservas_unidad_habitacion_id_aux');
        });
    }
};
