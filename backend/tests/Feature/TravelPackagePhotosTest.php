<?php

namespace Tests\Feature;

use App\Models\TravelPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Las fotos de un paquete turístico se toman de la carpeta pública
 * public/images/paquetes/{slug}/ y de las columnas `imagen` e `imagenes`.
 *
 * El modelo no lee las carpetas en cada petición: las vuelca a la tabla
 * `imagenes` el comando imagenes:sincronizar, y son las que se sirven. Por eso
 * cada test ejecuta ese comando justo después de colocar sus archivos.
 *
 * Estos tests comprueban que la carpeta manda sobre la base de datos, que el
 * orden es estable y que los archivos que no son imágenes se ignoran.
 */
class TravelPackagePhotosTest extends TestCase
{
    use RefreshDatabase;

    /** Carpetas temporales creadas por cada test, para borrarlas al terminar. */
    private array $carpetas = [];

    protected function tearDown(): void
    {
        foreach ($this->carpetas as $carpeta) {
            File::deleteDirectory($carpeta);
        }

        parent::tearDown();
    }

    /** Crea un paquete y, si se piden, archivos dentro de su carpeta de fotos. */
    private function crearPaqueteConFotos(array $archivos = [], array $extra = []): TravelPackage
    {
        $slug = 'tour-fotos-' . uniqid();
        $carpeta = public_path('images/paquetes/' . $slug);
        File::ensureDirectoryExists($carpeta);
        $this->carpetas[] = $carpeta;

        foreach ($archivos as $nombre) {
            File::put($carpeta . '/' . $nombre, 'no-es-una-imagen-real');
        }

        $paquete = TravelPackage::create($extra + [
            'nombre' => 'Tour Fotos',
            'slug' => $slug,
            'precio_desde' => 100,
            'moneda_iso' => 'BOB',
            'activo' => true,
        ]);

        $this->sincronizar();

        return $paquete;
    }

    /** Vuelca las carpetas y las columnas JSON a la tabla `imagenes`. */
    private function sincronizar(): void
    {
        Artisan::call('imagenes:sincronizar');
    }

    public function test_las_fotos_de_la_carpeta_aparecen_ordenadas_en_la_galeria(): void
    {
        $paquete = $this->crearPaqueteConFotos(['02-salon.jpg', '01-portada.jpg']);

        $response = $this->getJson('/api/travel-packages/' . $paquete->slug);

        $response->assertStatus(200);

        $base = 'http://localhost/images/paquetes/' . $paquete->slug . '/';

        // Ordenadas por nombre de archivo: 01 antes que 02.
        $this->assertSame(
            [$base . '01-portada.jpg', $base . '02-salon.jpg'],
            $response->json('data.images')
        );

        // La portada es la primera foto que hay en la carpeta.
        $response->assertJsonPath('data.image', $base . '01-portada.jpg');
    }

    public function test_se_ignoran_los_archivos_que_no_son_imagen(): void
    {
        $paquete = $this->crearPaqueteConFotos(['foto.webp', 'notas.txt', 'LEEME.md']);

        $imagenes = $this->getJson('/api/travel-packages/' . $paquete->slug)->json('data.images');

        $this->assertSame(
            ['http://localhost/images/paquetes/' . $paquete->slug . '/foto.webp'],
            $imagenes
        );
    }

    public function test_sin_fotos_en_la_carpeta_se_usan_las_de_la_base_de_datos(): void
    {
        $paquete = TravelPackage::create([
            'nombre' => 'Tour Solo DB',
            'slug' => 'tour-solo-db-' . uniqid(),
            'precio_desde' => 100,
            'moneda_iso' => 'BOB',
            'activo' => true,
            'imagen' => 'https://cdn.example.com/portada.jpg',
            'imagenes' => ['https://cdn.example.com/extra.jpg'],
        ]);

        $this->sincronizar();

        $imagenes = $this->getJson('/api/travel-packages/' . $paquete->slug)->json('data.images');

        $this->assertSame(
            ['https://cdn.example.com/portada.jpg', 'https://cdn.example.com/extra.jpg'],
            $imagenes
        );
    }
}
