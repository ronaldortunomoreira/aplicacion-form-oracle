<?php

namespace Tests\Feature;

use App\Mail\ReservationConfirmation;
use App\Models\Accommodation;
use App\Models\Empresa;
use App\Models\Pais;
use App\Models\RoomUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccommodationReservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * phpunit.xml apunta a la misma base MySQL que el desarrollo, así que sin
     * RefreshDatabase los tests escriben sobre los datos reales. Con el trait,
     * cada test corre dentro de una transacción y los seeders se reejecutan para
     * partir siempre del mismo estado.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_can_list_accommodation_types(): void
    {
        $response = $this->getJson('/api/accommodation-types');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    public function test_can_list_accommodations_with_filters(): void
    {
        $response = $this->getJson('/api/accommodations?type_slug=hotel');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    public function test_can_get_an_accommodation_by_slug(): void
    {
        $accommodation = Accommodation::whereNotNull('slug')->first();
        $this->assertNotNull($accommodation, 'An accommodation with a slug must exist from seeders');

        $response = $this->getJson("/api/accommodations/slug/{$accommodation->slug}");

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.slug', $accommodation->slug)
                 ->assertJsonPath('data.id', $accommodation->id);

        // La respuesta por slug debe traer las unidades igual que la respuesta por id,
        // porque el frontend usa la misma vista para ambas.
        $byId = $this->getJson("/api/accommodations/{$accommodation->id}");

        $this->assertSame(
            count($byId->json('data.room_units')),
            count($response->json('data.room_units'))
        );
    }

    public function test_unknown_slug_returns_404_and_numeric_route_still_works(): void
    {
        $this->getJson('/api/accommodations/slug/slug-que-no-existe')
             ->assertStatus(404);

        // La ruta por id solo acepta números, para que un slug no se resuelva por error.
        $accommodation = Accommodation::first();
        $this->assertNotNull($accommodation, 'An accommodation must exist from seeders');

        $this->getJson("/api/accommodations/{$accommodation->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $accommodation->id);
    }

    public function test_accommodation_exposes_country_as_name_plus_slug_iso_and_locale(): void
    {
        $accommodation = Accommodation::with('pais')->whereNotNull('pais_id')->first();
        $this->assertNotNull($accommodation, 'An accommodation with a country must exist from seeders');

        $response = $this->getJson("/api/accommodations/slug/{$accommodation->slug}");

        $response->assertStatus(200);

        // 'country' sigue siendo la cadena con el nombre, para no romper el
        // contrato JSON que ya consume el frontend.
        $response->assertJsonPath('data.country', $accommodation->pais->nombre);
        $response->assertJsonPath('data.country_slug', $accommodation->pais->slug);
        $response->assertJsonPath('data.country_iso2', $accommodation->pais->iso2);
        $response->assertJsonPath('data.country_locale', $accommodation->pais->locale());
    }

    public function test_can_filter_accommodations_by_country_slug_or_iso_code(): void
    {
        $porSlug = $this->getJson('/api/accommodations?country=espana')->assertStatus(200);
        $porIso = $this->getJson('/api/accommodations?country=ES')->assertStatus(200);

        $this->assertGreaterThan(0, count($porSlug->json('data')), 'Espana must have seeded accommodations');
        $this->assertSame(count($porSlug->json('data')), count($porIso->json('data')));

        foreach ($porSlug->json('data') as $item) {
            $this->assertSame('espana', $item['country_slug'], 'Filter must not leak other countries');
        }

        // Colombia solo tiene un alojamiento en el seed, así que el filtro por
        // país debe reducir el resultado frente a la lista completa.
        $todos = $this->getJson('/api/accommodations')->assertStatus(200);
        $colombia = $this->getJson('/api/accommodations?country=colombia')->assertStatus(200);

        $this->assertLessThan(count($todos->json('data')), count($colombia->json('data')));
        $this->assertSame('Colombia', $colombia->json('data.0.country'));
    }

    public function test_unknown_country_filter_returns_empty_list_instead_of_failing(): void
    {
        $this->getJson('/api/accommodations?country=atlantida')
             ->assertStatus(200)
             ->assertJsonPath('count', 0)
             ->assertJsonPath('data', []);
    }

    public function test_search_matches_the_country_name_through_the_relation(): void
    {
        $response = $this->getJson('/api/accommodations?search=colombia')->assertStatus(200);

        $this->assertGreaterThan(0, count($response->json('data')));
        foreach ($response->json('data') as $item) {
            $this->assertSame('CO', $item['country_iso2']);
        }
    }

    public function test_can_list_countries_with_counts_and_presentation_text(): void
    {
        $response = $this->getJson('/api/countries');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $slugs = array_column($response->json('data'), 'slug');
        $this->assertContains('espana', $slugs);

        $espana = collect($response->json('data'))->firstWhere('slug', 'espana');

        $this->assertSame('ES', $espana['iso2']);
        $this->assertSame('es', $espana['primary_language']);
        $this->assertSame('es_ES', $espana['locale']);
        $this->assertGreaterThan(0, $espana['accommodations_count']);
        // Hoy solo hay texto en español: debe caer al español en vez de null.
        $this->assertNotEmpty($espana['presentation_text']);
    }

    public function test_countries_ignores_inactive_rows(): void
    {
        Pais::where('slug', 'espana')->update(['activo' => false]);

        $slugs = array_column($this->getJson('/api/countries')->assertStatus(200)->json('data'), 'slug');

        $this->assertNotContains('espana', $slugs);
    }

    public function test_cities_still_returns_city_and_country(): void
    {
        $response = $this->getJson('/api/cities')->assertStatus(200);

        $this->assertGreaterThan(0, count($response->json('data')));
        $this->assertNotNull($response->json('data.0.city'));
        $this->assertNotNull($response->json('data.0.country'));
        $this->assertNotNull($response->json('data.0.count'));
    }

    public function test_can_get_a_country_with_its_accommodations(): void
    {
        $response = $this->getJson('/api/countries/espana');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.country.slug', 'espana')
                 ->assertJsonPath('data.country.iso2', 'ES')
                 ->assertJsonPath('data.country.locale', 'es_ES');

        $this->assertGreaterThan(0, $response->json('data.accommodations_count'));
        $this->assertNotEmpty($response->json('data.country.presentation_text'));

        foreach ($response->json('data.accommodations') as $item) {
            $this->assertSame('espana', $item['country_slug']);
            $this->assertNotNull($item['starting_price']);
        }
    }

    public function test_unknown_or_inactive_country_returns_404(): void
    {
        $this->getJson('/api/countries/pais-que-no-existe')->assertStatus(404);

        Pais::where('slug', 'colombia')->update(['activo' => false]);

        // Un país desactivado deja de publicarse, también en su página propia.
        $this->getJson('/api/countries/colombia')->assertStatus(404);
    }

    public function test_country_text_falls_back_to_spanish_when_language_is_missing(): void
    {
        $pais = Pais::where('slug', 'espana')->first();
        $pais->texto_presentacion = [
            'es' => 'Texto en español.',
            'en' => 'Text in English.',
        ];
        $pais->save();

        // Con texto en el idioma pedido se usa ese.
        $this->getJson('/api/countries/espana?lang=en')
             ->assertJsonPath('data.country.presentation_text', 'Text in English.');

        // Un idioma sin texto propio cae al español en vez de dejar la página vacía.
        $this->getJson('/api/countries/espana?lang=pt')
             ->assertJsonPath('data.country.presentation_text', 'Texto en español.');

        // Y el locale se construye con el idioma pedido, no con el del país.
        $this->getJson('/api/countries/espana?lang=en')
             ->assertJsonPath('data.country.locale', 'en_ES');
    }

    public function test_country_text_is_null_when_no_language_has_content(): void
    {
        $pais = Pais::where('slug', 'espana')->first();
        $pais->texto_presentacion = [];
        $pais->save();

        $this->getJson('/api/countries/espana?lang=pt')
             ->assertJsonPath('data.country.presentation_text', null);
    }

    /**
     * Los cuatro países publicados deben responder en ambos idiomas con textos
     * distintos, no solo con el español copiado.
     *
     * Sin esta comprobación, un `?lang=en` que devolviera el texto español
     * publicaría `/en/` como un duplicado de `/es/`: el fallo no se vería en la
     * API (responde 200 con contenido), sino en el SEO.
     */
    public function test_every_published_country_has_distinct_spanish_and_english_text(): void
    {
        $paises = Pais::where('activo', true)->get();

        $this->assertNotEmpty($paises, 'Must be at least one active country');

        foreach ($paises as $pais) {
            $presentacion = $pais->texto_presentacion ?? [];

            $this->assertArrayHasKey(
                'es',
                $presentacion,
                "{$pais->slug} has no Spanish presentation text"
            );
            $this->assertArrayHasKey(
                'en',
                $presentacion,
                "{$pais->slug} has no English presentation text"
            );
            $this->assertNotSame(
                $presentacion['es'],
                $presentacion['en'],
                "{$pais->slug} returns the same text for es and en"
            );
        }
    }

    /** El listado de países debe respetar el idioma pedido en cada fila. */
    public function test_country_list_translates_presentation_text_per_language(): void
    {
        $this->getJson('/api/countries?lang=en')
             ->assertOk()
             ->assertJsonStructure([
                 'data' => [
                     ['slug', 'name', 'presentation_text', 'accommodations_count'],
                 ],
             ]);

        $this->getJson('/api/countries?lang=en')->assertOk();

        foreach ($this->getJson('/api/countries?lang=en')->json('data') as $pais) {
            $this->assertSame(
                $pais['presentation_text'],
                Pais::where('slug', $pais['slug'])->first()->texto_presentacion['en'] ?? null,
                "{$pais['slug']} did not return its English text in the list"
            );
        }
    }

    public function test_can_preview_and_create_reservation_and_cancel_it(): void
    {
        $roomUnit = RoomUnit::first();
        $this->assertNotNull($roomUnit, 'A room unit must exist from seeders');

        $checkIn = now()->addDays(5)->format('Y-m-d');
        $checkOut = now()->addDays(8)->format('Y-m-d');

        // 1. Preview
        $previewResponse = $this->postJson('/api/reservations/preview', [
            'room_unit_id' => $roomUnit->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'adults_count' => 1,
            'children_count' => 0,
        ]);

        $previewResponse->assertStatus(200)
                        ->assertJsonPath('success', true)
                        ->assertJsonPath('data.nights', 3);

        // 2. Store Reservation
        $createResponse = $this->postJson('/api/reservations', [
            'room_unit_id' => $roomUnit->id,
            'guest_name' => 'Carlos Rodriguez',
            'guest_email' => 'carlos.test@example.com',
            'guest_phone' => '+34 600 123 456',
            'guest_country_code' => 'ES',
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'adults_count' => 1,
            'children_count' => 0,
            'payment_method' => 'pay_at_property',
            'special_requests' => 'Llegada tarde sobre las 20:00',
        ]);

        $createResponse->assertStatus(201)
                       ->assertJsonPath('success', true)
                       // El formulario manda el ISO2 y se guarda el prefijo.
                       ->assertJsonPath('data.guest_country_code', '+34');

        $code = $createResponse->json('data.reservation_code');
        $this->assertNotEmpty($code);

        $this->assertDatabaseHas('reservas', [
            'codigo_reserva' => $code,
            'codigo_pais' => '+34',
        ]);

        // 3. Lookup Reservation
        $lookupResponse = $this->postJson('/api/reservations/lookup', [
            'code' => $code,
            'email' => 'carlos.test@example.com',
        ]);

        $lookupResponse->assertStatus(200)
                       ->assertJsonPath('success', true)
                       ->assertJsonPath('data.reservation_code', $code)
                       ->assertJsonPath('data.reservation_status', 'confirmed');

        // 4. Cancel Reservation
        $cancelResponse = $this->putJson("/api/reservations/{$code}/cancel", [
            'email' => 'carlos.test@example.com',
            'reason' => 'Cambio de itinerario de viaje',
        ]);

        $cancelResponse->assertStatus(200)
                       ->assertJsonPath('success', true)
                       ->assertJsonPath('data.reservation_status', 'cancelled');
    }

    public function test_reservation_stores_the_dial_code_of_the_country_chosen_in_the_form(): void
    {
        $roomUnit = RoomUnit::first();
        $this->assertNotNull($roomUnit, 'A room unit must exist from seeders');

        $payload = [
            'room_unit_id' => $roomUnit->id,
            'guest_name' => 'Lucia Quispe',
            'guest_email' => 'lucia.bolivia@example.com',
            'guest_phone' => '71234567',
            'check_in_date' => now()->addDays(20)->format('Y-m-d'),
            'check_out_date' => now()->addDays(23)->format('Y-m-d'),
            'adults_count' => 1,
            'children_count' => 0,
            'payment_method' => 'pay_at_property',
        ];

        // El país viaja como ISO2 porque es lo que distingue Bolivia de las demás
        // regiones con numeración, y se guarda como el prefijo que se marca.
        $this->postJson('/api/reservations', $payload + ['guest_country_code' => 'BO'])
                 ->assertStatus(201)
                 ->assertJsonPath('data.guest_country_code', '+591');

        $this->assertDatabaseHas('reservas', [
            'correo_huesped' => 'lucia.bolivia@example.com',
            'codigo_pais' => '+591',
        ]);

        // Otro país, para que no quede como un caso único de Bolivia.
        $this->postJson('/api/reservations', $payload + [
            'guest_country_code' => 'DE',
            'guest_email' => 'hanna.alemania@example.com',
        ])
                 ->assertStatus(201)
                 ->assertJsonPath('data.guest_country_code', '+49');
    }

    public function test_reservation_requires_a_valid_country_code(): void
    {
        $roomUnit = RoomUnit::first();
        $this->assertNotNull($roomUnit, 'A room unit must exist from seeders');

        $payload = [
            'room_unit_id' => $roomUnit->id,
            'guest_name' => 'Visitante Sin Pais',
            'guest_email' => 'sin.pais@example.com',
            'guest_phone' => '600123456',
            'check_in_date' => now()->addDays(30)->format('Y-m-d'),
            'check_out_date' => now()->addDays(32)->format('Y-m-d'),
            'adults_count' => 1,
            'children_count' => 0,
        ];

        // Sin país no se puede saber cómo marcar el teléfono, así que no se acepta.
        $this->postJson('/api/reservations', $payload)
                 ->assertStatus(422)
                 ->assertJsonValidationErrors('guest_country_code');

        // El prefijo no vale como país: el selector manda ISO2.
        $this->postJson('/api/reservations', $payload + ['guest_country_code' => '+591'])
                 ->assertStatus(422)
                 ->assertJsonValidationErrors('guest_country_code');

        $this->assertDatabaseMissing('reservas', [
            'correo_huesped' => 'sin.pais@example.com',
        ]);
    }

    public function test_reservation_requires_a_phone_valid_for_the_selected_country(): void
    {
        $roomUnit = RoomUnit::first();
        $this->assertNotNull($roomUnit, 'A room unit must exist from seeders');

        $base = [
            'room_unit_id' => $roomUnit->id,
            'guest_name' => 'Ana Souza',
            'guest_email' => 'ana.telefono@example.com',
            'guest_country_code' => 'BO',
            'check_in_date' => now()->addDays(40)->format('Y-m-d'),
            'check_out_date' => now()->addDays(42)->format('Y-m-d'),
            'adults_count' => 1,
            'children_count' => 0,
        ];

        // Se acepta como lo escriba el huésped y se guarda el número nacional,
        // sin prefijo ni espacios, igual que en el alta de hotel.
        $this->postJson('/api/reservations', $base + ['guest_phone' => '+591 71234567'])
                 ->assertStatus(201)
                 ->assertJsonPath('data.guest_phone', '71234567')
                 ->assertJsonPath('data.guest_country_code', '+591');

        $this->assertDatabaseHas('reservas', [
            'correo_huesped' => 'ana.telefono@example.com',
            'telefono_huesped' => '71234567',
            'codigo_pais' => '+591',
        ]);

        // Un número de otro país no vale: guardado tal cual, llevaría un prefijo
        // que no le corresponde y el hotel no podría marcarlo.
        $this->postJson('/api/reservations', $base + ['guest_phone' => '+49 151 12345678'])
                 ->assertStatus(422)
                 ->assertJsonValidationErrors('guest_phone');

        // Ni uno más corto de lo que permite el país.
        $this->postJson('/api/reservations', $base + ['guest_phone' => '7123456'])
                 ->assertStatus(422)
                 ->assertJsonValidationErrors('guest_phone');
    }

    public function test_cada_pais_publica_su_moneda_iso(): void
    {
        // ISO 4217. Sin esta columna el precio era un decimal sin unidad: 185.00
        // son euros en Madrid o bolivianos en Cochabamba y nada lo distinguía.
        $esperado = [
            'espana' => 'EUR',
            'mexico' => 'MXN',
            'argentina' => 'ARS',
            'colombia' => 'COP',
            'bolivia' => 'BOB',
        ];

        foreach ($esperado as $slug => $moneda) {
            $this->assertSame(
                $moneda,
                Pais::where('slug', $slug)->value('moneda_iso'),
                "El país {$slug} debe publicar {$moneda}"
            );
        }
    }

    public function test_los_alojamientos_exponen_la_moneda_de_su_pais(): void
    {
        $data = $this->getJson('/api/accommodations')->assertStatus(200)->json('data');

        $this->assertGreaterThan(0, count($data));

        foreach ($data as $alojamiento) {
            $this->assertNotEmpty(
                $alojamiento['currency'],
                "El alojamiento {$alojamiento['name']} debe traer currency"
            );
            $this->assertSame(3, strlen($alojamiento['currency']), 'Un código ISO 4217 tiene 3 letras');
        }

        // Los de Cochabamba tienen que salir en bolivianos, que es justo el caso
        // que antes se anunciaba como "USD".
        $cochabamba = array_values(array_filter($data, fn ($a) => $a['country'] === 'Bolivia'));
        $this->assertGreaterThan(0, count($cochabamba));

        foreach ($cochabamba as $alojamiento) {
            $this->assertSame('BOB', $alojamiento['currency']);
        }
    }

    public function test_el_filtro_de_precio_solo_aplica_con_pais_elegido(): void
    {
        $todos = $this->getJson('/api/accommodations')->assertStatus(200)->json('data');

        // Sin país el precio se ignora, y no es un detalle de la UI: comparar 185
        // bolivianos contra 185 euros no significa nada. También se ignora en el
        // servidor para que una petición a mano no devuelva resultados mentirosos.
        $ignorado = $this->getJson('/api/accommodations?max_price=1')->assertStatus(200)->json('data');
        $this->assertCount(
            count($todos),
            $ignorado,
            'max_price sin country debe ignorarse, no filtrar'
        );

        // Dentro de un país los precios sí son comparables, porque todos están
        // en la misma moneda.
        $filtrado = $this->getJson('/api/accommodations?country=espana&max_price=1')
                         ->assertStatus(200)
                         ->json('data');
        $this->assertCount(0, $filtrado, 'con country, el precio sí debe filtrar');
    }

    public function test_la_reserva_congela_la_moneda_y_el_alojamiento(): void
    {
        $unidad = $this->unidadEnBolivia();

        $response = $this->postJson('/api/reservations', [
            'room_unit_id' => $unidad->id,
            'guest_name' => 'Lucia Mendoza',
            'guest_email' => 'lucia.moneda@example.com',
            'guest_phone' => '+591 71234567',
            'guest_country_code' => 'BO',
            'check_in_date' => now()->addDays(50)->format('Y-m-d'),
            'check_out_date' => now()->addDays(52)->format('Y-m-d'),
            'adults_count' => 1,
            'children_count' => 0,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.currency', 'BOB')
                 ->assertJsonPath('data.accommodation_id', $unidad->accommodation_id);

        $this->assertDatabaseHas('reservas', [
            'correo_huesped' => 'lucia.moneda@example.com',
            'alojamiento_id' => $unidad->accommodation_id,
            'moneda_iso' => 'BOB',
        ]);
    }

    public function test_la_reserva_ignora_la_moneda_que_manda_el_cliente(): void
    {
        $unidad = $this->unidadEnBolivia();
        $otroAlojamiento = Accommodation::where('pais_id', Pais::where('slug', 'espana')->value('id'))
                                          ->where('id', '!=', $unidad->accommodation_id)
                                          ->first();

        $response = $this->postJson('/api/reservations', [
            'room_unit_id' => $unidad->id,
            // Ni la moneda ni el alojamiento se aceptan desde el cliente: el
            // importe pactado está en la moneda donde está el hotel.
            'currency' => 'USD',
            'accommodation_id' => $otroAlojamiento?->id ?? 999999,
            'guest_name' => 'Pedro Suarez',
            'guest_email' => 'pedro.moneda@example.com',
            'guest_phone' => '+591 71234567',
            'guest_country_code' => 'BO',
            'check_in_date' => now()->addDays(60)->format('Y-m-d'),
            'check_out_date' => now()->addDays(62)->format('Y-m-d'),
            'adults_count' => 1,
            'children_count' => 0,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.currency', 'BOB')
                 ->assertJsonPath('data.accommodation_id', $unidad->accommodation_id);
    }

    /** Primera unidad de habitación con alojamiento en Bolivia. */
    private function unidadEnBolivia(): RoomUnit
    {
        $boliviaId = Pais::where('slug', 'bolivia')->value('id');

        $unidad = RoomUnit::whereHas('accommodation', fn ($q) => $q->where('pais_id', $boliviaId))
                           ->first();

        $this->assertNotNull($unidad, 'Debe existir una unidad de habitación en Bolivia desde los seeders');

        return $unidad;
    }

    public function test_reservation_notice_goes_to_the_company_and_the_hospedaya_backup(): void
    {
        Mail::fake();

        $empresa = Empresa::create([
            'name' => 'Hoteles del Sur',
            'email' => 'avisos@hotelesdelsur.test',
            'country_code' => 'BO',
        ]);

        $unidad = $this->unidadEnBolivia();
        $unidad->accommodation->update(['company_id' => $empresa->id]);

        $response = $this->postJson('/api/reservations', [
            'room_unit_id' => $unidad->id,
            'guest_name' => 'Maria Fernandez',
            'guest_email' => 'maria.correo@example.com',
            'guest_phone' => '+591 71234567',
            'guest_country_code' => 'BO',
            'check_in_date' => now()->addDays(10)->format('Y-m-d'),
            'check_out_date' => now()->addDays(12)->format('Y-m-d'),
            'adults_count' => 1,
            'children_count' => 0,
        ]);

        $response->assertStatus(201);

        $code = $response->json('data.reservation_code');

        Mail::assertSent(ReservationConfirmation::class, function (ReservationConfirmation $mail) use ($empresa, $code) {
            // Con Mail::fake() el asunto aún no se resuelve (subject es null
            // hasta el render), así que se pide al envelope del mailable.
            $asunto = $mail->envelope()->subject;

            return $mail->hasTo('avisos@hotelesdelsur.test')
                && $mail->hasTo(config('mail.hospedaya'))
                && str_contains($asunto, $code);
        });
    }

    public function test_reservation_notice_still_reaches_the_hospedaya_backup_without_a_company(): void
    {
        Mail::fake();

        $unidad = $this->unidadEnBolivia();
        $this->assertNull($unidad->accommodation->company_id, 'Los seeders no vinculan empresa');

        $response = $this->postJson('/api/reservations', [
            'room_unit_id' => $unidad->id,
            'guest_name' => 'Jose Perez',
            'guest_email' => 'jose.correo@example.com',
            'guest_phone' => '+591 71234567',
            'guest_country_code' => 'BO',
            'check_in_date' => now()->addDays(15)->format('Y-m-d'),
            'check_out_date' => now()->addDays(17)->format('Y-m-d'),
            'adults_count' => 1,
            'children_count' => 0,
        ]);

        $response->assertStatus(201);

        Mail::assertSent(ReservationConfirmation::class, function (ReservationConfirmation $mail) {
            return $mail->hasTo(config('mail.hospedaya'))
                && count($mail->to) === 1;
        });
    }

    public function test_cities_can_be_scoped_to_one_country(): void
    {
        $response = $this->getJson('/api/cities?country=espana')->assertStatus(200);

        $response->assertJsonPath('success', true)
                 ->assertJsonPath('country', 'espana');

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));

        // Todas las ciudades devueltas tienen que ser del país pedido, que es
        // justo lo que hace que la cascada sea fiable.
        foreach ($data as $destino) {
            $this->assertSame('espana', $destino['country_slug']);
        }

        $this->assertContains('Madrid', array_column($data, 'city'));
    }

    public function test_cities_accept_the_iso_code_too(): void
    {
        $porSlug = $this->getJson('/api/cities?country=espana')->assertStatus(200);
        $porIso = $this->getJson('/api/cities?country=ES')->assertStatus(200);

        // El selector de países manda slugs y el resto del buscador acepta ISO2;
        // las ciudades no pueden ser la excepción.
        $this->assertSame(
            array_column($porSlug->json('data'), 'city'),
            array_column($porIso->json('data'), 'city')
        );
    }

    public function test_cities_without_country_still_list_every_city(): void
    {
        $todos = $this->getJson('/api/cities')->assertStatus(200);
        $espana = $this->getJson('/api/cities?country=espana')->assertStatus(200);

        $todos->assertJsonPath('country', null);

        $this->assertGreaterThan(
            count($espana->json('data')),
            count($todos->json('data'))
        );
    }

    public function test_each_city_carries_its_country_slug(): void
    {
        $response = $this->getJson('/api/cities')->assertStatus(200);

        foreach ($response->json('data') as $destino) {
            $this->assertNotNull($destino['country_slug'], 'El autocomplete necesita el slug para fijar la cascada');
        }
    }

    public function test_tourist_destinations_are_listed_with_country_and_presentation(): void
    {
        $response = $this->getJson('/api/destinations')->assertStatus(200);

        $response->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));

        $porSlug = collect($data)->keyBy('slug');
        $this->assertTrue($porSlug->has('salar-de-uyuni'), 'Bolivia debe aportar destinos');

        $uyuni = $porSlug->get('salar-de-uyuni');
        $this->assertSame('bolivia', $uyuni['country_slug']);
        $this->assertSame('BO', $uyuni['country_iso2']);
        $this->assertNotEmpty($uyuni['presentation_text']);
    }

    public function test_tourist_destinations_can_be_scoped_and_translated(): void
    {
        $es = $this->getJson('/api/destinations?country=bolivia&lang=es')->assertStatus(200);
        $en = $this->getJson('/api/destinations?country=bolivia&lang=en')->assertStatus(200);

        $this->assertGreaterThan(0, count($es->json('data')));

        foreach ($es->json('data') as $destino) {
            $this->assertSame('bolivia', $destino['country_slug']);
        }

        // La presentación se sirve en el idioma pedido, que es lo que permite
        // que /en/destinos no repita el texto en español.
        $uyuniEs = collect($es->json('data'))->firstWhere('slug', 'salar-de-uyuni');
        $uyuniEn = collect($en->json('data'))->firstWhere('slug', 'salar-de-uyuni');

        $this->assertNotSame($uyuniEs['presentation_text'], $uyuniEn['presentation_text']);
    }

    public function test_tourist_destination_detail_lists_accommodations_of_its_city(): void
    {
        // Cochabamba tiene hoteles en el seed y es destino turístico, así que su
        // ficha debe traerlos. La cascada es destino -> país + ciudad -> alojamiento.
        $response = $this->getJson('/api/destinations/cochabamba')->assertStatus(200);

        $response->assertJsonPath('data.destination.slug', 'cochabamba');
        $this->assertGreaterThan(0, $response->json('data.accommodations_count'));

        foreach ($response->json('data.accommodations') as $item) {
            $this->assertSame('Cochabamba', $item['city']);
            $this->assertSame('bolivia', $item['country_slug']);
        }
    }

    public function test_unknown_tourist_destination_returns_404(): void
    {
        $this->getJson('/api/destinations/destino-que-no-existe')->assertStatus(404);
    }

    public function test_the_city_filter_matches_exactly_and_not_by_substring(): void
    {
        // "Mad" y "Cocha" son prefijos de Madrid y Cochabamba. Con LIKE '%Mad%' el
        // backend devolvía la ciudad como si el usuario la hubiera elegido; con
        // coincidencia exacta un fragmento no encuentra nada, que es lo que
        // corresponde a un selector donde siempre se elige un valor de la lista.
        $this->getJson('/api/accommodations?city=Mad')
             ->assertStatus(200)
             ->assertJsonPath('count', 0)
             ->assertJsonPath('data', []);

        $this->getJson('/api/accommodations?city=Cocha')
             ->assertStatus(200)
             ->assertJsonPath('data', []);

        $este = $this->getJson('/api/accommodations?city=Cancún')->assertStatus(200);
        $este->assertJsonPath('count', 1);
        $this->assertSame('Cancún', $este->json('data.0.city'));
    }

    public function test_the_city_filter_ignores_case_and_accents(): void
    {
        // La columna es utf8mb4_unicode_ci, así que la comparación exacta ya
        // normaliza. El frontend no debería tener que hacerlo por su cuenta.
        $conAcento = $this->getJson('/api/accommodations?city=Medellín')->assertStatus(200);
        $sinAcento = $this->getJson('/api/accommodations?city=medellin')->assertStatus(200);
        $enMayus = $this->getJson('/api/accommodations?city=MEDELLIN')->assertStatus(200);

        $this->assertSame(1, $conAcento->json('count'));
        $this->assertSame($conAcento->json('data.0.id'), $sinAcento->json('data.0.id'));
        $this->assertSame($conAcento->json('data.0.id'), $enMayus->json('data.0.id'));
    }

    public function test_the_city_filter_combines_with_the_country_filter(): void
    {
        // La cascada completa: primero el país, después la ciudad. Si el país no
        // casara con la ciudad, la combinación devolvería cero y el usuario vería
        // un vacío sin explicación.
        $response = $this->getJson('/api/accommodations?country=colombia&city=Medellín')->assertStatus(200);

        $response->assertJsonPath('count', 1);
        $this->assertSame('CO', $response->json('data.0.country_iso2'));
        $this->assertSame('Medellín', $response->json('data.0.city'));
    }

    public function test_a_city_that_belongs_to_another_country_returns_nothing(): void
    {
        // Elegir Bolivia y luego Medellín no debe devolver los hoteles de
        // Medellín que hay en el catálogo: no es el destino pedido.
        $this->getJson('/api/accommodations?country=bolivia&city=Medellín')
             ->assertStatus(200)
             ->assertJsonPath('count', 0)
             ->assertJsonPath('data', []);
    }
}
