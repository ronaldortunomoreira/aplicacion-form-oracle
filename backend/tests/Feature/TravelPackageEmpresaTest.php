<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Pais;
use App\Models\PaqueteTuristico;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El paquete turístico depende de la empresa: no se publica sin ella, el país
 * lo hereda de ella, y la base de datos no deja borrar una empresa que tenga
 * paquetes publicados.
 */
class TravelPackageEmpresaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /** Crea una empresa en el país que se le pida. */
    private function crearEmpresa(string $iso2 = 'BO'): Empresa
    {
        $pais = Pais::where('iso2', $iso2)->firstOrFail();

        return Empresa::create([
            'name' => 'Operadora ' . $pais->nombre,
            'email' => 'operadora-' . strtolower($iso2) . '-' . uniqid() . '@example.com',
            'country_code' => $iso2,
            'pais_id' => $pais->id,
        ]);
    }

    private function crearPaquete(Empresa $empresa, array $extra = []): PaqueteTuristico
    {
        return PaqueteTuristico::create($extra + [
            'empresa_id' => $empresa->id,
            'nombre' => 'Tour de Prueba 1 Dia',
            'slug' => 'tour-de-prueba-' . uniqid(),
            'precio_desde' => 100,
            'moneda_iso' => 'BOB',
            'activo' => true,
        ]);
    }

    public function test_un_paquete_no_se_puede_crear_sin_empresa(): void
    {
        // La columna es NOT NULL y quien la defiende es la base de datos.
        $this->expectException(QueryException::class);

        PaqueteTuristico::create([
            'nombre' => 'Tour Sin Empresa',
            'slug' => 'tour-sin-empresa-' . uniqid(),
            'precio_desde' => 100,
            'activo' => true,
        ]);
    }

    public function test_el_pais_del_paquete_se_hereda_de_su_empresa(): void
    {
        $paquete = $this->crearPaquete($this->crearEmpresa('ES'));

        $this->assertSame('espana', $paquete->paisDeEmpresa()?->slug);

        $this->getJson('/api/travel-packages/' . $paquete->slug)
            ->assertStatus(200)
            ->assertJsonPath('data.country', 'España')
            ->assertJsonPath('data.country_slug', 'espana')
            ->assertJsonPath('data.country_iso2', 'ES');
    }

    public function test_una_empresa_sin_pais_publicable_deja_el_paquete_sin_pais(): void
    {
        // El registro de empresas acepta el ISO2 de cualquier región del mundo,
        // pero `paises` solo contiene los países publicables del proyecto.
        $empresa = Empresa::create([
            'name' => 'Operadora Andina',
            'email' => 'operadora-andina-' . uniqid() . '@example.com',
            'country_code' => 'PE',
            'pais_id' => null,
        ]);

        $this->crearPaquete($empresa);

        $this->getJson('/api/travel-packages')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.country_slug', null);
    }

    public function test_el_filtro_por_pais_se_resuelve_a_traves_de_la_empresa(): void
    {
        $bolivia = $this->crearPaquete($this->crearEmpresa('BO'));
        $espana = $this->crearPaquete($this->crearEmpresa('ES'));

        // Cada búsqueda devuelve un solo paquete, así que el índice 0 es el
        // único y no depende del orden.
        $this->getJson('/api/travel-packages?country=bolivia')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $bolivia->slug);

        $this->getJson('/api/travel-packages?country=ES')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $espana->slug);
    }

    public function test_no_se_puede_borrar_una_empresa_que_tenga_paquetes(): void
    {
        $empresa = $this->crearEmpresa();

        $this->crearPaquete($empresa);

        // RESTRICT: la base de datos defiende el trabajo publicado. Reasignar o
        // borrar antes los paquetes es decisión del panel de administración.
        $this->expectException(QueryException::class);

        $empresa->delete();
    }

    public function test_una_empresa_sin_paquetes_si_se_puede_borrar(): void
    {
        $empresa = $this->crearEmpresa('ES');

        $empresa->delete();

        $this->assertDatabaseMissing('empresas', ['id' => $empresa->id]);
    }
}
