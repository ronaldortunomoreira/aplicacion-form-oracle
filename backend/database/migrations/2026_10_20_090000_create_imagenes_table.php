<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla única de fotos para alojamientos, tipos de alojamiento y paquetes
     * turísticos.
     *
     * Antes cada entidad guardaba sus fotos en una columna JSON propia
     * (`alojamientos.imagenes`, `travel_packages.imagenes`...) y además se
     * leían carpetas públicas distintas. Aquí la tabla es la única verdad: las
     * carpetas y las columnas JSON quedan como fuentes que el comando
     * `imagenes:sincronizar` vuelca a filas.
     *
     * `recurso_tipo` + `recurso_id` apuntan al dueño de la foto:
     *   - alojamiento       -> alojamientos.id
     *   - tipo_alojamiento  -> accommodation_types.id (fotos de tipo, compartidas)
     *   - paquete           -> travel_packages.id
     */
    public function up(): void
    {
        Schema::create('imagenes', function (Blueprint $table) {
            $table->id();

            $table->string('recurso_tipo', 40);
            $table->unsignedBigInteger('recurso_id');
            // Ruta relativa ("/images/...") o URL absoluta de la fuente externa.
            $table->string('ruta', 500);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('es_portada')->default(false);
            // carpeta (archivos públicos), bd (columna JSON) o panel (subida).
            $table->string('origen', 20)->default('carpeta');
            $table->string('texto_alt', 255)->nullable();

            $table->timestamps();

            $table->index(['recurso_tipo', 'recurso_id', 'orden'], 'imagenes_recurso_orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes');
    }
};
