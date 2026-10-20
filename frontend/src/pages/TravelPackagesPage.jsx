import React, { useEffect, useMemo, useRef, useState } from 'react';
import { travelPackageService } from '../services/api';
import TravelPackageCard from '../components/TravelPackageCard';
import Seo from '../components/Seo';
import { useIdioma } from '../hooks/useIdioma';
import { MdSearch, MdClose, MdPlace, MdTune, MdAutorenew, MdChevronLeft, MdChevronRight } from 'react-icons/md';

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

/**
 * Orden del listado. "recomendados" mantiene el orden que ya devuelve la API
 * (destacados primero, luego el campo `orden` y el nombre).
 */
const ORDENES = {
  recomendados: () => 0,
  precio_asc: (a, b) => a.starting_price - b.starting_price,
  precio_desc: (a, b) => b.starting_price - a.starting_price,
  nombre: (a, b) => a.name.localeCompare(b.name, 'es'),
};

/** Tarjetas por página. 6 encaja con la rejilla de 3 columnas (2 filas). */
const POR_PAGINA = 6;

export default function TravelPackagesPage() {
  const idioma = useIdioma();

  const [paquetes, setPaquetes] = useState([]);
  const [estado, setEstado] = useState('cargando');

  const [busqueda, setBusqueda] = useState('');
  const [pais, setPais] = useState('todos');
  const [orden, setOrden] = useState('recomendados');
  const [pagina, setPagina] = useState(1);

  useEffect(() => {
    let v = true;
    async function cargar() {
      try {
        const res = await travelPackageService.getPackages();
        if (v) { setPaquetes(res.data || []); setEstado('listo'); }
      } catch { if (v) setEstado('error'); }
    }
    cargar();
    return () => { v = false; };
  }, [idioma]);

  // Países presentes en el catálogo, para el selector de país del buscador.
  const paises = useMemo(() => {
    const mapa = new Map();
    paquetes.forEach((p) => {
      if (p.country && p.country_slug) mapa.set(p.country_slug, p.country);
    });
    return [...mapa.entries()]
      .map(([slug, nombre]) => ({ slug, nombre }))
      .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));
  }, [paquetes]);

  // El catálogo de paquetes es corto, así que el filtrado y el orden se hacen
  // en el navegador: el resultado aparece al instante mientras se escribe.
  const filtrados = useMemo(() => {
    const q = normalizar(busqueda);

    const lista = paquetes.filter((p) => {
      if (pais !== 'todos' && p.country_slug !== pais) return false;
      if (!q) return true;

      return [p.name, p.city, p.destination, p.country, p.presentation_text]
        .some((campo) => normalizar(campo).includes(q));
    });

    return [...lista].sort(ORDENES[orden] || ORDENES.recomendados);
  }, [paquetes, busqueda, pais, orden]);

  const hayFiltros = busqueda.trim() !== '' || pais !== 'todos' || orden !== 'recomendados';

  const limpiar = () => {
    setBusqueda('');
    setPais('todos');
    setOrden('recomendados');
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
  // cuando hay un salto. Así el navegador no se desborda con muchos paquetes.
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
      <Seo titulo="Paquetes turísticos" descripcion="Descubre paquetes turísticos para vivir experiencias únicas" />

      <section className="max-w-6xl mx-auto px-4 sm:px-6 pt-8">
        <h1 className="text-3xl font-extrabold">Paquetes turísticos</h1>
        <p className="text-slate-500 mt-1">Experiencias organizadas con todo incluido</p>
      </section>

      {/* Buscador: filtra por texto, país y orden sobre el catálogo cargado.
          Va sobre un panel de color para que salte de inmediato frente al fondo
          gris de la página. */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 mt-8">
        <div className="rounded-3xl p-5 sm:p-7 bg-gradient-to-br from-teal-600 to-emerald-500 shadow-xl shadow-teal-900/25 ring-1 ring-white/10">
          <div className="flex items-center gap-3 mb-4">
            <span className="p-2.5 rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/20 shrink-0">
              <MdSearch className="w-5 h-5 text-white" />
            </span>
            <div>
              <h2 className="text-white font-extrabold text-lg sm:text-xl leading-tight">
                Encuentra tu paquete
              </h2>
              <p className="text-teal-50/90 text-xs sm:text-sm mt-0.5">
                Busca por nombre, ciudad o destino, filtra por país y ordena los resultados.
              </p>
            </div>
          </div>

          <form
            onSubmit={(e) => e.preventDefault()}
            className="bg-white rounded-3xl p-3 sm:p-4 shadow-lg shadow-teal-950/10 flex flex-col lg:flex-row items-stretch lg:items-center gap-3"
          >
          <div className="relative w-full lg:flex-1">
            <MdSearch className="w-5 h-5 text-teal-600 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              type="text"
              value={busqueda}
              onChange={(e) => { setBusqueda(e.target.value); setPagina(1); }}
              placeholder="Busca por nombre, ciudad o destino..."
              aria-label="Buscar paquetes"
              className="w-full pl-11 pr-9 py-3 bg-slate-50 hover:bg-slate-100/80 focus:bg-white text-slate-900 placeholder:text-slate-400 font-medium text-sm rounded-2xl border border-slate-300 focus:border-teal-500 focus:outline-hidden transition-all"
            />
            {busqueda && (
              <button
                type="button"
                onClick={() => { setBusqueda(''); setPagina(1); }}
                aria-label="Borrar búsqueda"
                className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
              >
                <MdClose className="w-4 h-4" />
              </button>
            )}
          </div>

          {paises.length > 1 && (
            <div className="relative w-full lg:w-56">
              <MdPlace className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
              <select
                value={pais}
                onChange={(e) => { setPais(e.target.value); setPagina(1); }}
                aria-label="Filtrar por país"
                className="w-full pl-10 pr-8 py-3 bg-slate-50 hover:bg-slate-100/80 focus:bg-white text-slate-800 font-medium text-sm rounded-2xl border border-slate-300 focus:border-teal-500 focus:outline-hidden transition-all appearance-none cursor-pointer"
              >
                <option value="todos">Todos los países</option>
                {paises.map((p) => (
                  <option key={p.slug} value={p.slug}>{p.nombre}</option>
                ))}
              </select>
            </div>
          )}

          <div className="relative w-full lg:w-56">
            <MdTune className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <select
              value={orden}
              onChange={(e) => { setOrden(e.target.value); setPagina(1); }}
              aria-label="Ordenar paquetes"
              className="w-full pl-10 pr-8 py-3 bg-slate-50 hover:bg-slate-100/80 focus:bg-white text-slate-800 font-medium text-sm rounded-2xl border border-slate-300 focus:border-teal-500 focus:outline-hidden transition-all appearance-none cursor-pointer"
            >
              <option value="recomendados">Recomendados</option>
              <option value="precio_asc">Precio: menor a mayor</option>
              <option value="precio_desc">Precio: mayor a menor</option>
              <option value="nombre">Nombre (A-Z)</option>
            </select>
          </div>

          {hayFiltros && (
            <button
              type="button"
              onClick={limpiar}
              className="shrink-0 inline-flex items-center justify-center gap-1.5 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl text-sm font-bold transition-colors cursor-pointer"
            >
              <MdClose className="w-4 h-4" />
              Limpiar
            </button>
          )}
        </form>

          {estado === 'listo' && (
            <p className="mt-3 ml-2 text-xs font-semibold text-teal-50/90">
              {filtrados.length} {filtrados.length === 1 ? 'paquete encontrado' : 'paquetes encontrados'}
            </p>
          )}
        </div>
      </section>

      <section ref={resultadosRef} className="max-w-6xl mx-auto px-4 sm:px-6 mt-4">
        {estado === 'cargando' ? (
          <div className="py-24 flex flex-col items-center justify-center gap-3 text-slate-400">
            <MdAutorenew className="w-10 h-10 animate-spin text-teal-600" />
            <span className="text-sm font-semibold text-slate-600">Cargando paquetes...</span>
          </div>
        ) : estado === 'error' ? (
          <p className="text-sm text-slate-500 py-10 text-center">
            No se pudieron cargar los paquetes. Recarga la página.
          </p>
        ) : filtrados.length === 0 ? (
          <div className="py-20 flex flex-col items-center justify-center gap-3 text-center">
            <MdSearch className="w-10 h-10 text-slate-300" />
            <p className="text-sm font-semibold text-slate-600">
              No encontramos paquetes con esos criterios.
            </p>
            {hayFiltros && (
              <button
                onClick={limpiar}
                className="text-sm font-bold text-teal-600 hover:text-teal-700 cursor-pointer"
              >
                Limpiar filtros
              </button>
            )}
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {visibles.map((p) => <TravelPackageCard key={p.id} pkg={p} />)}
          </div>
        )}

        {/* Paginación: aparece solo si hay más de una página de resultados. */}
        {filtrados.length > 0 && totalPaginas > 1 && (
          <div className="mt-8">
            <p className="text-center text-xs font-semibold text-slate-500 tabular-nums">
              Mostrando {inicio + 1}–{fin} de {filtrados.length} paquetes
            </p>

            <nav
              className="mt-3 flex flex-wrap items-center justify-center gap-2"
              aria-label="Paginación de paquetes"
            >
              <button
                type="button"
                onClick={() => setPagina(paginaActual - 1)}
                disabled={paginaActual <= 1}
                className="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-sm font-bold bg-white border border-slate-200 text-slate-600 hover:border-[#154495]/40 hover:text-[#154495] transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
              >
                <MdChevronLeft className="w-4 h-4" />
                Anterior
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
                Siguiente
                <MdChevronRight className="w-4 h-4" />
              </button>
            </nav>
          </div>
        )}
      </section>
    </main>
  );
}
