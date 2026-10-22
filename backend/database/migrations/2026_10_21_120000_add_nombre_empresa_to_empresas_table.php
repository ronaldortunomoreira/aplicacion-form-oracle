<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade el nombre de la empresa a las solicitudes de alta.
 *
 * `empresas.nombre` es el nombre de la persona de contacto, y hace falta el de la
 * empresa para saber a quién se da de alta. Se crea una migración aparte en vez de
 * editar `create_empresas_table` porque esa ya se ejecutó en desarrollo y en la
 * base de pruebas.
 *
 * Nullable para no invalidar las solicitudes ya guardadas, que no lo tenían; a
 * partir de ahora el formulario lo exige siempre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('nombre_empresa', 120)->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('nombre_empresa');
        });
    }
};
