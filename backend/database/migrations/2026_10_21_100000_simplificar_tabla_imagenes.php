<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La tabla `imagenes` pasa a indexar solo archivos que viven en una carpeta
 * pública.
 *
 * Se quitan tres columnas:
 *
 *   - `ruta`      la ruta completa ya no se guarda: se arma al leer, con la
 *                 carpeta que le corresponde al dueño de la foto (ver
 *                 `carpetaFotos()` en Accommodation, AccommodationType y
 *                 TravelPackage). Solo queda `archivo`, con el nombre del
 *                 archivo ("01.jpg").
 *   - `es_portada` era redundante: la portada es la foto de menor `orden`.
 *   - `origen`    ya no hace falta distinguir de dónde vino la fila. El comando
 *                 `imagenes:sincronizar` gestiona todas, y las URLs que había en
 *                 las columnas JSON ahora se descargan a la carpeta en vez de
 *                 guardarse como texto.
 *
 * Se recrea la tabla entera en vez de alterarla: los datos son 100%
 * regenerables con `imagenes:sincronizar`, que `composer setup` ejecuta justo
 * después de migrar, y reconstruir en `down()` las 56 filas de URL externa que
 * se dejan de guardar sería mentira.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('imagenes');

        Schema::create('imagenes', function (Blueprint $table) {
            $table->id();

            $table->string('recurso_tipo', 40);
            $table->unsignedBigInteger('recurso_id');
            // Solo el nombre del archivo ("01.jpg"); la carpeta no se guarda.
            $table->string('archivo', 255);
            $table->unsignedInteger('orden')->default(0);
            $table->string('texto_alt', 255)->nullable();

            $table->timestamps();

            $table->index(['recurso_tipo', 'recurso_id', 'orden'], 'imagenes_recurso_orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes');

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
};
