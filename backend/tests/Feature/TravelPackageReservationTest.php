<?php

namespace Tests\Feature;

use App\Mail\ReservationConfirmation;
use App\Models\Empresa;
use App\Models\Pais;
use App\Models\Reservation;
use App\Models\PaqueteTuristico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TravelPackageReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * El seeder de paquetes no corre con DatabaseSeeder, así que se crea aquí.
     *
     * `empresa_id` es obligatorio: no existe un paquete sin empresa, así que
     * toda prueba que quiera un paquete tiene que empezar por su empresa.
     */
    private function crearPaquete(array $extra = []): PaqueteTuristico
    {
        return PaqueteTuristico::create($extra + [
            'empresa_id' => $this->crearEmpresa()->id,
            'nombre' => 'Tour Ciudad de Oro 1 Dia',
            'slug' => 'tour-ciudad-de-oro-1-dia',
            'duracion' => '1 dia',
            'precio_desde' => 500,
            'moneda_iso' => 'BOB',
            'estado' => true,
        ]);
    }

    /** Empresa de prueba, con el país del que heredará el paquete. */
    private function crearEmpresa(): Empresa
    {
        return Empresa::create([
            'name' => 'Operadora de Prueba',
            'email' => 'operadora-' . uniqid() . '@example.com',
            'country_code' => 'BO',
            'pais_id' => Pais::where('iso2', 'BO')->first()?->id,
        ]);
    }

    public function test_can_list_travel_packages(): void
    {
        $this->crearPaquete();

        $response = $this->getJson('/api/travel-packages');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'starting_price', 'currency']]]);
    }

    public function test_can_get_a_travel_package_by_slug(): void
    {
        $package = $this->crearPaquete();

        $response = $this->getJson("/api/travel-packages/{$package->slug}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.slug', $package->slug)
                 ->assertJsonPath('data.id', $package->id)
                 ->assertJsonPath('data.currency', 'BOB');
    }

    public function test_a_package_reservation_is_created_and_emails_the_guest(): void
    {
        Mail::fake();

        $package = $this->crearPaquete();

        $response = $this->postJson('/api/reservations/package', [
            'travel_package_id' => $package->id,
            'guest_name' => 'Ana Perez',
            'guest_email' => 'ana.package@example.com',
            'guest_phone' => '+591 71234567',
            'guest_country_code' => 'BO',
            'guest_document_id' => '12345678',
            'payment_method' => 'pay_at_property',
            'special_requests' => 'Sin gluten',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.guest_country_code', '+591')
                 ->assertJsonPath('data.travel_package_id', $package->id);

        $code = $response->json('data.reservation_code');
        $this->assertNotEmpty($code);

        // Sin unidad de habitación ni fechas: el paquete fija el precio entero.
        $this->assertDatabaseHas('reservas', [
            'codigo_reserva' => $code,
            'paquete_turistico_id' => $package->id,
            'unidad_habitacion_id' => null,
            'precio_total' => $package->precio_desde,
        ]);

        Mail::assertSent(ReservationConfirmation::class, function (ReservationConfirmation $mail) use ($package) {
            return $mail->hasTo('ana.package@example.com')
                && str_contains($mail->envelope()->subject, $package->nombre);
        });
    }

    /**
     * El correo de un paquete no tiene unidad ni fechas: la plantilla debe
     * renderizar sin intentar leer `roomUnit->accommodation` (que es null).
     */
    public function test_the_package_confirmation_mail_renders_without_a_room_unit(): void
    {
        $package = $this->crearPaquete();

        $reservation = new Reservation();
        $reservation->reservation_code = 'BK-TESTPKG';
        $reservation->travel_package_id = $package->id;
        $reservation->guest_name = 'Ana Perez';
        $reservation->guest_email = 'ana.package@example.com';
        $reservation->guest_phone = '71234567';
        $reservation->guest_country_code = '+591';
        $reservation->adults_count = 1;
        $reservation->children_count = 0;
        $reservation->nights = 0;
        $reservation->price_per_night = (float) $package->precio_desde;
        $reservation->cleaning_fee = 0;
        $reservation->taxes = 0;
        $reservation->total_price = (float) $package->precio_desde;
        $reservation->currency = $package->moneda_iso ?? 'USD';
        $reservation->payment_method = 'pay_at_property';
        $reservation->payment_status = 'pending';
        $reservation->estado = 1;
        $reservation->save();

        $html = (new ReservationConfirmation($reservation))->render();

        $this->assertStringContainsString($package->nombre, $html);
        $this->assertStringContainsString('BK-TESTPKG', $html);
    }

    public function test_a_package_reservation_requires_a_valid_country_and_phone(): void
    {
        $package = $this->crearPaquete();

        $base = [
            'travel_package_id' => $package->id,
            'guest_name' => 'Ana Perez',
            'guest_email' => 'ana.package@example.com',
            'guest_country_code' => 'BO',
            'guest_phone' => '+591 71234567',
        ];

        $this->postJson('/api/reservations/package', array_merge($base, ['guest_country_code' => 'XX']))
             ->assertStatus(422)
             ->assertJsonValidationErrors('guest_country_code');

        // Un número de otro país no vale para el país elegido.
        $this->postJson('/api/reservations/package', array_merge($base, ['guest_phone' => '+49 151 12345678']))
             ->assertStatus(422)
             ->assertJsonValidationErrors('guest_phone');
    }
}
