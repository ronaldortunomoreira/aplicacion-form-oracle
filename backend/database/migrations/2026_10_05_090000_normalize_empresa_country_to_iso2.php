<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use libphonenumber\PhoneNumberUtil;

/**
 * `codigo_pais` pasó a guardar el código ISO2 (ES, MX, AR...) en lugar del
 * prefijo internacional (+34, +52...).
 *
 * El prefijo no servía como identificador de país: +34 y +52 son únicos, pero
 * +1 lo comparten veinticuatro países y +44 cuatro, así que al ofrecer todos los
 * países en el formulario el mismo prefijo describiría varios a la vez y la
 * validación no sabría cuál rechazar. Con el ISO2 cada entrada del selector es
 * unívoca, y el prefijo se deriva de él con libphonenumber.
 *
 * Esta migración reescribe las filas anteriores. Se resuelven los prefijos
 * usando el catálogo `paises` del propio proyecto, y lo que no se pueda resolver
 * se deja tal cual: es preferible conservar un dato antiguo sin normalizar antes
 * que borrarlo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresas') || !Schema::hasColumn('empresas', 'codigo_pais')) {
            return;
        }

        $util = PhoneNumberUtil::getInstance();

        // Prefijo internacional -> ISO2, según los países del catálogo. El
        // proyecto tiene un solo país por prefijo, así que no hay ambigüedad.
        $porPrefijo = [];

        foreach (DB::table('paises')->get(['iso2']) as $pais) {
            $porPrefijo['+' . $util->getCountryCodeForRegion($pais->iso2)] = $pais->iso2;
        }

        DB::table('empresas')
            ->whereNotNull('codigo_pais')
            ->where('codigo_pais', 'like', '+%')
            ->orderBy('id')
            ->each(function ($empresa) use ($porPrefijo) {
                $iso2 = $porPrefijo[$empresa->codigo_pais] ?? null;

                if ($iso2 === null) {
                    return;
                }

                DB::table('empresas')
                    ->where('id', $empresa->id)
                    ->update(['codigo_pais' => $iso2]);
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresas') || !Schema::hasColumn('empresas', 'codigo_pais')) {
            return;
        }

        $util = PhoneNumberUtil::getInstance();

        DB::table('empresas')
            ->whereNotNull('codigo_pais')
            ->orderBy('id')
            ->each(function ($empresa) use ($util) {
                if (!in_array($empresa->codigo_pais, $util->getSupportedRegions(), true)) {
                    return;
                }

                DB::table('empresas')
                    ->where('id', $empresa->id)
                    ->update([
                        'codigo_pais' => '+' . $util->getCountryCodeForRegion($empresa->codigo_pais),
                    ]);
            });
    }
};