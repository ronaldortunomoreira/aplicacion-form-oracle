<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El paquete turístico pasa a depender de la empresa que lo publica.
 *
 * La relación que manda es la empresa: el país del paquete se hereda de ella, y
 * por eso `travel_packages` pierde `pais_id` y `destino_id`. Lo que sí se queda
 * es `ciudad`, que es un dato del propio tour y no una relación.
 *
 * `empresa_id` es NOT NULL a propósito: no se publica un paquete sin empresa.
 * Al ser obligatoria la columna, la clave foránea no puede ser ON DELETE SET
 * NULL, y se elige RESTRICT: borrar una empresa que tenga paquetes publicados
 * debe fallar en la base de datos en vez de borrarles el trabajo en cascada.
 * (`reservas.travel_package_id` no tiene clave foránea, así que un CASCADE
 * dejaría reservas apuntando a paquetes inexistentes.)
 *
 * `empresas.pais_id` en cambio queda nullable. El registro de empresas acepta
 * el ISO2 de cualquier región del mundo (es el catálogo de libphonenumber),
 * mientras que `paises` solo contiene los países publicables del proyecto. Una
 * empresa de un país no publicable no tiene fila a la que apuntar, y sus
 * paquetes saldrán sin país: mismo criterio que `CatalogoPaises`.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Cada empresa pasa a saber de qué país es. Nullable, por lo de arriba.
        Schema::table('empresas', function (Blueprint $table) {
            $table->foreignId('pais_id')
                  ->nullable()
                  ->after('codigo_pais')
                  ->constrained('paises')
                  ->nullOnDelete();
        });

        // 2. `codigo_pais` guarda el ISO2, que es exactamente `paises.iso2`, así
        //    que el backfill es un JOIN directo. Lo que no se resuelve se queda
        //    en null: se prefiere una empresa sin país a perderla.
        DB::statement('
            UPDATE empresas e
            LEFT JOIN paises p ON p.iso2 = e.codigo_pais
            SET e.pais_id = p.id
            WHERE e.pais_id IS NULL AND e.codigo_pais IS NOT NULL
        ');

        // 3. El paquete depende de su empresa. Nullable solo durante la
        //    migración: hay que rellenar los paquetes existentes antes de
        //    volver la columna obligatoria.
        Schema::table('travel_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable()->after('id');
        });

        $this->vincularPaquetesExistentes();

        DB::statement('ALTER TABLE travel_packages MODIFY empresa_id BIGINT UNSIGNED NOT NULL');

        Schema::table('travel_packages', function (Blueprint $table) {
            $table->foreign('empresa_id')
                  ->references('id')
                  ->on('empresas')
                  ->restrictOnDelete();
        });

        // 4. El país y el destino propios del paquete ya no existen. Las dos
        //    columnas se crearon como enteros sin clave foránea, así que basta
        //    con soltarlas.
        Schema::table('travel_packages', function (Blueprint $table) {
            $table->dropColumn(['pais_id', 'destino_id']);
        });
    }

    public function down(): void
    {
        // Las dos columnas vuelven. `destino_id` no se puede recuperar: la
        // relación con el destino se rompió, y el nombre no está guardado en
        // ninguna parte. `pais_id` sí se recupera desde la empresa.
        Schema::table('travel_packages', function (Blueprint $table) {
            $table->unsignedInteger('pais_id')->nullable()->after('id');
            $table->unsignedInteger('destino_id')->nullable()->after('pais_id');
        });

        DB::statement('
            UPDATE travel_packages tp
            LEFT JOIN empresas e ON e.id = tp.empresa_id
            SET tp.pais_id = e.pais_id
            WHERE tp.pais_id IS NULL AND e.pais_id IS NOT NULL
        ');

        Schema::table('travel_packages', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropForeign(['pais_id']);
            $table->dropColumn('pais_id');
        });
    }

    /**
     * Da empresa a los paquetes que ya existen, que es lo que permite volver la
     * columna obligatoria.
     *
     * Los paquetes del seeder nacen de un destino, así que el país de cada uno
     * es el de su destino: se usa ese mismo criterio aquí. Si para un país no
     * hay ninguna empresa, se crea una de demo con ese país.
     */
    private function vincularPaquetesExistentes(): void
    {
        $sinEmpresa = DB::table('travel_packages')
            ->whereNull('empresa_id')
            ->get(['id', 'pais_id']);

        if ($sinEmpresa->isEmpty()) {
            return;
        }

        // Reutiliza la primera empresa ya existente de cada país.
        $empresasPorPais = [];

        foreach (DB::table('empresas')->whereNotNull('pais_id')->get(['id', 'pais_id']) as $empresa) {
            $empresasPorPais[$empresa->pais_id] ??= $empresa->id;
        }

        foreach ($sinEmpresa as $paquete) {
            $pais = $paquete->pais_id
                ? DB::table('paises')->find($paquete->pais_id, ['id', 'nombre', 'iso2'])
                : null;

            $clave = $pais?->id ?? 'sin-pais';

            $empresasPorPais[$clave] ??= DB::table('empresas')->insertGetId([
                'nombre' => ($pais->nombre ?? 'Operadora') . ' Tours',
                'correo' => 'tours-' . ($pais?->iso2 ?? 'demo') . '@hospedaya.test',
                'codigo_pais' => $pais?->iso2,
                'pais_id' => $pais?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('travel_packages')
                ->where('id', $paquete->id)
                ->update(['empresa_id' => $empresasPorPais[$clave]]);
        }
    }
};
