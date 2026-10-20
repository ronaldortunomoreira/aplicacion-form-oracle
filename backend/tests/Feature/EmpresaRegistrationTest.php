<?php

namespace Tests\Feature;

use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Datos mínimos válidos de una solicitud, con el país y número por defecto
     * (España) para que cada test solo declare lo que le importa.
     *
     * `country_code` es el ISO2 y no el prefijo: con todos los países en el
     * selector el prefijo ya no identifica a uno solo (+1 lo comparten 24).
     */
    private function solicitud(array $extra = []): array
    {
        return array_merge([
            'name' => 'Hotel Sierra Nevada',
            'email' => 'reservas@sierranevada.example',
            'country_code' => 'ES',
            'whatsapp' => '600123456',
        ], $extra);
    }

    public function test_a_visitor_can_list_requests(): void
    {
        Empresa::create([
            'name' => 'Hotel Sierra Nevada',
            'email' => 'reservas@sierranevada.example',
            'country_code' => 'ES',
            'whatsapp' => '600123456',
            'detail' => 'Tenemos tres habitaciones junto a la parada del bus.',
        ]);

        $response = $this->getJson('/api/companies');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        $this->assertSame(
            'reservas@sierranevada.example',
            $response->json('data.0.email'),
            'La API debe exponer las columnas en inglés aunque la tabla esté en español.'
        );
    }

    public function test_a_visitor_can_submit_a_request(): void
    {
        $response = $this->postJson('/api/companies', $this->solicitud([
            'detail' => 'Cinco cabañas con cocina propia.',
        ]));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Hotel Sierra Nevada')
            ->assertJsonPath('data.country_code', 'ES')
            ->assertJsonPath('data.whatsapp', '600123456');

        $this->assertDatabaseHas('empresas', [
            'nombre' => 'Hotel Sierra Nevada',
            'correo' => 'reservas@sierranevada.example',
            'codigo_pais' => 'ES',
            'whatsapp' => '600123456',
        ]);
    }

    public function test_a_submitted_request_shows_up_in_the_list(): void
    {
        $this->postJson('/api/companies', $this->solicitud([
            'email' => 'hola@mar.example',
        ]))->assertCreated();

        $this->getJson('/api/companies')
            ->assertOk()
            ->assertJsonPath('data.0.email', 'hola@mar.example');
    }

    public function test_the_detail_is_optional(): void
    {
        $this->postJson('/api/companies', $this->solicitud([
            'email' => 'hola@estrella.example',
        ]))->assertCreated();

        $this->assertDatabaseHas('empresas', [
            'nombre' => 'Hotel Sierra Nevada',
            'correo' => 'hola@estrella.example',
            'detalle' => null,
        ]);
    }

    public function test_name_email_country_and_whatsapp_are_required(): void
    {
        $this->postJson('/api/companies', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'country_code', 'whatsapp']);

        $this->assertDatabaseCount('empresas', 0);
    }

    public function test_the_email_must_be_valid_and_unique(): void
    {
        $this->postJson('/api/companies', $this->solicitud(['email' => 'no-es-un-correo']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->postJson('/api/companies', $this->solicitud())
            ->assertCreated();

        $this->postJson('/api/companies', $this->solicitud([
            'name' => 'Hotel Sierra Nevada otra vez',
        ]))->assertStatus(422)->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('empresas', 1);
    }

    /**
     * El país se identifica con el ISO2. Los que no existen en los metadatos de
     * la UIT se rechazan, y también los prefijos sueltos: son el formato
     * anterior y aceptarlos volvería a guardar datos que no significan un país.
     */
    public function test_the_country_code_must_be_a_supported_country(): void
    {
        foreach (['XX', '+34', 'espana', '', null, '999'] as $pais) {
            $this->postJson('/api/companies', $this->solicitud(['country_code' => $pais]))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['country_code']);
        }

        $this->assertDatabaseCount('empresas', 0);
    }

    /**
     * El número se guarda normalizado: sin prefijo internacional ni prefijos
     * troncales. El visitante puede escribirlo como le parezca porque es quien
     * lo pega de otra web.
     */
    public function test_the_whatsapp_number_is_stored_normalized(): void
    {
        $formatos = [
            '600 123 456',
            '600123456',
            '+34 600 123 456',
            '+34 (600) 12-34-56',
            '0034 600123456',
        ];

        foreach ($formatos as $indice => $whatsapp) {
            $this->postJson('/api/companies', $this->solicitud([
                'name' => "Hotel Formato {$indice}",
                'email' => "formato{$indice}@ejemplo.example",
                'whatsapp' => $whatsapp,
            ]))->assertCreated()->assertJsonPath('data.whatsapp', '600123456');
        }

        $this->assertDatabaseMissing('empresas', ['whatsapp' => '+34600123456']);
    }

    /**
     * Cada país tiene su propia longitud y sus propios prefijos de móvil, así
     * que un número válido de un país no debe servir en el siguiente. La lista
     * va más allá de los cinco países del catálogo porque el selector ofrece
     * todos.
     */
    public function test_the_whatsapp_number_must_match_the_selected_country(): void
    {
        $casos = [
            // [ISO2, número válido, número inválido para ese país]
            ['ES', '600123456', '123456789'],      // España: 9 dígitos, móvil 6/7.
            ['MX', '5512345678', '1512345678'],    // México: 10 dígitos, no empieza por 1.
            ['AR', '1123456789', '12345'],         // Argentina: 10 dígitos.
            ['CO', '3001234567', '2001234567'],    // Colombia: 10 dígitos, móvil empieza por 3.
            ['BO', '71234567', '51234567'],        // Bolivia: 8 dígitos, móvil 6/7.
            ['US', '2015550123', '0123'],          // Estados Unidos: 10 dígitos.
            ['DE', '15123456789', '123'],          // Alemania: móvil 15x/16x/17x.
            ['GB', '7400123456', '12345'],         // Reino Unido: móvil empieza por 7.
            ['IT', '3123456789', '0123'],          // Italia: móvil 3xx.
            ['BR', '11961234567', '0123456789'],   // Brasil: móvil empieza por 11.
            ['JP', '9012345678', '12345'],         // Japón: 10 dígitos, móvil 70/80/90.
            ['IN', '8123456789', '12345'],         // India: móvil empieza por 6-9.
        ];

        foreach ($casos as $indice => [$pais, $valido, $invalido]) {
            $this->postJson('/api/companies', $this->solicitud([
                'email' => "valido{$indice}@ejemplo.example",
                'country_code' => $pais,
                'whatsapp' => $valido,
            ]))->assertCreated();

            $this->postJson('/api/companies', $this->solicitud([
                'email' => "invalido{$indice}@ejemplo.example",
                'country_code' => $pais,
                'whatsapp' => $invalido,
            ]))->assertStatus(422)->assertJsonValidationErrors(['whatsapp']);
        }
    }

    /**
     * Prefijo internacional + número guardado debe dar siempre el número en
     * formato E.164, que es lo que se necesita para escribir el enlace de
     * WhatsApp (https://wa.me/<número>). Esta es la propiedad que importa del
     * prefijo troncal: el 0 de "030 1234567" en Alemania o el "0" de
     * "07400 123456" en el Reino Unido no forman parte del número internacional,
     * así que el mismo número escrito en las dos formas tiene que acabar
     * guardado igual.
     */
    public function test_the_stored_number_rebuilds_the_international_one(): void
    {
        $diales = [
            'ES' => '+34',
            'DE' => '+49',
            'MX' => '+52',
            'GB' => '+44',
            'BR' => '+55',
            'IT' => '+39',
            'AR' => '+54',
            'CO' => '+57',
        ];

        // El mismo número escrito en formato nacional y en internacional. Las dos
        // entradas tienen que producir exactamente el mismo valor guardado.
        $pares = [
            ['DE', '030 1234567', '+49 30 1234567', '301234567'],
            ['ES', '600 123 456', '+34 600 123 456', '600123456'],
            ['MX', '55 1234 5678', '+52 55 1234 5678', '5512345678'],
            ['GB', '07400 123456', '+44 7400 123456', '7400123456'],
            ['IT', '312 345 6789', '+39 312 345 6789', '3123456789'],
            ['AR', '011 15 2345 6789', '+54 9 11 2345 6789', '91123456789'],
            ['CO', '300 123 4567', '+57 300 123 4567', '3001234567'],
            ['BR', '11 96123 4567', '+55 11 96123 4567', '11961234567'],
        ];

        foreach ($pares as $indice => [$pais, $nacional, $internacional, $guardado]) {
            $this->postJson('/api/companies', $this->solicitud([
                'email' => "nacional{$indice}@ejemplo.example",
                'country_code' => $pais,
                'whatsapp' => $nacional,
            ]))->assertCreated()->assertJsonPath('data.whatsapp', $guardado);

            $this->postJson('/api/companies', $this->solicitud([
                'email' => "internacional{$indice}@ejemplo.example",
                'country_code' => $pais,
                'whatsapp' => $internacional,
            ]))->assertCreated()->assertJsonPath('data.whatsapp', $guardado);

            // Y el enlace de WhatsApp que se construirá a partir de la fila sale
            // bien: el prefijo del país más el número guardado.
            $this->assertSame(
                '+' . preg_replace('/\D/', '', $internacional),
                $diales[$pais] . $guardado,
                "El número de {$pais} debería reconstruirse como internacional."
            );
        }
    }

    /**
     * Un número internacional de otro país no puede guardarse bajo el país
     * elegido: si no, el enlace de WhatsApp apuntaría al número equivocado.
     */
    public function test_a_number_from_another_country_is_rejected(): void
    {
        $this->postJson('/api/companies', $this->solicitud([
            'email' => 'cruzado@ejemplo.example',
            'country_code' => 'ES',
            'whatsapp' => '+49 151 23456789',
        ]))->assertStatus(422)->assertJsonValidationErrors(['whatsapp']);

        $this->assertDatabaseCount('empresas', 0);
    }

    /**
     * El catálogo del selector ofrece todos los países con los que se puede
     * validar un número, no solo los cinco del catálogo de alojamientos.
     */
    public function test_the_company_countries_endpoint_lists_every_supported_country(): void
    {
        $response = $this->getJson('/api/company-countries');

        $response->assertOk()->assertJsonPath('success', true);

        $paises = $response->json('data');

        $this->assertGreaterThan(200, count($paises), 'Debería listar todas las regiones con numeración.');

        $porIso = collect($paises)->keyBy('iso2');

        $this->assertSame('España', $porIso['ES']['name']);
        $this->assertSame('+34', $porIso['ES']['dial_code']);
        $this->assertNotEmpty($porIso['ES']['phone_example']);

        // Alejados del catálogo de alojamientos: el selector los ofrece igual.
        $this->assertTrue($porIso->has('DE'));
        $this->assertTrue($porIso->has('JP'));

        foreach ($paises as $pais) {
            $this->assertMatchesRegularExpression('/^\+[1-9]\d{0,3}$/', $pais['dial_code']);
            $this->assertNotEmpty($pais['phone_example']);
        }
    }

    /**
     * El selector se pinta en el idioma del visitante.
     */
    public function test_the_company_countries_endpoint_is_localized(): void
    {
        $espanol = collect($this->getJson('/api/company-countries?lang=es')->json('data'));
        $ingles = collect($this->getJson('/api/company-countries?lang=en')->json('data'));

        $this->assertSame('España', $espanol->firstWhere('iso2', 'ES')['name']);
        $this->assertSame('Spain', $ingles->firstWhere('iso2', 'ES')['name']);
        $this->assertSame('Germany', $ingles->firstWhere('iso2', 'DE')['name']);
    }

    /**
     * El catálogo de países con alojamientos no debe incluirse reglas de
     * teléfono: si las incluyera, el buscador y las páginas /paises/<slug>
     * empezarían a ofrecer países sin ningún alojamiento publicado.
     */
    public function test_the_accommodation_countries_endpoint_has_no_phone_rules(): void
    {
        $response = $this->getJson('/api/countries');

        $response->assertOk();

        $espana = collect($response->json('data'))->firstWhere('iso2', 'ES');

        $this->assertArrayNotHasKey('dial_code', $espana);
        $this->assertArrayNotHasKey('phone_pattern', $espana);
        $this->assertArrayNotHasKey('phone_example', $espana);
    }
}