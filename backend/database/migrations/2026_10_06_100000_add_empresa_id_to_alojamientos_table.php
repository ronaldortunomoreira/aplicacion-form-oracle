<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vincula cada alojamiento con la empresa a la que pertenece.
     *
     * La columna es nullable porque los alojamientos existentes todavía no
     * tienen empresa asignada, y porque `empresas` guarda solicitudes de alta:
     * un alojamiento puede publicarse sin que exista (o siga existiendo) la
     * solicitud que lo dio de alta.
     *
     * ON DELETE SET NULL en vez de CASCADE: borrar una solicitud de alta no
     * debe borrar la propiedad publicada, solo desvincularla. Mismo criterio
     * que `alojamientos.pais_id`.
     */
    public function up(): void
    {
        Schema::table('alojamientos', function (Blueprint $table) {
            $table->foreignId('empresa_id')
                  ->nullable()
                  ->after('pais_id')
                  ->constrained('empresas')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alojamientos', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
    }
};
