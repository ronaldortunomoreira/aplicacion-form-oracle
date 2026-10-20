<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Solicitudes de alta enviadas desde la web.
 *
 * Es una tabla deliberadamente pequeña: cualquier interesado deja su nombre, su
 * correo y un comentario, y la solicitud queda visible en la página sin más
 * trámite. Sin estado, sin código de seguimiento y sin datos fiscales, porque
 * la web solo necesita recoger el contacto.
 *
 * `detalle` es texto y no un varchar corto porque es un comentario libre: no
 * tiene una forma fija que validar. `correo` es único para que la misma empresa
 * no llene la página con solicitudes repetidas al pulsar el botón dos veces.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('correo', 150)->unique();
            $table->text('detalle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};