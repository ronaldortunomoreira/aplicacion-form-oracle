import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import Seo from '../components/Seo';
import { urlBase } from '../contexto/urlBase';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { metadatosDestinos } from '../seo/metadatos';
import { copy } from '../seo/copys';
import { NOMBRE } from '../seo/sitio';
import { destinationService } from '../services/api';
import { MdArrowForward, MdChevronRight, MdChevronLeft, MdExplore, MdAutorenew, MdPlace, MdSearch, MdClose } from 'react-icons/md';

/**
 * Normaliza texto para buscar sin que importen mayúsculas ni acentos:
 * "Salar de Uyuni" y "uyuni" se tratan igual.
 */
const normalizar = (texto) =>
  String(texto ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();

/** Tarjetas por página. 6 encaja con la rejilla de 3 columnas (2 filas). */
const POR_PAGINA = 6;

/**
 * Índice de destinos turísticos: /es/destinos
 *
 * Es el catálogo editorial de lugares (Salar de Uyuni, Cartagena...), distinto
 * del índice de países: aquí no se agrupa por estado, sino por sitio que merece
 * el viaje. Reparte autoridad hacia cada ficha, que si no dependerían solo de
 * los enlaces del menú.
 */
export default function DestinationsPage() {
  const idioma = useIdioma();
  const r = useRuta();
  const textos = copy(idioma);

  const [destinations, setDestinations] = useState([]);
  const [estado, setEstado] = useState('cargando');

  const [busqueda, setBusqueda] = useState('');
  const [pais, setPais] = useState('todos');
  const [pagina, setPagina] = useState(1);

  useEffect(() => {
    let vigente = true;

    const cargar = async () => {
      try {
        const res = await destinationService.getDestinations();

        if (!vigente) return;
        setDestinations(res.data || []);
        setEstado('listo');
      } catch {
        if (!vigente) return;
        setEstado('error');
      }
    };

    cargar();

    return () => {
      vigente = false;
    };
  }, [idioma]);

  const meta = metadatosDestinos({ baseUrl: urlBase(), idioma, destinations });

  // Países presentes en el catálogo de destinos, para el selector del buscador.
  const paises = useMemo(() => {
    const mapa = new Map();
    destinations.forEach((d) => {
      if (d.country && d.country_slug) mapa.set(d.country_slug, d.country);
    });
    return [...mapa.entries()]
      .map(([slug, nombre]) => ({ slug, nombre }))
      .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));
  }, [destinations]);

  // El catálogo de destinos es corto, así que el filtrado se hace en el
  // navegador: el resultado aparece al instante mientras se escribe.
  const filtrados = useMemo(() => {
    const q = normalizar(busqueda);

    return destinations.filter((d) => {
      if (pais !== 'todos' && d.country_slug !== pais) return false;
      if (!q) return true;

      return [d.name, d.city, d.country, d.presentation_text]
        .some((campo) => normalizar(campo).includes(q));
    });
  }, [destinations, busqueda, pais]);

  const hayFiltros = busqueda.trim() !== '' || pais !== 'todos';

  const limpiar = () => {
    setBusqueda('');
    setPais('todos');
    setPagina(1);
  };

  // Paginación: el listado ya filtrado se divide en páginas de POR_PAGINA
  // tarjetas. `paginaActual` acota la página guardada por si los filtros reducen
  // los resultados por debajo de la página en la que se estaba.
  const totalPaginas = Math.max(1, Math.ceil(filtrados.length / POR_PAGINA));
  const paginaActual = Math.min(pagina, totalPaginas);
  const inicio = (paginaActual - 1) * POR_PAGINA;
  const visibles = filtrados.slice(inicio, inicio + POR_PAGINA);
  const fin = inicio + visibles.length;

  // Páginas que se pintan: los extremos y las vecinas de la actual, con "..."
  // cuando hay un salto. Así el navegador no se desborda con muchos destinos.
  const paginasVisibles = useMemo(() => {
    const rango = 1;
    const items = [];

    for (let n = 1; n <= totalPaginas; n++) {
      const esBorde = n === 1 || n === totalPaginas;
      const esCerca = Math.abs(n - paginaActual) <= rango;

      if (esBorde || esCerca) {
        const anterior = items[items.length - 1];
        if (anterior && typeof anterior === 'number' && n - anterior > 1) {
          items.push('...');
        }
        items.push(n);
      }
    }

    return items;
  }, [totalPaginas, paginaActual]);

  // Al pasar de página se vuelve al inicio de los resultados. Solo para
  // páginas > 1: cuando el filtro reinicia la página a 1 mientras se escribe,
  // el desplazamiento mandaría el cuadro de búsqueda fuera de la vista.
  const resultadosRef = useRef(null);
  const paginaAnterior = useRef(1);

  useEffect(() => {
    if (pagina !== paginaAnterior.current) {
      paginaAnterior.current = pagina;

      if (pagina > 1) {
        resultadosRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  }, [pagina]);

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

      <nav aria-label="Ruta de navegación" className="max-w-6xl mx-auto px-4 sm:px-6 pt-6">
        <ol className="flex items-center gap-1.5 text-xs text-slate-500">
          <li>
            <Link to={r('/')} className="hover:text-teal-600 font-medium">
              {textos.navegacion.inicio}
            </Link>
          </li>
          <li aria-hidden="true">
            <MdChevronRight className="w-3.5 h-3.5" />
          </li>
          <li>
            <span className="font-semibold text-slate-700" aria-current="page">
              {textos.navegacion.destinos}
            </span>
          </li>
        </ol>
      </nav>

      <section className="max-w-6xl mx-auto px-4 sm:px-6 pt-6">
        <div className="bg-gradient-to-br from-teal-600 to-emerald-500 rounded-3xl p-6 sm:p-10 text-white shadow-xl shadow-teal-900/25 ring-1 ring-white/10">
          <p className="text-xs font-bold uppercase tracking-widest text-emerald-50 flex items-center gap-2">
            <MdExplore className="w-4 h-4" />
            {NOMBRE}
          </p>
          <h1 className="text-3xl sm:text-4xl font-extrabold mt-2">{textos.destinos.indiceH1}</h1>
          <p className="mt-3 text-sm sm:text-base text-emerald-50/90 max-w-3xl leading-relaxed">
            {textos.destinos.indiceResumen}
          </p>

          {/* Buscador: filtra por texto y país sobre el catálogo cargado. */}
          <form
            onSubmit={(e) => e.preventDefault()}
            className="mt-6 bg-gradient-to-br from-teal-600 to-emerald-500 rounded-2xl p-2.5 shadow-lg shadow-teal-900/25 ring-1 ring-white/20 flex flex-col sm:flex-row gap-2.5"
          >
            <div className="relative w-full sm:flex-1">
              <MdSearch className="w-4 h-4 text-teal-600 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
              <input
                type="text"
                value={busqueda}
                onChange={(e) => { setBusqueda(e.target.value); setPagina(1); }}
                placeholder={textos.destinos.buscarPlaceholder}
                aria-label={textos.destinos.buscarAria}
                className="w-full pl-9 pr-8 py-2 bg-white text-slate-900 placeholder:text-slate-400 font-medium text-sm rounded-xl border border-transparent focus:border-white focus:ring-2 focus:ring-white/40 focus:outline-hidden transition-all"
              />
              {busqueda && (
                <button
                  type="button"
                  onClick={() => { setBusqueda(''); setPagina(1); }}
                  aria-label={textos.destinos.limpiarBusqueda}
                  className="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
                >
                  <MdClose className="w-4 h-4" />
                </button>
              )}
            </div>

            {paises.length > 1 && (
              <div className="relative w-full sm:w-52">
                <MdPlace className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <select
                  value={pais}
                  onChange={(e) => { setPais(e.target.value); setPagina(1); }}
                  aria-label={textos.destinos.filtrarPorPais}
                  className="w-full pl-9 pr-8 py-2 bg-white text-slate-800 font-medium text-sm rounded-xl border border-transparent focus:border-white focus:ring-2 focus:ring-white/40 focus:outline-hidden transition-all appearance-none cursor-pointer"
                >
                  <option value="todos">{textos.destinos.todosLosPaises}</option>
                  {paises.map((p) => (
                    <option key={p.slug} value={p.slug}>{p.nombre}</option>
                  ))}
                </select>
              </div>
            )}
          </form>

          {estado === 'listo' && (
            <p className="mt-3 ml-1 text-xs font-semibold text-emerald-50">
              {textos.destinos.indiceResultados({ total: filtrados.length })}
            </p>
          )}
        </div>
      </section>

      <section ref={resultadosRef} className="max-w-6xl mx-auto px-4 sm:px-6 mt-10">
        {estado === 'cargando' ? (
          <div className="py-20 flex flex-col items-center justify-center gap-3 text-slate-400">
            <MdAutorenew className="w-10 h-10 animate-spin text-teal-600" />
            <span className="text-sm font-semibold text-slate-600">
              {textos.destinos.indiceCargando}
            </span>
          </div>
        ) : destinations.length === 0 ? (
          <p className="text-sm text-slate-500 py-10 text-center">
            {textos.destinos.indiceVacio}
          </p>
        ) : filtrados.length === 0 ? (
          <div className="py-20 flex flex-col items-center justify-center gap-3 text-center">
            <MdSearch className="w-10 h-10 text-slate-300" />
            <p className="text-sm font-semibold text-slate-600">
              {textos.destinos.indiceSinResultados}
            </p>
            {hayFiltros && (
              <button
                type="button"
                onClick={limpiar}
                className="text-sm font-bold text-teal-600 hover:text-teal-700 cursor-pointer"
              >
                {textos.destinos.limpiarFiltros}
              </button>
            )}
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {visibles.map((destino) => (
              <Link
                key={destino.slug}
                to={r(`/destinos/${destino.slug}`)}
                className="group bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1 hover:border-teal-200 transition-all duration-300 flex flex-col"
              >
                <div className="relative aspect-16/10 w-full overflow-hidden bg-slate-100">
                  {destino.image && (
                    <img
                      src={destino.image}
                      alt={destino.name}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                      loading="lazy"
                    />
                  )}

                  {destino.country && (
                    <span className="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold tracking-wide bg-slate-900/80 text-white backdrop-blur-md shadow-sm">
                      {destino.country}
                    </span>
                  )}
                </div>

                <div className="p-6 flex-1 flex flex-col justify-between">
                  <div>
                    {destino.city && (
                      <div className="flex items-center gap-1 text-xs font-semibold text-slate-500 mb-1.5">
                        <MdPlace className="w-3.5 h-3.5 text-teal-600 shrink-0" />
                        <span>{destino.city}</span>
                      </div>
                    )}

                    <h2 className="text-lg font-extrabold text-slate-900 group-hover:text-teal-600 transition-colors">
                      {destino.name}
                    </h2>

                    {destino.presentation_text && (
                      <p className="mt-2 text-xs text-slate-500 line-clamp-3 leading-relaxed">
                        {destino.presentation_text}
                      </p>
                    )}
                  </div>

                  <span className="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-teal-600">
                    {textos.destinos.verDestino}
                    <MdArrowForward className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                  </span>
                </div>
              </Link>
            ))}
          </div>
        )}

        {/* Paginación: aparece solo si hay más de una página de resultados. */}
        {filtrados.length > 0 && totalPaginas > 1 && (
          <div className="mt-10">
            <p className="text-center text-xs font-semibold text-slate-500 tabular-nums">
              {textos.destinos.mostrando({ desde: inicio + 1, hasta: fin, total: filtrados.length })}
            </p>

            <nav
              className="mt-3 flex flex-wrap items-center justify-center gap-2"
              aria-label="Paginación de destinos"
            >
              <button
                type="button"
                onClick={() => setPagina(paginaActual - 1)}
                disabled={paginaActual <= 1}
                className="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-sm font-bold bg-white border border-slate-200 text-slate-600 hover:border-[#154495]/40 hover:text-[#154495] transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
              >
                <MdChevronLeft className="w-4 h-4" />
                {textos.destinos.paginacionAnterior}
              </button>

              {paginasVisibles.map((item, i) =>
                item === '...' ? (
                  <span key={`gap-${i}`} className="px-1 text-slate-400" aria-hidden="true">…</span>
                ) : (
                  <button
                    key={item}
                    type="button"
                    onClick={() => setPagina(item)}
                    aria-current={item === paginaActual ? 'page' : undefined}
                    aria-label={`Página ${item}`}
                    className={`px-3.5 py-2 rounded-xl text-sm font-bold text-center tabular-nums transition-colors cursor-pointer ${
                      item === paginaActual
                        ? 'bg-[#154495] border border-[#154495] text-white shadow-md shadow-[#154495]/25'
                        : 'bg-white border border-slate-200 text-slate-600 hover:border-[#154495]/40 hover:text-[#154495]'
                    }`}
                  >
                    {item}
                  </button>
                ),
              )}

              <button
                type="button"
                onClick={() => setPagina(paginaActual + 1)}
                disabled={paginaActual >= totalPaginas}
                className="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-sm font-bold bg-white border border-slate-200 text-slate-600 hover:border-[#154495]/40 hover:text-[#154495] transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
              >
                {textos.destinos.paginacionSiguiente}
                <MdChevronRight className="w-4 h-4" />
              </button>
            </nav>
          </div>
        )}
      </section>
    </main>
  );
}
