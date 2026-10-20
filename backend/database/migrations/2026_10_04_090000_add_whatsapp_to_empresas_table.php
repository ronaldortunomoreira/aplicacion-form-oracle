<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade el contacto de WhatsApp a las solicitudes de alta.
 *
 * Se crea una migración aparte en vez de editar `create_empresas_table`, que ya
 * se ejecutó en desarrollo y en la base de pruebas: volver a correrla no volvería
 * a añadir las columnas.
 *
 * `codigo_pais` y `whatsapp` van separados porque el prefijo se elige en un
 * desplegable y el número se escribe a mano. Juntos reconstruyen el número
 * internacional completo, que es lo que hace falta para escribirle al
 * alojamiento. El prefijo no se guarda junto a los dígitos porque entonces
 * habría que volver a normalizar en cada lectura.
 *
 * Los campos son nullable para no invalidar las solicitudes ya guardadas antes
 * de que existieran; a partir de ahora el formulario los exige siempre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('codigo_pais', 6)->nullable()->after('correo');
            $table->string('whatsapp', 20)->nullable()->after('codigo_pais');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['codigo_pais', 'whatsapp']);
        });
    }
};