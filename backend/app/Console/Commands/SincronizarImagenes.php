<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Imagen;
use App\Models\TravelPackage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Vuelca a la tabla `imagenes` (la única verdad sobre las galerías) todo lo
 * que hasta ahora vivía repartido en carpetas públicas y en columnas JSON:
 *
 *   - public/images/alojamientos/<carpeta>/<slug>/  (una carpeta por alojamiento)
 *   - public/images/alojamientos/<carpeta>/         (fotos del tipo, compartidas)
 *   - public/images/paquetes/<slug>/                (una carpeta por paquete)
 *   - alojamientos.imagenes, travel_packages.imagen e travel_packages.imagenes
 *
 * Borra y vuelve a insertar las filas cuyo `origen` es 'carpeta' o 'bd' para cada
 * recurso. Las filas de `origen` 'panel' (futuras subidas desde el panel) no se
 * tocan.
 */
class SincronizarImagenes extends Command
{
    protected $signature = 'imagenes:sincronizar';

    protected $description = 'Vuelca las fotos de las carpetas públicas y de las columnas JSON a la tabla `imagenes`';

    /** Extensiones que se consideran imagen al leer una carpeta. */
    private const EXTENSIONES = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

    /**
     * Carpeta pública de fotos de cada tipo de alojamiento. El slug es el mismo
     * que viaja en la URL (/alojamientos?type_slug=hotel) y el nombre de carpeta
     * es la forma legible que ve el administrador al arrastrar las imágenes:
     * public/images/alojamientos/<carpeta>/.
     */
    private const CARPETAS_TIPO = [
        'hotel' => 'hoteles',
        'hostal' => 'hostales',
        'cabana' => 'cabanas',
        'apartamento' => 'apartamentos',
        'glamping' => 'glamping',
    ];

    public function handle(): int
    {
        $total = 0;
        $total += $this->sincronizarTiposDeAlojamiento();
        $total += $this->sincronizarAlojamientos();
        $total += $this->sincronizarPaquetes();

        $this->info("{$total} fotos sincronizadas en la tabla `imagenes`.");

        return self::SUCCESS;
    }

    /**
     * Fotos del tipo de alojamiento, que sirven de galería a todos los
     * alojamientos de ese tipo que no tengan carpeta propia.
     */
    private function sincronizarTiposDeAlojamiento(): int
    {
        $total = 0;

        foreach (AccommodationType::all() as $tipo) {
            $carpeta = self::CARPETAS_TIPO[$tipo->slug] ?? null;

            if (! $carpeta) {
                $this->warn("Sin carpeta publica para el tipo '{$tipo->slug}' (id {$tipo->id}); sus fotos de la BD se importan igual.");

                $carpeta = $tipo->slug;
            }

            $rutas = $this->desdeCarpeta('images/alojamientos/' . $carpeta);
            $total += $this->guardarFotos('tipo_alojamiento', $tipo->id, $rutas);
        }

        return $total;
    }

    /**
     * Fotos de cada alojamiento: su carpeta propia, y si no tiene, la carpeta de
     * su tipo. Al final se añaden las URLs de la columna `imagenes`, sin repetir.
     */
    private function sincronizarAlojamientos(): int
    {
        $total = 0;

        foreach (Accommodation::query()->with('accommodationType')->get() as $alojamiento) {
            $carpeta = self::CARPETAS_TIPO[$alojamiento->accommodationType?->slug]
                ?? $alojamiento->accommodationType?->slug;

            $rutas = [];

            if ($carpeta) {
                // La carpeta propia del alojamiento manda; si no tiene fotos, se
                // cae a la del tipo (compartida por todos los de ese tipo).
                $relativa = 'images/alojamientos/' . $carpeta . '/' . $alojamiento->slug;
                $rutas = $this->desdeCarpeta($relativa);

                if (empty($rutas)) {
                    $rutas = $this->desdeCarpeta('images/alojamientos/' . $carpeta);
                }
            }

            $rutas = $this->unir($rutas, $alojamiento->imagenes);

            $total += $this->guardarFotos('alojamiento', $alojamiento->id, $rutas);
        }

        return $total;
    }

    /**
     * Fotos de cada paquete: la carpeta de su paquete y después la portada
     * (`imagen`) y las URLs de la columna `imagenes`, sin repetir.
     */
    private function sincronizarPaquetes(): int
    {
        $total = 0;

        foreach (TravelPackage::all() as $paquete) {
            $rutas = $this->desdeCarpeta('images/paquetes/' . $paquete->slug);
            $rutas = $this->unir($rutas, array_merge([$paquete->imagen], $paquete->imagenes ?? []));

            $total += $this->guardarFotos('paquete', $paquete->id, $rutas);
        }

        return $total;
    }

    /**
     * Nombres de los archivos de imagen de una carpeta, ordenados de forma
     * natural (01, 02, ... 10) para que la portada sea predecible. Devuelve una
     * lista vacía si la carpeta no existe.
     *
     * @return array<int, string>
     */
    private function desdeCarpeta(string $rutaRelativa): array
    {
        $absoluta = public_path($rutaRelativa);

        if (! File::isDirectory($absoluta)) {
            return [];
        }

        return collect(File::files($absoluta))
            ->filter(fn ($archivo) => in_array(strtolower($archivo->getExtension()), self::EXTENSIONES, true))
            ->sortBy(fn ($archivo) => $archivo->getFilename(), SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn ($archivo) => '/' . $rutaRelativa . '/' . rawurlencode($archivo->getFilename()))
            ->values()
            ->all();
    }

    /**
     * Añade al final de una lista de rutas las URLs que vienen de la base de
     * datos, descartando vacías y repetidas (tanto con carpeta como entre sí).
     *
     * @return array<int, string>
     */
    private function unir(array $rutas, ?array $urls): array
    {
        foreach ($urls ?? [] as $url) {
            if (is_string($url) && $url !== '' && ! in_array($url, $rutas, true)) {
                $rutas[] = $url;
            }
        }

        return array_values($rutas);
    }

    /**
     * Reemplaza las fotos de un recurso. Borra solo las filas de los orígenes que
     * gestiona este comando (carpeta y base de datos); las de origen 'panel' se
     * conservan.
     *
     * @param array<int, string> $rutas
     */
    private function guardarFotos(string $tipo, int $id, array $rutas): int
    {
        Imagen::where('recurso_tipo', $tipo)
            ->where('recurso_id', $id)
            ->whereIn('origen', ['carpeta', 'bd'])
            ->delete();

        foreach ($rutas as $orden => $ruta) {
            Imagen::create([
                'recurso_tipo' => $tipo,
                'recurso_id' => $id,
                'ruta' => $ruta,
                'orden' => $orden,
                'es_portada' => $orden === 0,
                // Las rutas de carpeta salen de archivos del servidor; las de la
                // base de datos traen el host ya puesto, así que no hacen falta.
                'origen' => str_starts_with($ruta, '/') ? 'carpeta' : 'bd',
            ]);
        }

        return count($rutas);
    }
}
