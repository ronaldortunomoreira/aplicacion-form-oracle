<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Imagen;
use App\Models\TravelPackage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Pone la tabla `imagenes` al día con lo que hay en disco.
 *
 * Hace dos cosas, en este orden:
 *
 *   1. Baja a la carpeta de cada recurso las URLs que estaban guardadas como
 *      texto en las columnas JSON (`alojamientos.imagenes`,
 *      `travel_packages.imagen` y `travel_packages.imagenes`). Esas columnas
 *      dejan de ser una fuente de galería y pasan a ser solo de dónde salen los
 *      archivos.
 *   2. Vuelca a la tabla los nombres de los archivos que hay en cada carpeta:
 *
 *        - images/alojamientos/<carpeta>/<slug>/   fotos propias del alojamiento
 *        - images/alojamientos/<carpeta>/          fotos del tipo, compartidas
 *        - images/paquetes/<slug>/                 fotos del paquete
 *
 * La descarga es idempotente: el nombre del archivo sale de la URL, así que
 * re-sincronizar no vuelve a bajar lo que ya está en disco ni mueve nada. Lo
 * que se descarga y después se quita de la columna, se borra de la carpeta.
 *
 * Al crear la subcarpeta propia de un alojamiento que no tenía ninguna, ese
 * alojamiento pasa a tener fotos propias y deja de heredar las de su tipo.
 */
class SincronizarImagenes extends Command
{
    protected $signature = 'imagenes:sincronizar';

    protected $description = 'Descarga las fotos de las columnas JSON a las carpetas públicas y vuelca los archivos a la tabla `imagenes`';

    /** Extensiones que se consideran imagen al leer una carpeta. */
    private const EXTENSIONES = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

    /** Tamaño máximo de una foto descargada. */
    private const MAXIMO_BYTES = 5242880;

    /**
     * Marca de los archivos que ha descargado este comando. Sirve para distinguir
     * lo que puso el administrador a mano, que no se debe borrar nunca.
     */
    private const PREFIJO_DESCARGA = 'd-';

    public function handle(): int
    {
        $this->descargarColumnasJson();

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
     */
    private function sincronizarAlojamientos(): int
    {
        $total = 0;

        foreach (Accommodation::query()->with('accommodationType')->get() as $alojamiento) {
            $carpeta = $alojamiento->carpetaFotos();

            $total += $this->volcar(
                'alojamiento',
                $alojamiento->id,
                $carpeta ? $this->archivosEn(public_path($carpeta)) : []
            );
        }

        return $total;
    }

    /** Fotos de cada paquete, de su carpeta. */
    private function sincronizarPaquetes(): int
    {
        $total = 0;

        foreach (TravelPackage::all() as $paquete) {
            $total += $this->volcar('paquete', $paquete->id, $this->archivosEn(public_path($paquete->carpetaFotos())));
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
     * Baja a la carpeta de cada recurso las URLs que las columnas JSON tenían
     * guardadas como texto.
     */
    private function descargarColumnasJson(): void
    {
        foreach (Accommodation::query()->with('accommodationType')->get() as $alojamiento) {
            // Sin tipo no hay carpeta posible, así que esas URLs se quedan como
            // están y la galería las seguirá sirviendo desde la columna.
            $carpeta = $alojamiento->carpetaFotos();

            if ($carpeta) {
                $this->descargarEn($carpeta, $alojamiento->imagenes ?? []);
            }
        }

        foreach (TravelPackage::all() as $paquete) {
            $this->descargarEn(
                $paquete->carpetaFotos(),
                array_merge(array_filter([$paquete->imagen]), $paquete->imagenes ?? [])
            );
        }
    }

    /**
     * Descarga a una carpeta las URLs de la lista y borra las que ya no estén.
     *
     * @param array<int, mixed> $urls
     */
    private function descargarEn(string $carpetaRelativa, array $urls): void
    {
        $absoluta = public_path($carpetaRelativa);
        $esperados = [];

        foreach ($urls as $url) {
            if (! is_string($url) || $url === '') {
                continue;
            }

            $base = $this->nombreBase($url);

            if (! $base) {
                continue;
            }

            $esperados[] = $base;

            // El nombre sale de la URL, así que si el archivo ya está en disco no
            // se vuelve a descargar.
            if (! empty(File::glob($absoluta . '/' . $base . '.*'))) {
                continue;
            }

            $this->descargar($url, $absoluta, $base);
        }

        $this->borrarDescargasHuerfanas($absoluta, $esperados);
    }

    /**
     * Nombre base, sin extensión, de la foto descargada de una URL.
     *
     * Sale del hash de la URL y no de su posición, para que el archivo sea
     * estable: re-sincronizar no lo renombra ni lo vuelve a bajar, y se puede
     * saber cuál es el suyo mirando solo el disco.
     */
    private function nombreBase(string $url): ?string
    {
        if (! preg_match('#^https?://#i', $url)) {
            return null;
        }

        return self::PREFIJO_DESCARGA . substr(sha1($url), 0, 16);
    }

    /** Escribe una URL en la carpeta. Devuelve false si no se pudo descargar. */
    private function descargar(string $url, string $carpetaAbsoluta, string $base): bool
    {
        try {
            $respuesta = Http::timeout(15)
                ->retry(2, 200)
                ->withHeaders(['Accept' => 'image/*'])
                ->get($url);
        } catch (Throwable $e) {
            $this->warn("No se pudo descargar {$url}: {$e->getMessage()}");

            return false;
        }

        if (! $respuesta->successful()) {
            $this->warn("No se pudo descargar {$url}: HTTP {$respuesta->status()}");

            return false;
        }

        $cuerpo = $respuesta->body();

        if ($cuerpo === '') {
            $this->warn("No se pudo descargar {$url}: respuesta vacia.");

            return false;
        }

        if (strlen($cuerpo) > self::MAXIMO_BYTES) {
            $this->warn('Descartada ' . $url . ': supera ' . self::MAXIMO_BYTES . ' bytes.');

            return false;
        }

        File::ensureDirectoryExists($carpetaAbsoluta);
        File::put($carpetaAbsoluta . '/' . $base . '.' . $this->extensionDe($respuesta->header('Content-Type')), $cuerpo);

        return true;
    }

    /**
     * Extensión del archivo descargado, según el Content-Type del servidor. Un
     * servidor que no lo manda se asume jpeg.
     */
    private function extensionDe(?string $contentType): string
    {
        $mapa = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
        ];

        $tipo = strtolower(trim(explode(';', $contentType ?? '')[0]));

        return $mapa[$tipo] ?? 'jpg';
    }

    /**
     * Borra de una carpeta las descargas cuya URL ya no está en la columna
     * JSON. Los archivos con otro nombre, que son los que puso el
     * administrador, no se tocan.
     *
     * @param array<int, string> $esperados
     */
    private function borrarDescargasHuerfanas(string $carpetaAbsoluta, array $esperados): void
    {
        if (! File::isDirectory($carpetaAbsoluta)) {
            return;
        }

        foreach (File::glob($carpetaAbsoluta . '/' . self::PREFIJO_DESCARGA . '*') as $ruta) {
            $nombre = basename($ruta);

            // El nombre es d-<hash>.<extension>, y el hash no lleva puntos.
            if (! in_array(explode('.', $nombre)[0], $esperados, true)) {
                File::delete($ruta);
                $this->info("Descarga huerfana borrada: {$nombre}");
            }
        }
    }
}
