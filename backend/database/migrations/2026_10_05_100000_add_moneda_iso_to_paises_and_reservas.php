<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Añade la moneda a los países y la congela en las reservas.
 *
 * Hasta aquí los precios se guardaban como `decimal(10,2)` pelados, sin ningún
 * código de moneda en ninguna tabla, mientras el frontend imprimía "USD" escrito
 * a mano. Con cinco países y cinco monedas distintas (EUR, MXN, ARS, COP, BOB)
 * un `decimal` a secas es indescifrable: 185.00 son euros en Madrid o bolivianos
 * en Cochabamba, y el filtro de precio comparaba ambos como si fueran lo mismo.
 *
 * La moneda vive en dos sitios y solo en dos:
 *
 *  - `paises.moneda_iso` es la fuente de verdad, una fila por país. Todo lo demás
 *    la hereda: el precio de una habitación no lleva moneda porque
 *    `unidades_habitacion.alojamiento_id` -> `alojamientos.pais_id` -> paises
 *    ya deja la cadena montada. Duplicarla ahí obligaría a mantenerla sincronizada.
 *  - `reservas.moneda_iso` es una copia congelada el día de la reserva. No es
 *    redundancia: una reserva es un documento económico y su precio no puede
 *    cambiar porque el país cambie de moneda mañana. Por eso la escribe
 *    ReservationService una vez y no vuelve a leerla.
 *
 * `reservas.alojamiento_id` se desnormaliza en esta misma migración. La tabla no
 * lo tenía, así que obtener su moneda exigía cruzar tres tablas (reservas ->
 * unidades_habitacion -> alojamientos -> paises), tanto para este backfill como
 * para cada consulta de informes posterior. Guardar el id evita ese join para
 * siempre, y es un dato que no cambia: una reserva siempre se hizo en un
 * alojamiento concreto.
 *
 * `reservas.moneda_iso` queda nullable a propósito, igual que
 * `alojamientos.pais_id`, que está en ON DELETE SET NULL. Si un alojamiento se
 * queda sin país, sus reservas no tienen moneda que congelar, y es preferible
 * guardar null a inventar un importe en una moneda que nadie pactó.
 */
return new class extends Migration
{
    /**
     * ISO 4217. Bolivia es BOB y no "BOB$"; el código es lo que espera
     * schema.org en `Offer.priceCurrency`.
     */
    private const MONEDAS_POR_ISO2 = [
        'ES' => 'EUR',
        'MX' => 'MXN',
        'AR' => 'ARS',
        'CO' => 'COP',
        'BO' => 'BOB',
    ];

    public function up(): void
    {
        // Nullable primero porque la tabla ya tiene filas y MySQL no puede
        // añadir una columna NOT NULL sin valor por defecto para ellas.
        Schema::table('paises', function (Blueprint $table) {
            $table->char('moneda_iso', 3)->nullable()->after('iso2');
        });

        foreach (self::MONEDAS_POR_ISO2 as $iso2 => $moneda) {
            DB::table('paises')
                ->where('iso2', $iso2)
                ->update(['moneda_iso' => $moneda]);
        }

        DB::statement('ALTER TABLE paises MODIFY moneda_iso CHAR(3) NOT NULL');

        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedBigInteger('alojamiento_id')->nullable()->after('unidad_habitacion_id');
            $table->foreign('alojamiento_id')
                ->references('id')
                ->on('alojamientos')
                // RESTRICT, igual que reservas_unidad_habitacion_id_foreign: no
                // se borra un alojamiento que ya tiene reservas cobradas.
                ->restrictOnDelete();
            $table->char('moneda_iso', 3)->nullable()->after('precio_total');
        });

        // Se rellena cruzando las tres tablas porque `reservas` no conocía su
        // alojamiento. LEFT JOIN a propósito: si el alojamiento no tiene país, su
        // reserva se queda sin moneda en vez de heredar un valor inventado.
        DB::statement(<<<'SQL'
            UPDATE reservas r
            JOIN unidades_habitacion u ON u.id = r.unidad_habitacion_id
            JOIN alojamientos a ON a.id = u.alojamiento_id
            LEFT JOIN paises p ON p.id = a.pais_id
            SET r.alojamiento_id = a.id,
                r.moneda_iso = p.moneda_iso
        SQL);

        // `unidad_habitacion_id` es NOT NULL y `unidades_habitacion.alojamiento_id`
        // también, así que toda fila tiene alojamiento y se puede endurecer. La FK
        // creada arriba ya valida que el id exista.
        DB::statement('ALTER TABLE reservas MODIFY alojamiento_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['alojamiento_id']);
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['alojamiento_id', 'moneda_iso']);
        });

        Schema::table('paises', function (Blueprint $table) {
            $table->dropColumn('moneda_iso');
        });
    }
};