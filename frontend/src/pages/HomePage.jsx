import React, { useCallback, useEffect, useState } from 'react';
import CategoryPills from '../components/CategoryPills';
import CountryPills from '../components/CountryPills';
import SearchBar from '../components/SearchBar';
import FilterSidebar from '../components/FilterSidebar';
import AccommodationCard from '../components/AccommodationCard';
import Seo from '../components/Seo';
import { useReserva } from '../contexto/ReservaContexto';
import { urlBase } from '../contexto/urlBase';
import { useIdioma } from '../hooks/useIdioma';
import { metadatosHome } from '../seo/metadatos';
import { copy } from '../seo/copys';
import { accommodationService } from '../services/api';
import { MdSentimentDissatisfied, MdAutorenew, MdKeyboardArrowUp, MdKeyboardArrowDown, MdFilterList } from 'react-icons/md';

const FILTROS_INICIALES = {
  max_price: 300,
  unit_type: '',
  bathroom_type: '',
  min_rating: '',
  amenities: [],
};

export default function HomePage() {
  const idioma = useIdioma();
  const textos = copy(idioma);
  const {
    checkIn,
    setCheckIn,
    checkOut,
    setCheckOut,
    guests,
    setGuests,
    reiniciarBusqueda,
  } = useReserva();

  const [types, setTypes] = useState([]);
  const [destinations, setDestinations] = useState([]);
  const [countries, setCountries] = useState([]);
  const [accommodations, setAccommodations] = useState([]);
  const [loading, setLoading] = useState(true);

  const [selectedTypeSlug, setSelectedTypeSlug] = useState('all');
  const [selectedCountry, setSelectedCountry] = useState('all');
  const [selectedCity, setSelectedCity] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [filters, setFilters] = useState(FILTROS_INICIALES);
  const [filtrosAbiertos, setFiltrosAbiertos] = useState(false);

  // Los destinos se piden con el país ya elegido, no todos de golpe. Con cinco
  // países son pocas filas, pero el endpoint devuelve una ciudad por
  // alojamiento activo: al crecer el catálogo, la lista entera dejaría de ser
  // un autocomplete de destinos y pasaría a ser un volcado de ciudades.
  useEffect(() => {
    let vigente = true;

    const cargarDestinos = async () => {
      try {
        const res = await accommodationService.getCities(
          selectedCountry !== 'all' ? selectedCountry : null
        );
        // La respuesta puede llegar fuera de orden si el usuario cambia de país
        // rápido: sin este cierre, la petición antigua (sin filtrar) podría
        // pintarse encima de la nueva y volver a mostrar ciudades de todos los
        // países.
        if (vigente && res.success) setDestinations(res.data);
      } catch (err) {
        if (vigente) console.error('Error cargando destinos:', err);
      }
    };

    cargarDestinos();

    return () => {
      vigente = false;
    };
  }, [selectedCountry]);

  useEffect(() => {
    const cargarIniciales = async () => {
      try {
        const [typesRes, paisesRes] = await Promise.all([
          accommodationService.getTypes(),
          accommodationService.getCountries(idioma),
        ]);
        if (typesRes.success) setTypes(typesRes.data);
        if (paisesRes.success) setCountries(paisesRes.data);
      } catch (err) {
        console.error('Error cargando datos iniciales:', err);
      }
    };

    cargarIniciales();
  }, [idioma]);

  // Moneda del país elegido, para poder etiquetar el rango del filtro de precio.
  // `/api/countries` ya la trae porque `paises.moneda_iso` es una columna real.
  const monedaPaisSeleccionado =
    countries.find((pais) => pais.slug === selectedCountry)?.moneda_iso ?? null;

  const buscar = useCallback(async () => {
    try {
      setLoading(true);
      const res = await accommodationService.getAccommodations({
        type_slug: selectedTypeSlug,
        country: selectedCountry !== 'all' ? selectedCountry : undefined,
        // Viaja separada de `search`: el backend la compara de forma exacta. Si
        // se mandara dentro del texto libre, "Medellín" también.matchearía hotels
        // cuyo nombre o dirección contuvieran esa palabra.
        city: selectedCity || undefined,
        search: searchQuery || undefined,
        check_in: checkIn || undefined,
        check_out: checkOut || undefined,
        guests: guests > 1 ? guests : undefined,
        // El precio solo viaja con un país elegido: cada alojamiento está en la
        // moneda de su país y el backend ignora el filtro sin `country`.
        max_price:
          selectedCountry !== 'all' && filters.max_price < 300 ? filters.max_price : undefined,
        unit_type: filters.unit_type || undefined,
        bathroom_type: filters.bathroom_type || undefined,
        min_rating: filters.min_rating || undefined,
      });

      if (res.success) {
        let resultados = res.data;

        // Las amenidades se filtran en el frontend porque la API no las expone
        // como parámetro de búsqueda.
        if (filters.amenities.length > 0) {
          resultados = resultados.filter((item) => {
            const amenidades = item.amenities || [];
            return filters.amenities.every((a) => amenidades.includes(a));
          });
        }

        setAccommodations(resultados);
      }
    } catch (err) {
      console.error('Error consultando alojamientos:', err);
    } finally {
      setLoading(false);
    }
  }, [selectedTypeSlug, selectedCountry, selectedCity, searchQuery, checkIn, checkOut, guests, filters]);

  // Recarga cuando cambian la categoría, el país, la ciudad o los filtros del
  // sidebar. La búsqueda por texto y las fechas se lanzan explícitamente desde
  // SearchBar.
  useEffect(() => {
    buscar();
  }, [selectedTypeSlug, selectedCountry, selectedCity, filters]);

  const manejarFiltro = (key, value) => {
    setFilters((prev) => ({ ...prev, [key]: value }));
  };

  // Cambiar de país descarta la ciudad. Sin esto se podría quedar "Medellín"
  // seleccionado con Bolivia como país, una combinación imposible que el backend
  // respondería con cero resultados y sin explicación: el usuario vería la
  // ciudad en la etiqueta y una lista vacía sin motivo aparente.
  const seleccionarPais = (slug) => {
    setSelectedCountry(slug);
    if (slug !== selectedCountry) setSelectedCity(null);
  };

  // Elegir una ciudad del autocomplete fija también su país, que es lo que
  // completa la cascada: así el filtro de precio (que solo existe dentro de un
  // país) pasa a estar disponible sin un paso manual extra.
  const seleccionarCiudad = (ciudad, paisSlug) => {
    setSelectedCity(ciudad);
    if (paisSlug) setSelectedCountry(paisSlug);
  };

  const restablecerTodo = () => {
    setFilters(FILTROS_INICIALES);
    setSearchQuery('');
    setSelectedTypeSlug('all');
    setSelectedCountry('all');
    setSelectedCity(null);
    setGuests(1);
    reiniciarBusqueda();
    buscar();
  };

  const ciudades = [...new Set(accommodations.map((a) => a.city).filter(Boolean))];
  const meta = metadatosHome({
    baseUrl: urlBase(),
    idioma,
    total: accommodations.length,
    ciudades,
  });

  return (
    <main className="flex-1 pb-16">
      <Seo
        titulo={meta.titulo}
        descripcion={meta.descripcion}
        canonical={meta.canonical}
        robots={meta.robots}
        ogType={meta.ogType}
        ogLocale={meta.ogLocale}
        imagen={meta.imagen}
        imagenAlt={meta.imagenAlt}
        alternates={meta.alternates}
        jsonLd={meta.jsonLd}
      />

      {/* Hero */}
      <div className="relative overflow-hidden bg-gradient-to-br from-[#1E2A78] via-[#0E8F87] to-[#1FB8A9] text-white pt-12 pb-20 px-4 sm:px-6">
        <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#FFFFFF_1px,transparent_1px)] [background-size:16px_16px]" />
        <div className="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-[#7ED6C8]/25 blur-3xl" aria-hidden="true" />
        <div className="absolute -bottom-28 -left-20 w-72 h-72 rounded-full bg-[#1E2A78]/40 blur-3xl" aria-hidden="true" />
        <div className="max-w-5xl mx-auto text-center relative z-10 space-y-4">
          <h1 className="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight">
            {textos.home.titular} <br className="hidden sm:block" />
            <span className="bg-gradient-to-r from-[#D6EEF2] via-[#FFFFFF] to-[#7ED6C8] bg-clip-text text-transparent">
              {textos.home.titularResaltado}
            </span>
          </h1>

          <p className="text-sm sm:text-base text-[#D6EEF2] max-w-2xl mx-auto font-normal">
            {textos.home.entradilla}
          </p>
        </div>
      </div>

      {/* Buscador */}
      <SearchBar
        searchQuery={searchQuery}
        setSearchQuery={setSearchQuery}
        checkIn={checkIn}
        setCheckIn={setCheckIn}
        checkOut={checkOut}
        setCheckOut={setCheckOut}
        guests={guests}
        setGuests={setGuests}
        destinations={destinations}
        paisElegido={selectedCountry !== 'all' ? countries.find((p) => p.slug === selectedCountry)?.name ?? '' : null}
        ciudadElegida={selectedCity}
        onSelectCity={seleccionarCiudad}
        onClearCity={() => setSelectedCity(null)}
        onSearch={buscar}
        onReset={restablecerTodo}
      />

      {/* Categorías */}
      <CategoryPills
        types={types}
        selectedSlug={selectedTypeSlug}
        onSelectCategory={setSelectedTypeSlug}
      />

      {/* Países: filtro del listado y enlaces a las landings /paises/:slug */}
      <CountryPills
        countries={countries}
        selected={selectedCountry}
        onSelect={seleccionarPais}
      />

      {/* Contenido principal */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div className="flex flex-col lg:flex-row gap-8 items-start">
          {/* En móvil los filtros van plegados tras un botón para no obligar a
              desplazarse por un panel enorme antes de ver los resultados. */}
          <button
            type="button"
            onClick={() => setFiltrosAbiertos((v) => !v)}
            aria-expanded={filtrosAbiertos}
            aria-controls="panel-filtros"
            className="lg:hidden w-full flex items-center justify-between gap-2 px-4 py-3 rounded-2xl bg-gradient-to-b from-slate-300 to-slate-500 border border-transparent text-white font-bold text-sm shadow-md shadow-slate-400/30 cursor-pointer"
          >
            <span className="flex items-center gap-2">
              <MdFilterList className="w-4 h-4" />
              Filtros
            </span>
            {filtrosAbiertos ? <MdKeyboardArrowUp className="w-4 h-4" /> : <MdKeyboardArrowDown className="w-4 h-4" />}
          </button>

          <aside
            id="panel-filtros"
            className={`w-full lg:w-72 shrink-0 ${filtrosAbiertos ? 'block' : 'hidden lg:block'}`}
          >
            <FilterSidebar
              filters={filters}
              onFilterChange={manejarFiltro}
              onResetFilters={() => setFilters(FILTROS_INICIALES)}
              moneda={monedaPaisSeleccionado}
              mostrarPrecio={selectedCountry !== 'all'}
            />
          </aside>

          <div className="flex-1 w-full space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
              <h2 className="text-lg font-extrabold text-slate-900">
                {textos.home.encontrados({ total: accommodations.length })}
              </h2>
              <span className="text-xs text-slate-500 font-medium">
                {textos.home.notaMoneda}
              </span>
            </div>

            {loading ? (
              <div className="py-24 flex flex-col items-center justify-center gap-3 text-slate-400">
                <MdAutorenew className="w-10 h-10 animate-spin text-rose-600" />
                <span className="text-sm font-semibold text-slate-600">
                  {textos.home.cargando}
                </span>
              </div>
            ) : accommodations.length > 0 ? (
              <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-6">
                {accommodations.map((item) => (
                  <AccommodationCard key={item.id} accommodation={item} />
                ))}
              </div>
            ) : (
              <div className="bg-white rounded-3xl p-8 sm:p-12 text-center border border-slate-200 shadow-xs space-y-4">
                <div className="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                  <MdSentimentDissatisfied className="w-8 h-8" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-slate-900">
                    {textos.home.sinResultados}
                  </h3>
                  <p className="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    {textos.home.sinResultadosAyuda}
                  </p>
                </div>
                <button
                  type="button"
                  onClick={restablecerTodo}
                  className="px-5 py-2.5 rounded-xl bg-gradient-to-b from-slate-300 to-slate-500 text-white font-bold text-xs hover:from-slate-400 hover:to-slate-600 shadow-md shadow-slate-400/30 transition-all cursor-pointer"
                >
                  {textos.home.restablecer}
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </main>
  );
}
