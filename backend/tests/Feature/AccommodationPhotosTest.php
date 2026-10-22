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
 * El alojamiento no tiene columna de fotos: la carpeta es la única verdad. Los
 * tests se crean sus propios alojamientos con un slug aleatorio para no depender
 * de las fotos reales que pueda haber en las carpetas del proyecto.
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
            'status' => true,
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

    /** Vuelca las carpetas a la tabla `imagenes`. */
    private function sincronizar(): void
    {
        Artisan::call('imagenes:sincronizar');
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

        // El comando renumera la carpeta: las propias van primero, consecutivas,
        // y la del tipo no se cuela.
        $this->assertSame([$base . '01.jpg', $base . '02.jpg'], array_slice($imagenes, 0, 2));
        $this->assertNotContains(self::HOST . '/images/alojamientos/glamping/00-tipo.jpg', $imagenes);
    }

    public function test_sin_subcarpeta_se_usan_las_fotos_del_tipo(): void
    {
        $alojamiento = $this->crearAlojamiento('hostal');

        $this->fotoEnCarpetaTipo('hostales', '01-portada.jpg');

        $this->sincronizar();

        $imagenes = $this->getJson('/api/accommodations/slug/' . $alojamiento->slug)->json('data.images');

        // La foto es del tipo, así que la ruta es la de la carpeta compartida,
        // no la subcarpeta del alojamiento. La carpeta del tipo no se renumera:
        // se sirve tal cual está en disco.
        $this->assertSame(self::HOST . '/images/alojamientos/hostales/01-portada.jpg', $imagenes[0]);
        $this->assertStringNotContainsString($alojamiento->slug, $imagenes[0]);
    }

    public function test_una_subcarpeta_vacia_cae_a_las_fotos_del_tipo(): void
    {
        $alojamiento = $this->crearAlojamiento('hostal');

        $this->fotoEnCarpetaTipo('hostales', '01-portada.jpg');
        // La subcarpeta existe pero sin fotos: no debe dejar la galería vacía.
        // La carpeta del tipo no se renumera, así que la ruta conserva su nombre.
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

    public function test_re_sincronizar_no_toca_la_red_ni_deshace_lo_que_hay_en_disco(): void
    {
        Http::fake();

        $alojamiento = $this->crearAlojamiento('hostal');

        $this->fotoEnSubcarpeta('hostales', $alojamiento->slug, '01-portada.jpg');

        $this->sincronizar();
        $this->sincronizar();

        // La carpeta manda: el comando nunca golpea la red para los
        // alojamientos, ni la primera vez, porque su columna `imagenes` ya no
        // existe. Aquí se comprueba que dos pasadas no generan peticiones.
        $this->assertCount(0, Http::recorded());

        $base = self::HOST . '/images/alojamientos/hostales/' . $alojamiento->slug . '/';

        $this->assertSame(
            [$base . '01.jpg'],
            $this->getJson('/api/accommodations/slug/' . $alojamiento->slug)->json('data.images')
        );
    }

    public function test_quitar_un_archivo_de_la_carpeta_lo_quita_de_la_galeria(): void
    {
        $alojamiento = $this->crearAlojamiento('glamping');

        $this->fotoEnSubcarpeta('glamping', $alojamiento->slug, '01-portada.jpg');
        $this->fotoEnSubcarpeta('glamping', $alojamiento->slug, '02-salon.jpg');

        $this->sincronizar();

        // El administrador borra una foto de la carpeta y vuelve a sincronizar:
        // la tabla refleja el disco, así que la galería no conserva nada viejo.
        // Tras la primera sincronización el comando renombró la segunda foto a
        // 02.jpg, así que es ese archivo el que se borra.
        File::delete(public_path('images/alojamientos/glamping/' . $alojamiento->slug . '/02.jpg'));
        $this->sincronizar();

        $base = self::HOST . '/images/alojamientos/glamping/' . $alojamiento->slug . '/';

        // Al borrar la segunda, el comando renumera la que queda a 01.jpg.
        $this->assertSame(
            [$base . '01.jpg'],
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
        $portada = self::HOST . '/images/alojamientos/hoteles/' . $alojamiento->slug . '/01.jpg';

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
