<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renombra la tabla `travel_packages` a `paquetes_turisticos` para unificar el
 * idioma del esquema con el resto de tablas del dominio (alojamientos,
 * unidades_habitacion, empresas, reservas, paises, destinos).
 *
 * La columna de reservas que apunta al paquete también se traduce:
 * `reservas.travel_package_id` pasa a `reservas.paquete_turistico_id`.
 *
 * `reservas.travel_package_id` no tiene clave foránea (ver la migración que la
 * creó), así que renombrar la tabla no arrastra ninguna restricción; solo hay
 * que renombrar la columna para que siga diciendo lo mismo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('travel_packages', 'paquetes_turisticos');

        Schema::table('reservas', function (Blueprint $table) {
            $table->renameColumn('travel_package_id', 'paquete_turistico_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->renameColumn('paquete_turistico_id', 'travel_package_id');
        });

        Schema::rename('paquetes_turisticos', 'travel_packages');
    }
};
