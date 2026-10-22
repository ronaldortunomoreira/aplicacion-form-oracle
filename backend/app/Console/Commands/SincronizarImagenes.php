<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Imagen;
use App\Models\PaqueteTuristico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Pone la tabla `imagenes` al día con lo que hay en disco.
 *
 * Vuelca a la tabla los nombres de los archivos que hay en cada carpeta:
 *
 *        - images/alojamientos/<carpeta>/<slug>/   fotos propias del alojamiento
 *        - images/alojamientos/<carpeta>/          fotos del tipo, compartidas
 *        - images/paquetes/<slug>/                 fotos del paquete
 *
 * Cada carpeta queda con sus fotos numeradas 01.jpg, 02.jpg... de forma
 * consecutiva, sin huecos. La carpeta es la única verdad de la galería y el
 * comando no toca la red.
 */
class SincronizarImagenes extends Command
{
    protected $signature = 'imagenes:sincronizar';

    protected $description = 'Vuelca los archivos de las carpetas públicas a la tabla `imagenes`';

    /** Extensiones que se consideran imagen al leer una carpeta. */
    private const EXTENSIONES = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

    public function handle(): int
    {
        $total = 0;
        $total += $this->sincronizarTiposDeAlojamiento();
        $total += $this->sincronizarAlojamientos();
        $total += $this->sincronizarPaquetes();

        $this->info("{$total} fotos en la tabla `imagenes`.");

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
            $carpeta = $tipo->carpetaFotos();

            if (! File::isDirectory(public_path($carpeta))) {
                $this->warn("Sin carpeta publica '{$carpeta}' para el tipo '{$tipo->slug}' (id {$tipo->id}).");
            }

            $total += $this->volcar('tipo_alojamiento', $tipo->id, $this->archivosEn(public_path($carpeta)));
        }

        return $total;
    }

    /**
     * Fotos propias de cada alojamiento, las de su subcarpeta.
     *
     * Un alojamiento sin subcarpeta propia no recibe ninguna fila: el modelo cae
     * a las filas de su tipo, que son las mismas para todos los alojamientos de
     * ese tipo. Copiarlas aquí sería duplicar la galería y, además, dejaría al
     * modelo sin forma de saber que esas fotos viven en la carpeta del tipo y no
     * en la subcarpeta del alojamiento.
     *
     * A diferencia de la carpeta del tipo, la subcarpeta propia se renumera
     * (01.jpg, 02.jpg...) porque es de un solo alojamiento. La del tipo se sirve
     * tal cual está en disco, sin tocarla.
     */
    private function sincronizarAlojamientos(): int
    {
        $total = 0;

        foreach (Accommodation::query()->with('accommodationType')->get() as $alojamiento) {
            $carpeta = $alojamiento->carpetaFotos();

            if (! $carpeta) {
                $total += $this->volcar('alojamiento', $alojamiento->id, []);

                continue;
            }

            $absoluta = public_path($carpeta);
            $enDisco = $this->imagenesEnDisco($absoluta);

            if ($enDisco !== []) {
                $this->renumerar($absoluta, $enDisco);
            }

            $total += $this->volcar('alojamiento', $alojamiento->id, $this->archivosEn($absoluta));
        }

        return $total;
    }

    /**
     * Fotos de cada paquete, de su carpeta.
     *
     * La carpeta de un paquete es suya y de nadie más, así que se renumera
     * (01.jpg, 02.jpg...) para que el orden sea consecutivo y estable.
     */
    private function sincronizarPaquetes(): int
    {
        $total = 0;

        foreach (PaqueteTuristico::all() as $paquete) {
            $absoluta = public_path($paquete->carpetaFotos());
            $enDisco = $this->imagenesEnDisco($absoluta);

            if ($enDisco !== []) {
                $this->renumerar($absoluta, $enDisco);
            }

            $total += $this->volcar('paquete', $paquete->id, $this->archivosEn($absoluta));
        }

        return $total;
    }

    /**
     * Nombres de los archivos de imagen de una carpeta, ordenados de forma
     * natural (01, 02, ... 10) para que el orden sea predecible. Devuelve una
     * lista vacía si la carpeta no existe.
     *
     * @return array<int, string>
     */
    private function archivosEn(string $absoluta): array
    {
        if ($absoluta === '' || ! File::isDirectory($absoluta)) {
            return [];
        }

        return collect(File::files($absoluta))
            ->filter(fn ($archivo) => in_array(strtolower($archivo->getExtension()), self::EXTENSIONES, true))
            ->sortBy(fn ($archivo) => $archivo->getFilename(), SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn ($archivo) => $archivo->getFilename())
            ->values()
            ->all();
    }

    /**
     * Reemplaza todas las fotos de un recurso por la lista que se le pase.
     *
     * @param array<int, string> $archivos
     */
    private function volcar(string $tipo, int $id, array $archivos): int
    {
        Imagen::where('recurso_tipo', $tipo)
            ->where('recurso_id', $id)
            ->delete();

        foreach ($archivos as $orden => $archivo) {
            Imagen::create([
                'recurso_tipo' => $tipo,
                'recurso_id' => $id,
                'archivo' => $archivo,
                'orden' => $orden,
            ]);
        }

        return count($archivos);
    }

    /**
     * Rutas de las imágenes de una carpeta, en orden natural por nombre.
     *
     * @return array<int, string>
     */
    private function imagenesEnDisco(string $absoluta): array
    {
        if (! File::isDirectory($absoluta)) {
            return [];
        }

        return collect(File::files($absoluta))
            ->filter(fn ($archivo) => in_array(strtolower($archivo->getExtension()), self::EXTENSIONES, true))
            ->sortBy(fn ($archivo) => $archivo->getFilename(), SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn ($archivo) => $archivo->getPathname())
            ->values()
            ->all();
    }

    /**
     * Renombra una lista de archivos a 01.ext, 02.ext... de forma consecutiva y
     * borra los que queden sin sitio.
     *
     * Se pasa por nombres intermedios porque los originales también están
     * numerados: mover 01 a 02 pisaría el 02 si no se saca antes de en medio.
     *
     * @param array<int, string> $rutas
     */
    private function renumerar(string $absoluta, array $rutas): void
    {
        if (! File::isDirectory($absoluta) || $rutas === []) {
            return;
        }

        // 1. Todo a un nombre intermedio, para no pisarse entre sí.
        $intermedios = [];

        foreach ($rutas as $ruta) {
            $temporal = $absoluta . '/tmp-ren-' . uniqid() . '.' . pathinfo($ruta, PATHINFO_EXTENSION);
            File::move($ruta, $temporal);
            $intermedios[] = $temporal;
        }

        // 2. Cada intermedio a su número final.
        $finales = [];

        foreach ($intermedios as $indice => $temporal) {
            $numero = str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT);
            $final = $absoluta . '/' . $numero . '.' . pathinfo($temporal, PATHINFO_EXTENSION);
            File::move($temporal, $final);
            $finales[] = $this->normalizar($final);
        }

        // 3. Cualquier imagen que no sea una de las finales sobra. Se limpian
        //    también los temporales de una ejecución que se cortara a medias.
        foreach ($this->imagenesEnDisco($absoluta) as $ruta) {
            if (! in_array($this->normalizar($ruta), $finales, true)) {
                File::delete($ruta);
                $this->info('Foto sobrante borrada: ' . basename($ruta));
            }
        }
    }

    /**
     * Ruta con separadores "/", para poder comparar rutas sin depender de si el
     * sistema usa "\" (Windows) o "/".
     */
    private function normalizar(string $ruta): string
    {
        return str_replace('\\', '/', $ruta);
    }
}
