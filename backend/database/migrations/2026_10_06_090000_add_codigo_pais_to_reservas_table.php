<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El formulario de reserva pide el país del teléfono del huésped, igual que
     * el de alta de hotel, y guarda el prefijo internacional (+591 para Bolivia).
     *
     * A diferencia de `empresas.codigo_pais`, aquí se guarda el prefijo y no el
     * ISO2 porque el teléfono del huésped se usa para contactarle, no para
     * clasificar su procedencia. Nullable para que las reservas anteriores,
     * createdas sin este campo, sigan siendo válidas.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('codigo_pais', 6)->nullable()->after('telefono_huesped');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('codigo_pais');
        });
    }
};