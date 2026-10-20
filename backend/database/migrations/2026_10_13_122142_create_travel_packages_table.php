<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pais_id')->nullable();
            $table->unsignedInteger('destino_id')->nullable();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('ciudad')->nullable();
            $table->json('texto_presentacion')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('incluye')->nullable();
            $table->text('no_incluye')->nullable();
            $table->text('itinerario')->nullable();
            $table->string('duracion')->nullable();
            $table->integer('min_personas')->default(1);
            $table->integer('max_personas')->default(20);
            $table->decimal('precio_desde', 10, 2)->default(0);
            $table->string('moneda_iso', 3)->nullable();
            $table->string('imagen')->nullable();
            $table->json('imagenes')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->boolean('destacado')->default(false);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_packages');
    }
};
