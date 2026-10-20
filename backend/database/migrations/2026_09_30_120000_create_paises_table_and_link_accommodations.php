<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Normaliza un nombre de país para comparar sin acentos ni mayúsculas, de
     * forma que "México" y "Mexico " se consoliden en la misma fila.
     */
    private function normaliza(string $valor): string
    {
        return Str::ascii(mb_strtolower(trim($valor)));
    }

    /**
     * Catálogo inicial de países.
     *
     * `iso2` es el código ISO 3166-1 alfa-2. Lo necesita schema.org para
     * `PostalAddress.addressCountry`, que recomienda expresarse con ese código en
     * lugar del nombre del país, y también para el og:locale por región.
     *
     * `idioma_principal` es el código ISO 639-1. Los cuatro países actuales
     * comparten español, pero el campo existe para que añadir un país de habla
     * distinta (Brasil, Estados Unidos) no requiera tocar el esquema.
     *
     * `texto_presentacion` es un mapa idioma -> texto, no un texto suelto, para
     * que la página de país sirva su versión correcta cuando haya traducciones
     * sin necesidad de otra migración.
     */
    private function paises(): array
    {
        return [
            [
                'nombre' => 'España',
                'slug' => 'espana',
                'iso2' => 'ES',
                'idioma_principal' => 'es',
                'texto_presentacion' => [
                    'es' => 'España reúne una oferta de hospedaje muy variada: hoteles históricos en Madrid y Barcelona, hostales en barrios con mucha vida, cabañas rurales en la Sierra y apartamentos turísticos en la costa. Elige la opción que encaje con tu viaje y reserva con confirmación inmediata.',
                ],
                'activo' => true,
            ],
            [
                'nombre' => 'México',
                'slug' => 'mexico',
                'iso2' => 'MX',
                'idioma_principal' => 'es',
                'texto_presentacion' => [
                    'es' => 'México combina resorts de playa en el Caribe y el Pacífico con escapadas de montaña y pueblos con carácter. Desde Cancún hasta Valle de Bravo, encontrarás alojamientos pensados para cada tipo de viaje.',
                ],
                'activo' => true,
            ],
            [
                'nombre' => 'Argentina',
                'slug' => 'argentina',
                'iso2' => 'AR',
                'idioma_principal' => 'es',
                'texto_presentacion' => [
                    'es' => 'Argentina es el destino ideal para quien busca naturaleza y aventura: cabañas a orillas de los lagos de la Patagonia, refugios de montaña y apartamentos en el centro de Buenos Aires.',
                ],
                'activo' => true,
            ],
            [
                'nombre' => 'Colombia',
                'slug' => 'colombia',
                'iso2' => 'CO',
                'idioma_principal' => 'es',
                'texto_presentacion' => [
                    'es' => 'Colombia es tan diversa como su geografía: desde la costa Caribe hasta los valles del café y la zona andina. Medellín es una buena base para descubrir el país.',
                ],
                'activo' => true,
            ],
        ];
    }

    public function up(): void
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->string('iso2', 2)->unique();
            $table->string('idioma_principal', 10)->default('es');
            // Mapa idioma -> texto de introducción de la página del país.
            $table->json('texto_presentacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $paises = $this->paises();

        foreach ($paises as $pais) {
            DB::table('paises')->insert([
                ...$pais,
                // El query builder no aplica los casts del modelo, así que el
                // mapa de textos se serializa a mano para la columna json.
                'texto_presentacion' => json_encode($pais['texto_presentacion'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 1. Columna nueva, nullable de momento para poder rellenar por pasos.
        Schema::table('alojamientos', function (Blueprint $table) {
            $table->foreignId('pais_id')
                  ->nullable()
                  ->after('provincia')
                  ->constrained('paises')
                  ->nullOnDelete();
        });

        // 2. Backfill: cada alojamiento apunta al país que ya tenía por nombre.
        $idsPorNombre = collect(DB::table('paises')->get(['id', 'nombre']))
            ->mapWithKeys(fn ($p) => [$this->normaliza($p->nombre) => $p->id]);

        $sinPais = [];

        foreach (DB::table('alojamientos')->select('id', 'pais')->get() as $alojamiento) {
            $paisId = $idsPorNombre->get($this->normaliza((string) $alojamiento->pais));

            if ($paisId) {
                DB::table('alojamientos')->where('id', $alojamiento->id)->update(['pais_id' => $paisId]);
            } else {
                $sinPais[] = $alojamiento->pais;
            }
        }

        // 3. No se borra la columna vieja si algún alojamiento quedó sin
        //    país conocido: es preferible que la migración falle con el dato
        //    exacto a perder información de forma silenciosa.
        if ($sinPais !== []) {
            throw new RuntimeException(
                'No se pudo asignar país a estos alojamientos: ' . implode(', ', array_unique($sinPais))
                . '. Añádelos a la tabla paises y vuelve a ejecutar la migración.'
            );
        }

        Schema::table('alojamientos', function (Blueprint $table) {
            $table->dropColumn('pais');
        });
    }

    public function down(): void
    {
        Schema::table('alojamientos', function (Blueprint $table) {
            $table->string('pais', 100)->nullable()->after('provincia');
        });

        // Se recupera el nombre legible desde la tabla de países.
        foreach (DB::table('alojamientos')->select('id', 'pais_id')->get() as $alojamiento) {
            $nombre = $alojamiento->pais_id
                ? DB::table('paises')->where('id', $alojamiento->pais_id)->value('nombre')
                : null;

            DB::table('alojamientos')->where('id', $alojamiento->id)->update(['pais' => $nombre]);
        }

        Schema::table('alojamientos', function (Blueprint $table) {
            $table->dropForeign(['pais_id']);
            $table->dropColumn('pais_id');
        });

        Schema::dropIfExists('paises');
    }
};
