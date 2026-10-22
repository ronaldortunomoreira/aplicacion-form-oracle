<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Destination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Destinos turísticos: /api/destinations y /api/destinations/{slug}.
 *
 * No confundir con /api/cities, que alimenta el autocomplete del buscador con
 * las ciudades donde hay alojamientos. Aquí se sirve el catálogo editorial de
 * destinos, con su presentación y su imagen.
 */
class DestinationController extends Controller
{
    /**
     * Listado de destinos activos, opcionalmente acotado a un país.
     *
     * Acepta `?country=` con el slug ("bolivia") o el ISO2 ("BO"), igual que el
     * resto del buscador, y `?lang=` para elegir el idioma de la presentación.
     */
    public function index(Request $request): JsonResponse
    {
        $idioma = $request->get('lang', 'es');

        $query = Destination::query()
            ->with('country')
            ->where('estado', true);

        if ($request->filled('country') && $request->country !== 'all') {
            $country = trim($request->country);
            $query->whereHas('country', function ($q) use ($country) {
                $q->where('slug', Str::slug($country))
                  ->orWhere('iso2', strtoupper($country));
            });
        }

        $destinos = $query
            // Destacados primero, luego el orden editorial y, por último, el
            // nombre: así la portada de destinos es estable entre despliegues.
            ->orderByDesc('destacado')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Destination $destino) => $this->aArray($destino, $idioma));

        return response()->json([
            'success' => true,
            'count' => $destinos->count(),
            'data' => $destinos,
        ]);
    }

    /**
     * Detalle de un destino con los alojamientos de su ciudad.
     *
     * Los alojamientos se resuelven por `pais_id` + `ciudad`, de modo que la
     * ficha del destino enseña las propiedades publicadas en ese punto sin
     * duplicar la relación en la base de datos. Si el destino no apunta a una
     * ciudad (un paraje natural, por ejemplo), devuelve la lista vacía.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $idioma = $request->get('lang', 'es');

        $destino = Destination::with('country')
            ->where('slug', $slug)
            ->where('estado', true)
            ->firstOrFail();

        $alojamientos = collect();

        if ($destino->pais_id && $destino->ciudad) {
            $alojamientos = Accommodation::query()
                ->with(['accommodationType', 'roomUnits', 'pais'])
                ->where('pais_id', $destino->pais_id)
                ->where('ciudad', $destino->ciudad)
                ->where('estado', true)
                ->orderBy('valoracion_huesped', 'desc')
                ->get()
                ->transform(function ($item) {
                    $item->starting_price = $item->roomUnits->min('base_price_per_night') ?? 0;

                    return $item;
                });
        }

        return response()->json([
            'success' => true,
            'data' => [
                'destination' => $this->aArray($destino, $idioma),
                'accommodations_count' => $alojamientos->count(),
                'accommodations' => $alojamientos,
            ],
        ]);
    }

    /**
     * Forma de la respuesta de un destino. Se construye a mano, como en
     * `countries()`, para aplanar el país y no obligar al frontend a navegar un
     * objeto anidado.
     */
    private function aArray(Destination $destino, string $idioma): array
    {
        return [
            'id' => $destino->id,
            'name' => $destino->nombre,
            'slug' => $destino->slug,
            'city' => $destino->ciudad,
            'presentation_text' => $destino->presentacion($idioma),
            'image' => $destino->imagen,
            'latitude' => $destino->latitud,
            'longitude' => $destino->longitud,
            'featured' => (bool) $destino->destacado,
            'order' => (int) $destino->orden,
            'country' => $destino->country?->nombre,
            'country_slug' => $destino->country?->slug,
            'country_iso2' => $destino->country?->iso2,
        ];
    }
}
