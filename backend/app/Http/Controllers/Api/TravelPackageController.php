<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelPackageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $idioma = $request->get('lang', 'es');

        $query = TravelPackage::query()
            ->with(['country', 'destination'])
            ->where('activo', true);

        if ($request->filled('country') && $request->country !== 'all') {
            $country = trim($request->country);
            $query->whereHas('country', function ($q) use ($country) {
                $q->where('slug', str($country)->slug())
                  ->orWhere('iso2', strtoupper($country));
            });
        }

        if ($request->filled('destination')) {
            $query->whereHas('destination', function ($q) use ($request) {
                $q->where('slug', $request->destination);
            });
        }

        $paquetes = $query
            ->orderByDesc('destacado')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->map(fn (TravelPackage $p) => $this->aArray($p, $idioma, false, $request));

        return response()->json([
            'success' => true,
            'count' => $paquetes->count(),
            'data' => $paquetes,
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $idioma = $request->get('lang', 'es');

        $paquete = TravelPackage::with(['country', 'destination'])
            ->where('slug', $slug)
            ->where('activo', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->aArray($paquete, $idioma, true, $request),
        ]);
    }

    private function aArray(TravelPackage $p, string $idioma, bool $full, Request $request): array
    {
        // Las fotos salen de public/images/paquetes/{slug}/ (carpeta editable a
        // mano) y, si no hay ninguna, de las URLs guardadas en la base de datos.
        // galeria() ya deja primero las de la carpeta, así que la portada es la
        // primera foto que el administrador haya puesto en la carpeta.
        $galeria = $p->galeria();

        $data = [
            'id' => $p->id,
            'name' => $p->nombre,
            'slug' => $p->slug,
            'city' => $p->ciudad,
            'presentation_text' => $p->presentacion($idioma),
            'duration' => $p->duracion,
            'min_people' => (int) $p->min_personas,
            'max_people' => (int) $p->max_personas,
            'starting_price' => (float) $p->precio_desde,
            'currency' => $p->moneda_iso,
            'image' => $this->urlAbsoluta($galeria[0] ?? null, $request),
            'featured' => (bool) $p->destacado,
            'order' => (int) $p->orden,
            'country' => $p->country?->nombre,
            'country_slug' => $p->country?->slug,
            'country_iso2' => $p->country?->iso2,
            'destination' => $p->destination?->nombre,
            'destination_slug' => $p->destination?->slug,
        ];

        if ($full) {
            $data['description'] = $p->descripcion;
            $data['includes'] = $p->getIncluyeLista($idioma);
            $data['excludes'] = $p->getNoIncluyeLista($idioma);
            $data['itinerary'] = $p->getItinerarioLista($idioma);
            $data['images'] = array_map(
                fn (string $url) => $this->urlAbsoluta($url, $request),
                $galeria
            );
            $data['latitude'] = $p->latitud;
            $data['longitude'] = $p->longitud;
        }

        return $data;
    }

    /**
     * Convierte una ruta de foto en absoluta usando el host real de la petición.
     *
     * Las fotos de la carpeta se guardan como rutas relativas ("/images/...") y
     * las URLs ya existentes (p. ej. de la base de datos) ya son absolutas. El
     * frontend corre en otro puerto en desarrollo, así que no puede resolver una
     * ruta relativa por sí solo: hay que devolverle la URL completa.
     */
    private function urlAbsoluta(?string $url, Request $request): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $url)) {
            return $url;
        }

        return $request->getSchemeAndHttpHost() . '/' . ltrim($url, '/');
    }
}
