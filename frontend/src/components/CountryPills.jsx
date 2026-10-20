import React from 'react';
import { Link } from 'react-router-dom';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { copy } from '../seo/copys';
import { MdArrowForward, MdPlace, MdPublic } from 'react-icons/md';

/**
 * Fila de países del buscador de la portada.
 *
 * Solo filtra. El enlace a las landings `/paises/<slug>` vive en el índice de
 * países (y en el botón "Ver todos") porque un mismo control no puede ser a la
 * vez un `<button>` de filtrado y un enlace: al pulsar un `Link` se sale de la
 * portada, de modo que el filtro se aplicaría sobre un componente que ya está
 * desmontándose y el usuario no vería ningún cambio.
 *
 * El filtro es un estado efímero que no se indexa; las landings son URLs
 * permanentes que sí posicionan para "hoteles en España". Por eso son piezas
 * separadas y no una sola.
 */
export default function CountryPills({ countries = [], selected = 'all', onSelect }) {
  const idioma = useIdioma();
  const r = useRuta();
  const textos = copy(idioma);

  if (!countries.length) return null;

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
      <div className="flex items-center gap-2.5 mb-3">
        <MdPublic className="w-4 h-4 shrink-0" />
        <h2 className="text-sm font-extrabold text-slate-900">{textos.paisesFiltro.titulo}</h2>

        <Link
          to={r('/paises')}
          className="ml-auto inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700"
        >
          {textos.navegacion.paises}
          <MdArrowForward className="w-3.5 h-3.5" />
        </Link>
      </div>

      <div className="flex flex-wrap gap-2">
        <button
          type="button"
          onClick={() => onSelect('all')}
          aria-pressed={selected === 'all'}
          className={`inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer ${
            selected === 'all'
              ? 'bg-gradient-to-b from-slate-300 to-slate-500 text-white shadow-sm shadow-slate-400/30'
              : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300'
          }`}
        >
          {textos.paisesFiltro.todos}
        </button>

        {countries.map((pais) => {
          const activo = selected === pais.slug;

          return (
            <button
              key={pais.slug}
              type="button"
              onClick={() => onSelect(pais.slug)}
              aria-pressed={activo}
              className={`inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                activo
                  ? 'bg-gradient-to-b from-slate-300 to-slate-500 text-white shadow-sm shadow-slate-400/30'
                  : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300 hover:text-slate-800'
              }`}
            >
              <MdPlace className="w-3.5 h-3.5" />
              {pais.name}
              <span className={activo ? 'text-white/80' : 'text-slate-400'}>
                ({pais.accommodations_count})
              </span>
            </button>
          );
        })}
      </div>
    </div>
  );
}
