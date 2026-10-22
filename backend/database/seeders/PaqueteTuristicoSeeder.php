<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Empresa;
use App\Models\Pais;
use App\Models\PaqueteTuristico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Un tour de un día por cada destino destacado.
 *
 * El paquete ya no guarda su país ni su destino: depende de la empresa que lo
 * publica y el país lo hereda de ella. Por eso el seeder crea antes una empresa
 * de demo por país (si no existe ya una con ese correo) y enlaza cada paquete
 * con la de su país.
 *
 * No está cableado en DatabaseSeeder: se ejecuta a mano con
 * `php artisan db:seed --class=PaqueteTuristicoSeeder`.
 */
class PaqueteTuristicoSeeder extends Seeder
{
    /** Empresas usadas durante la ejecución, indexadas por id de país. */
    private array $empresasPorPais = [];

    public function run(): void
    {
        $destinos = Destination::where('destacado', true)->take(20)->get();

        if ($destinos->isEmpty()) {
            return;
        }

        foreach ($destinos as $d) {
            $slug = 'tour-' . Str::slug($d->nombre) . '-1-dia';

            if (PaqueteTuristico::where('slug', $slug)->exists()) {
                continue;
            }

            PaqueteTuristico::create([
                'empresa_id' => $this->empresaDe($d->pais_id),
                'nombre' => 'Tour ' . $d->nombre . ' 1 Dia',
                'slug' => $slug,
                'ciudad' => $d->ciudad,
                'texto_presentacion' => ['es' => 'Experiencia completa.', 'en' => 'Full experience.'],
                'incluye' => ['Guia', 'Transporte'],
                'no_incluye' => ['Comidas'],
                'itinerario' => [['hora' => '09:00', 'actividad' => 'Inicio']],
                'duracion' => '1 dia',
                'min_personas' => 1,
                'max_personas' => 12,
                'precio_desde' => 500,
                // La moneda sigue saliendo del país del destino, que es el mismo
                // que el de la empresa: `destinos` no se toca.
                'moneda_iso' => $d->country?->moneda_iso ?? 'USD',
                'destacado' => true,
                'estado' => true,
            ]);
        }
    }

    /**
     * Empresa que publica los paquetes de un país: la primera que ya exista para
     * ese país, o una de demo creada al vuelo.
     */
    private function empresaDe(?int $paisId): int
    {
        $clave = $paisId ?? 'sin-pais';

        if (isset($this->empresasPorPais[$clave])) {
            return $this->empresasPorPais[$clave];
        }

        $pais = $paisId ? Pais::find($paisId) : null;
        // El correo es único en `empresas`, así que sirve de identificador para
        // no crear la misma empresa de demo dos veces al reejecutar el seeder.
        $correo = 'tours-' . ($pais?->iso2 ?? 'demo') . '@hospedaya.test';

        $this->empresasPorPais[$clave] = Empresa::where('correo', $correo)->first()?->id
            ?? Empresa::create([
                'name' => ($pais->nombre ?? 'Operadora') . ' Tours',
                'email' => $correo,
                'country_code' => $pais?->iso2,
                'pais_id' => $paisId,
            ])->id;

        return $this->empresasPorPais[$clave];
    }
}
