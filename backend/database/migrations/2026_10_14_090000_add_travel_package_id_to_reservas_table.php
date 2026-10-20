<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Una reserva de paquete turístico no tiene unidad de habitación ni fechas: el
 * paquete fija el precio de una vez por todas. La reserva de alojamiento sigue
 * funcionando igual, solo que esas columnas pasan a ser opcionales.
 *
 * El `hasColumn` deja reejecutar la migración en bases que ya tenían la columna
 * añadida a mano (el caso de desarrollo), donde no está registrada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedBigInteger('unidad_habitacion_id')->nullable()->change();
            $table->unsignedBigInteger('alojamiento_id')->nullable()->change();
            $table->date('fecha_entrada')->nullable()->change();
            $table->date('fecha_salida')->nullable()->change();
        });

        if (!Schema::hasColumn('reservas', 'travel_package_id')) {
            Schema::table('reservas', function (Blueprint $table) {
                // Sin clave foránea: `travel_packages.id` es `int unsigned` y la
                // columna histórica de desarrollo es `bigint unsigned`. El
                // modelo solo la usa para resolver el paquete, nunca para un JOIN
                // que dependa del tipo exacto.
                $table->unsignedBigInteger('travel_package_id')->nullable()->after('unidad_habitacion_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reservas', 'travel_package_id')) {
            Schema::table('reservas', function (Blueprint $table) {
                $table->dropColumn('travel_package_id');
            });
        }

        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedBigInteger('unidad_habitacion_id')->nullable(false)->change();
            $table->unsignedBigInteger('alojamiento_id')->nullable(false)->change();
            $table->date('fecha_entrada')->nullable(false)->change();
            $table->date('fecha_salida')->nullable(false)->change();
        });
    }
};
