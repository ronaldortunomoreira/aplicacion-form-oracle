<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Pais;
use App\Models\RoomUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Las fotos de un alojamiento se toman de la carpeta pública de su tipo,
 * public/images/alojamientos/<tipo>/ (hoteles, hostales, cabanas, apartamentos,
 * glamping), y dentro de ella puede haber una subcarpeta por alojamiento con el
 * slug como nombre (<tipo>/<slug>/) para darle fotos propias.
 *
 * El modelo no lee las carpetas en cada petición: las vuelca a la tabla
 * `imagenes` el comando imagenes:sincronizar, y son las que se sirven. Por eso
 * cada test ejecuta ese comando justo después de colocar sus archivos.
 *
 * Los tests se crean sus propios alojamientos con un slug aleatorio para no
 * depender de las fotos reales que pueda haber en las carpetas del proyecto.
 */
class AccommodationPhotosTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'http://localhost';

    /** Archivos creados por cada test, para borrarlos al terminar. */
    private array $archivos = [];

    /** Subcarpetas creadas por cada test, para borrarlas enteras al terminar. */
    private array $subcarpetas = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    protected function tearDown(): void
    {
        foreach ($this->archivos as $archivo) {
            File::delete($archivo);
        }

        foreach ($this->subcarpetas as $carpeta) {
            File::deleteDirectory($carpeta);
        }

        parent::tearDown();
    }

    /** Crea un alojamiento de un tipo con un slug aleatorio y datos mínimos. */
    private function crearAlojamiento(string $tipoSlug, array $extra = []): Accommodation
    {
        $tipo = AccommodationType::where('slug', $tipoSlug)->firstOrFail();
        $pais = Pais::firstOrFail();

        return Accommodation::create(array_merge([
            'accommodation_type_id' => $tipo->id,
            'name' => 'Alojamiento de prueba',
            'slug' => 'prueba-' . $tipoSlug . '-' . Str::lower(Str::random(8)),
            'description' => 'Alojamiento creado solo para los tests de fotos.',
            'address' => 'Calle de prueba 1',
            'city' => 'Ciudad de prueba',
            'country_id' => $pais->id,
            'status' => 'active',
            'images' => [],
        ], $extra));
    }

    /** Crea una unidad de habitación con inventario suficiente para reservar. */
    private function crearUnidad(Accommodation $alojamiento): RoomUnit
    {
        return RoomUnit::create([
            'accommodation_id' => $alojamiento->id,
            'name' => 'Unidad de prueba',
            'unit_type' => 'private_room',
            'capacity_adults' => 2,
            'capacity_children' => 0,
            'total_units' => 3,
            'base_price_per_night' => 100,
            'cleaning_fee' => 0,
            'bathroom_type' => 'private',
            'images' => [],
        ]);
    }

    /** Añade una foto a la carpeta de un tipo (compartida por todos del tipo). */
    private function fotoEnCarpetaTipo(string $carpeta, string $nombre): string
    {
        return $this->escribirFoto(public_path('images/alojamientos/' . $carpeta), $nombre, false);
    }

    /** Añade una foto a la subcarpeta propia de un alojamiento (por slug). */
    private function fotoEnSubcarpeta(string $carpeta, string $slug, string $nombre): string
    {
        return $this->escribirFoto(public_path('images/alojamientos/' . $carpeta . '/' . $slug), $nombre, true);
    }

    private function escribirFoto(string $dir, string $nombre, bool $subcarpeta): string
    {
        File::ensureDirectoryExists($dir);

        if ($subcarpeta) {
            $this->subcarpetas[] = $dir;
        }

        $ruta = $dir . '/' . $nombre;
        File::put($ruta, 'no-es-una-imagen-real');
        $this->archivos[] = $ruta;

        return $ruta;
    }

    /** Vuelca las carpetas y las columnas JSON a la tabla `imagenes`. */
    private function sincronizar(): void
    {
        Artisan::call('imagenes:sincronizar');
    }

    /**
     * Apunta la subcarpeta propia de un alojamiento para borrarla al terminar el
     * test. La usa el comando al descargar, así que el test no sabe de antemano
     * que existe.
     */
    private function recordarSubcarpetaDe(Accommodation $alojamiento): void
    {
        $carpeta = $alojamiento->carpetaFotos();

        if ($carpeta) {
            $this->subcarpetas[] = public_path($carpeta);
        }
    }

    public function test_la_subcarpeta_del_alojamiento_manda_sobre_la_del_tipo(): void
    {
        $alojamiento = $this->crearAlojamiento('glamping');

        // Foto compartida en la carpeta del tipo y fotos propias del alojamiento.
        $this->fotoEnCarpetaTipo('glamping', '00-tipo.jpg');
        $this->fotoEnSubcarpeta('glamping', $alojamiento->slug, '02-salon.jpg');
        $this->fotoEnSubcarpeta('glamping', $alojamiento->slug, '01-portada.jpg');

        $this->sincronizar();

        $imagenes = $this->getJson('/api/accommodations/slug/' . $alojamiento->slug)->json('data.images');

        $base = self::HOST . '/images/alojamientos/glamping/' . $alojamiento->slug . '/';

        // Las propias van primero, ordenadas, y la del tipo no se cuela.
        $this->assertSame([$base . '01-portada.jpg', $base . '02-salon.jpg'], array_slice($imagenes, 0, 2));
        $this->assertNotContains(self::HOST . '/images/alojamientos/glamping/00-tipo.jpg', $imagenes);
    }

    public function test_sin_subcarpeta_se_usan_las_fotos_del_tipo(): void
    {
        $alojamiento = $this->crearAlojamiento('hostal');

        $this->fotoEnCarpetaTipo('hostales', '01-portada.jpg');

        $this->sincronizar();

        $imagenes = $this->getJson('/api/accommodations/slug/' . $alojamiento->slug)->json('data.images');

        // La foto es del tipo, así que la ruta es la de la carpeta compartida,
        // no la subcarpeta del alojamiento.
        $this->assertSame(self::HOST . '/images/alojamientos/hostales/01-portada.jpg', $imagenes[0]);
        $this->assertStringNotContainsString($alojamiento->slug, $imagenes[0]);
    }

    public function test_una_subcarpeta_vacia_cae_a_las_fotos_del_tipo(): void
    {
        $alojamiento = $this->crearAlojamiento('hostal');

        $this->fotoEnCarpetaTipo('hostales', '01-portada.jpg');
        // La subcarpeta existe pero sin fotos: no debe dejar la galería vacía.
        $this->fotoEnSubcarpeta('hostales', $alojamiento->slug, 'notas.txt');

        $this->sincronizar();

        $imagenes = $this->getJson('/api/accommodations/slug/' . $alojamiento->slug)->json('data.images');

        $this->assertSame(self::HOST . '/images/alojamientos/hostales/01-portada.jpg', $imagenes[0]);
    }

    public function test_se_ignoran_los_archivos_que_no_son_imagen(): void
    {
        $alojamiento = $this->crearAlojamiento('hostal');

        $this->fotoEnCarpetaTipo('hostales', 'foto.webp');
        $this->fotoEnCarpetaTipo('hostales', 'notas.txt');
        $this->fotoEnCarpetaTipo('hostales', 'LEEME.md');

        $this->sincronizar();

        $imagenes = $this->getJson('/api/accommodations/slug/' . $alojamiento->slug)->json('data.images');

        $this->assertSame([self::HOST . '/images/alojamientos/hostales/foto.webp'], $imagenes);
    }

    public function test_sin_fotos_en_carpetas_se_descarga_la_columna_json(): void
    {
        // Las URLs de la columna JSON ya no se sirven como texto: el comando las
        // baja a la carpeta del alojamiento y la galería sale de ahí.
        Http::fake([
            'cdn.example.test/*' => Http::response('contenido-jpeg', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $db = ['https://cdn.example.test/uno.jpg', 'https://cdn.example.test/dos.jpg'];
        $alojamiento = $this->crearAlojamiento('cabana', ['images' => $db]);

        $this->recordarSubcarpetaDe($alojamiento);

        $this->sincronizar();

        $imagenes = $this->getJson('/api/accommodations/slug/' . $alojamiento->slug)->json('data.images');

        $base = self::HOST . '/images/alojamientos/cabanas/' . $alojamiento->slug . '/';

        $this->assertCount(2, $imagenes);
        $this->assertStringStartsWith($base . 'd-', $imagenes[0]);
        $this->assertStringEndsWith('.jpg', $imagenes[0]);
    }

    public function test_re_sincronizar_no_vuelve_a_descargar_lo_que_ya_esta_en_disco(): void
    {
        Http::fake([
            'cdn.example.test/*' => Http::response('contenido-jpeg', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $alojamiento = $this->crearAlojamiento('hostal', ['images' => ['https://cdn.example.test/uno.jpg']]);

        $this->recordarSubcarpetaDe($alojamiento);

        $this->sincronizar();
        $this->sincronizar();

        // El archivo se llama igual las dos veces, así que solo se baja una vez.
        Http::assertSentCount(1);
    }

    public function test_una_url_que_no_se_puede_descargar_no_rompe_la_galeria(): void
    {
        Http::fake([
            'cdn.example.test/*' => Http::response('no existe', 404),
        ]);

        $db = ['https://cdn.example.test/rota.jpg'];
        $alojamiento = $this->crearAlojamiento('cabana', ['images' => $db]);

        $this->recordarSubcarpetaDe($alojamiento);

        $this->sincronizar();

        $imagenes = $this->getJson('/api/accommodations/slug/' . $alojamiento->slug)->json('data.images');

        // No hay archivo, así que la galería cae a la columna JSON y la foto se
        // sigue viendo: una descarga fallida no puede dejar el alojamiento sin
        // imágenes.
        $this->assertSame($db, $imagenes);
    }

    public function test_quitar_un_archivo_de_la_carpeta_lo_quita_de_la_galeria(): void
    {
        $alojamiento = $this->crearAlojamiento('glamping');

        $this->fotoEnSubcarpeta('glamping', $alojamiento->slug, '01-portada.jpg');
        $borrada = $this->fotoEnSubcarpeta('glamping', $alojamiento->slug, '02-salon.jpg');

        $this->sincronizar();

        // El administrador borra una foto de la carpeta y vuelve a sincronizar:
        // la tabla refleja el disco, así que la galería no conserva nada viejo.
        File::delete($borrada);
        $this->sincronizar();

        $base = self::HOST . '/images/alojamientos/glamping/' . $alojamiento->slug . '/';

        $this->assertSame(
            [$base . '01-portada.jpg'],
            $this->getJson('/api/accommodations/slug/' . $alojamiento->slug)->json('data.images')
        );
    }

    public function test_las_reservas_tambien_traen_las_fotos_del_alojamiento(): void
    {
        // "Mis Reservas" lee reservation.room_unit.accommodation.images, así que
        // la reserva debe llevar la misma galería que el buscador.
        $alojamiento = $this->crearAlojamiento('hotel');
        $unidad = $this->crearUnidad($alojamiento);

        $this->fotoEnSubcarpeta('hoteles', $alojamiento->slug, '01-portada.jpg');
        $this->sincronizar();
        $portada = self::HOST . '/images/alojamientos/hoteles/' . $alojamiento->slug . '/01-portada.jpg';

        $create = $this->postJson('/api/reservations', [
            'room_unit_id' => $unidad->id,
            'guest_name' => 'Huésped Fotos',
            'guest_email' => 'fotos.reserva@example.com',
            'guest_phone' => '+34 600 123 456',
            'guest_country_code' => 'ES',
            'check_in_date' => now()->addDays(70)->format('Y-m-d'),
            'check_out_date' => now()->addDays(72)->format('Y-m-d'),
            'adults_count' => 1,
            'children_count' => 0,
        ])->assertStatus(201);

        $this->assertSame($portada, $create->json('data.room_unit.accommodation.images.0'));

        $lookup = $this->postJson('/api/reservations/lookup', [
            'code' => $create->json('data.reservation_code'),
            'email' => 'fotos.reserva@example.com',
        ])->assertStatus(200);

        $this->assertSame($portada, $lookup->json('data.room_unit.accommodation.images.0'));

        $cancel = $this->putJson('/api/reservations/' . $create->json('data.reservation_code') . '/cancel', [
            'email' => 'fotos.reserva@example.com',
            'reason' => 'Prueba',
        ])->assertStatus(200);

        $this->assertSame($portada, $cancel->json('data.room_unit.accommodation.images.0'));
    }
}
