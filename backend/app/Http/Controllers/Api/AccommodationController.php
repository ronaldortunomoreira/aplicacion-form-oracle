<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Pais;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccommodationController extends Controller
{
    /**
     * Search and list accommodations with advanced filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Accommodation::query()
            ->with(['accommodationType', 'roomUnits', 'pais'])
            ->where('estado', true);

        // Filter by category / type
        if ($request->filled('type_slug') && $request->type_slug !== 'all') {
            $query->whereHas('accommodationType', function ($q) use ($request) {
                $q->where('slug', $request->type_slug);
            });
        } elseif ($request->filled('type_id')) {
            $query->where('tipo_alojamiento_id', $request->type_id);
        }

        // Search text (city, name, country, address)
        if ($request->filled('search')) {
            $searchTerm = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'LIKE', $searchTerm)
                  ->orWhere('ciudad', 'LIKE', $searchTerm)
                  ->orWhere('direccion', 'LIKE', $searchTerm)
                  // El país ya no es una columna de texto, así que se busca a
                  // través de la relación.
                  ->orWhereHas('pais', function ($q) use ($searchTerm) {
                      $q->where('nombre', 'LIKE', $searchTerm);
                  });
            });
        }

        // Filter by country, accepting either the slug ("espana") or the ISO
        // code ("ES"). The slug is the canonical form because it is what the
        // country pages use in their URLs.
        if ($request->filled('country') && $request->country !== 'all') {
            $country = trim($request->country);
            $query->whereHas('pais', function ($q) use ($country) {
                $q->where('slug', Str::slug($country))
                  ->orWhere('iso2', strtoupper($country));
            });
        }

        // Filter by city specifically
        //
        // Coincidencia EXACTA, no un LIKE con comodines. El selector de
        // destinos es una cascada: primero se elige el país y la API devuelve las
        // ciudades de ese país, así que el valor de `city` siempre es uno de los
        // valores que ya existen en la columna. Con `LIKE '%Rio%'` bastaba con
        // teclear tres letras para arrastrar también "Río Grande" y cualquier
        // otra ciudad que contuviera el fragmento.
        //
        // No hace falta normalizar a mano: la columna es `utf8mb4_unicode_ci`, y
        // esa collation compara sin distinguir mayúsculas, acentos ni espacios
        // finales, así que `medellin` encuentra `Medellín` y `cancun` encuentra
        // `Cancún`. Usar `=` además deja aprovechar `alojamientos_ciudad_index`.
        if ($request->filled('city')) {
            $query->where('ciudad', trim($request->city));
        }

        // Filter by stars
        if ($request->filled('star_rating')) {
            $query->where('estrellas', '>=', (int) $request->star_rating);
        }

        // Filter by guest rating (e.g. 9.0+)
        if ($request->filled('min_rating')) {
            $query->where('valoracion_huesped', '>=', (float) $request->min_rating);
        }

        // Filter by unit type (entire_place, private_room, shared_room)
        if ($request->filled('unit_type')) {
            $query->whereHas('roomUnits', function ($q) use ($request) {
                $q->where('tipo_unidad', $request->unit_type);
            });
        }

        // Filter by bathroom type (private, shared)
        if ($request->filled('bathroom_type')) {
            $query->whereHas('roomUnits', function ($q) use ($request) {
                $q->where('tipo_bano', $request->bathroom_type);
            });
        }

        // Filter by price range
        //
        // Cada precio está en la moneda de su país (BOB en Cochabamba, EUR en
        // Madrid, MXN en Cancún), así que comparar el número pelado entre países
        // no significa nada: 185 son euros o bolivianos y el filtro no puede
        // saberlo. El precio solo es comparable dentro de un país, por eso se
        // exige `country`.
        //
        // Se ignora en el servidor y no solo se oculta en el frontend, para que
        // una petición a mano o una copia cacheada del bundle anterior no
        // devuelva resultados engañosos.
        $paisElegido = $request->filled('country') && $request->country !== 'all';
        if ($paisElegido && ($request->filled('min_price') || $request->filled('max_price'))) {
            $minPrice = $request->min_price ?? 0;
            $maxPrice = $request->max_price ?? 999999;
            $query->whereHas('roomUnits', function ($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('precio_base_noche', [$minPrice, $maxPrice]);
            });
        }

        // Filter by dates and guest capacity
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;
        $guests = (int) ($request->guests ?? 1);

        if ($checkIn && $checkOut) {
            $query->whereHas('roomUnits', function ($q) use ($checkIn, $checkOut, $guests) {
                $q->whereRaw('(capacidad_adultos + capacidad_ninos) >= ?', [$guests])
                  ->whereRaw("
                      total_unidades > (
                          SELECT COUNT(*) FROM reservas
                          WHERE reservas.unidad_habitacion_id = unidades_habitacion.id
                            AND reservas.estado IN (1, 2)
                            AND reservas.fecha_entrada < ?
                            AND reservas.fecha_salida > ?
                      )
                  ", [$checkOut, $checkIn]);
            });
        } elseif ($guests > 1) {
            $query->whereHas('roomUnits', function ($q) use ($guests) {
                $q->whereRaw('(capacidad_adultos + capacidad_ninos) >= ?', [$guests]);
            });
        }

        // Order by
        $sortBy = $request->get('sort', 'rating_desc');
        switch ($sortBy) {
            case 'rating_desc':
                $query->orderBy('valoracion_huesped', 'desc');
                break;
            case 'reviews_desc':
                $query->orderBy('numero_resenas', 'desc');
                break;
            default:
                $query->orderBy('id', 'asc');
        }

        $accommodations = $this->conPreciosYDisponibilidad($query->get(), $checkIn, $checkOut);
        $accommodations = $this->conImagenes($accommodations, $request);

        return response()->json([
            'success' => true,
            'count' => $accommodations->count(),
            'data' => $accommodations,
        ]);
    }

    /**
     * Añade a cada alojamiento el precio mínimo y, si se han pedido fechas, la
     * disponibilidad por unidad. Extracción del listado para que la página de
     * país calcule exactamente los mismos campos que el buscador.
     */
    private function conPreciosYDisponibilidad($accommodations, ?string $checkIn, ?string $checkOut)
    {
        return $accommodations->transform(function ($item) use ($checkIn, $checkOut) {
            $item->starting_price = $item->roomUnits->min('base_price_per_night') ?? 0;

            if ($checkIn && $checkOut) {
                foreach ($item->roomUnits as $unit) {
                    $unit->available_count = $unit->availableUnitsCount($checkIn, $checkOut);
                }
            }

            return $item;
        });
    }

    /**
     * Sustituye la lista de fotos de cada alojamiento por su galería completa:
     * las fotos de su carpeta pública (public/images/alojamientos/<tipo>/) más
     * las URLs guardadas en la base de datos. Las rutas de carpeta se devuelven
     * absolutas con el host real de la petición, porque el frontend corre en
     * otro puerto y no puede resolver una ruta relativa por sí solo.
     */
    private function conImagenes($accommodations, Request $request)
    {
        return $accommodations->transform(function ($item) use ($request) {
            $item->images = $item->galeriaAbsoluta($request->getSchemeAndHttpHost());

            return $item;
        });
    }

    /**
     * Get single accommodation details with room units.
     */
    public function show(Request $request, $id): JsonResponse
    {
        return $this->detalleDe(
            Accommodation::with(['accommodationType', 'roomUnits', 'pais'])->findOrFail($id),
            $request
        );
    }

    /**
     * Get a single accommodation by slug.
     *
     * El frontend usa el slug en la URL (/alojamiento/grand-palace-luxury-hotel-spa)
     * porque las URLs descriptivas posicionan mejor que un id numérico, y porque
     * es lo que se puede generar un sitemap y enlazar desde la home.
     */
    public function showBySlug(Request $request, string $slug): JsonResponse
    {
        return $this->detalleDe(
            Accommodation::with(['accommodationType', 'roomUnits', 'pais'])->where('slug', $slug)->firstOrFail(),
            $request
        );
    }

    /**
     * Builds the detail response: availability and price estimate per unit.
     *
     * Shared by the id and slug lookups so both return the same shape.
     */
    private function detalleDe(Accommodation $accommodation, Request $request): JsonResponse
    {
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;

        $nights = 1;
        if ($checkIn && $checkOut) {
            $cIn = Carbon::parse($checkIn);
            $cOut = Carbon::parse($checkOut);
            if ($cOut->gt($cIn)) {
                $nights = $cIn->diffInDays($cOut);
            }
        }

        foreach ($accommodation->roomUnits as $unit) {
            if ($checkIn && $checkOut) {
                $unit->available_count = $unit->availableUnitsCount($checkIn, $checkOut);
                $unit->is_available = $unit->available_count > 0;
            } else {
                $unit->available_count = $unit->total_units;
                $unit->is_available = true;
            }

            // Price estimate
            $unit->calculated_nights = $nights;
            $unitSubtotal = ($unit->base_price_per_night * $nights) + $unit->cleaning_fee;
            $unitTaxes = round($unitSubtotal * 0.10, 2);
            $unit->estimated_total = $unitSubtotal + $unitTaxes;
        }

        $accommodation->images = $accommodation->galeriaAbsoluta($request->getSchemeAndHttpHost());

        return response()->json([
            'success' => true,
            'data' => $accommodation,
        ]);
    }

    /**
     * Get list of unique cities (grouped by country) for autocomplete.
     *
     * Alimenta el segundo paso de la cascada del buscador: primero se elige el
     * país y luego, entre `?country=`, las ciudades de ese país. Por eso devuelve
     * también `country_slug`: el frontend lo necesita para fijar los dos filtros
     * a la vez al elegir una ciudad, en lugar de deducir el país del texto.
     *
     * Se llama `cities` y no `destinations` a propósito: `/api/destinations` es
     * ahora el catálogo editorial de destinos turísticos (DestinationController).
     * Este endpoint no es un catálogo, solo la lista de ciudades donde hay
     * alojamientos.
     *
     * Sin `?country=` devuelve todas las ciudades, agrupadas por ciudad Y país.
     * Agrupar solo por ciudad mezclaría dos "Cochabamba" de países distintos en
     * una entrada con el país de una de ellas.
     *
     * Se hace join con paises porque el nombre del país ya no está en
     * alojamientos. toBase() evita hidratar modelos Accommodation: sus atributos
     * calculados (country, country_slug...) se sobrepondrían a los alias de esta
     * consulta y el 'country' del autocomplete volvería a salir vacío.
     */
    public function cities(Request $request): JsonResponse
    {
        $query = Accommodation::query()
            ->join('paises', 'alojamientos.pais_id', '=', 'paises.id')
            ->where('alojamientos.estado', true)
            // `whereHas` sobre la relación no sirve aquí porque la consulta es un
            // join puro: se filtra por la columna de la tabla unida.
            ->whereNotNull('alojamientos.ciudad');

        // Acepta el mismo valor que el resto del buscador: slug ("espana") o ISO2
        // ("ES"). Es lo coherente con `index()` y con /api/countries, de modo que
        // el slug que llega del selector de países sirve tal cual aquí.
        if ($request->filled('country') && $request->country !== 'all') {
            $country = trim($request->country);
            $query->where(function ($q) use ($country) {
                $q->where('paises.slug', Str::slug($country))
                    ->orWhere('paises.iso2', strtoupper($country));
            });
        }

        $destinations = $query
            ->select('alojamientos.ciudad as city', 'paises.nombre as country', 'paises.slug as country_slug')
            ->selectRaw('count(*) as count')
            // El id y no el nombre: dos países distintos pueden llamarse igual.
            ->groupBy('alojamientos.ciudad', 'paises.id', 'paises.nombre', 'paises.slug')
            ->orderBy('paises.nombre')
            ->orderBy('alojamientos.ciudad')
            ->toBase()
            ->get();

        return response()->json([
            'success' => true,
            // Se anuncia si la respuesta viene recortada por un país, para que el
            // frontend no presente como "todos los destinos" una lista parcial.
            'country' => $request->filled('country') && $request->country !== 'all'
                ? trim($request->country)
                : null,
            'data' => $destinations,
        ]);
    }

    /**
     * List of active countries with their accommodation count.
     *
     * Alimenta el selector de país del buscador y las páginas /es/paises/<slug>.
     * Se expone la presentación como mapa de idiomas para que el frontend
     * elija la que corresponda en lugar de recibir un único idioma.
     *
     * Deliberadamente no incluye reglas de teléfono: este catálogo es el de los
     * países con alojamientos publicados. Los datos de contacto del formulario de
     * alta salen de `GET /api/company-countries`, que sí ofrece todos los países.
     */
    public function countries(Request $request): JsonResponse
    {
        $idioma = $request->get('lang', 'es');

        $paises = Pais::query()
            ->where('estado', true)
            ->withCount('accommodations')
            ->orderBy('nombre')
            ->get()
            ->map(function ($pais) use ($idioma) {
                return [
                    'name' => $pais->nombre,
                    'slug' => $pais->slug,
                    'iso2' => $pais->iso2,
                    'primary_language' => $pais->idioma_principal,
                    'locale' => $pais->locale($idioma),
                    'presentation_text' => $pais->presentacion($idioma),
                    'accommodations_count' => $pais->accommodations_count,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $paises->count(),
            'data' => $paises,
        ]);
    }

    /**
     * Detail of a single country with its accommodations.
     *
     * The country pages need both in one request: the presentation text for the
     * intro and the list of properties to link from. Returns 404 for an unknown
     * or deactivated slug so those URLs are not published.
     */
    public function country(Request $request, string $slug): JsonResponse
    {
        $idioma = $request->get('lang', 'es');

        $pais = Pais::where('slug', $slug)
            ->where('estado', true)
            ->firstOrFail();

        $alojamientos = Accommodation::query()
            ->with(['accommodationType', 'roomUnits', 'pais'])
            ->where('pais_id', $pais->id)
            ->where('estado', true)
            ->orderBy('valoracion_huesped', 'desc')
            ->get();

        $alojamientos = $this->conPreciosYDisponibilidad(
            $alojamientos,
            $request->check_in,
            $request->check_out
        );
        $alojamientos = $this->conImagenes($alojamientos, $request);

        return response()->json([
            'success' => true,
            'data' => [
                'country' => [
                    'name' => $pais->nombre,
                    'slug' => $pais->slug,
                    'iso2' => $pais->iso2,
                    'primary_language' => $pais->idioma_principal,
                    'locale' => $pais->locale($idioma),
                    'presentation_text' => $pais->presentacion($idioma),
                ],
                'accommodations_count' => $alojamientos->count(),
                'accommodations' => $alojamientos,
            ],
        ]);
    }
}
