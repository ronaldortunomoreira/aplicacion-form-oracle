<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Pais;
use App\Models\PaqueteTuristico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Las fotos de un paquete turístico se toman de la carpeta pública
 * public/images/paquetes/{slug}/, que es la única verdad de su galería.
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
    private function crearPaqueteConFotos(array $archivos = [], array $extra = []): PaqueteTuristico
    {
        $slug = 'tour-fotos-' . uniqid();
        $carpeta = public_path('images/paquetes/' . $slug);
        File::ensureDirectoryExists($carpeta);
        $this->carpetas[] = $carpeta;

        foreach ($archivos as $nombre) {
            File::put($carpeta . '/' . $nombre, 'no-es-una-imagen-real');
        }

        $paquete = PaqueteTuristico::create($extra + [
            'empresa_id' => $this->crearEmpresa()->id,
            'nombre' => 'Tour Fotos',
            'slug' => $slug,
            'precio_desde' => 100,
            'moneda_iso' => 'BOB',
            'activo' => true,
        ]);

        $this->sincronizar();

        return $paquete;
    }

    /**
     * Empresa de prueba: `empresa_id` es obligatorio en los paquetes, así que
     * no se puede crear uno sin una empresa detrás.
     */
    private function crearEmpresa(): Empresa
    {
        return Empresa::create([
            'name' => 'Operadora Fotos',
            'email' => 'operadora-fotos-' . uniqid() . '@example.com',
            'country_code' => 'BO',
            'pais_id' => Pais::where('iso2', 'BO')->first()?->id,
        ]);
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

        // Ordenadas por nombre de archivo: 01 antes que 02. El comando renumera
        // la carpeta, así que los nombres finales son consecutivos.
        $this->assertSame(
            [$base . '01.jpg', $base . '02.jpg'],
            $response->json('data.images')
        );

        // La portada es la primera foto que hay en la carpeta.
        $response->assertJsonPath('data.image', $base . '01.jpg');
    }

    public function test_se_ignoran_los_archivos_que_no_son_imagen(): void
    {
        $paquete = $this->crearPaqueteConFotos(['foto.webp', 'notas.txt', 'LEEME.md']);

        $imagenes = $this->getJson('/api/travel-packages/' . $paquete->slug)->json('data.images');

        $this->assertSame(
            ['http://localhost/images/paquetes/' . $paquete->slug . '/01.webp'],
            $imagenes
        );
    }

    public function test_un_paquete_sin_fotos_en_la_carpeta_no_tiene_galeria(): void
    {
        // La carpeta es la única verdad: el paquete ya no guarda ninguna URL, así
        // que sin archivos en disco la galería sale vacía.
        $paquete = PaqueteTuristico::create([
            'empresa_id' => $this->crearEmpresa()->id,
            'nombre' => 'Tour Sin Fotos',
            'slug' => 'tour-sin-fotos-' . uniqid(),
            'precio_desde' => 100,
            'moneda_iso' => 'BOB',
            'activo' => true,
        ]);

        $this->carpetas[] = public_path('images/paquetes/' . $paquete->slug);

        $this->sincronizar();

        $response = $this->getJson('/api/travel-packages/' . $paquete->slug);

        $response->assertJsonPath('data.images', []);
        $response->assertJsonPath('data.image', null);
    }
}
