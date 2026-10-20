import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Seo from '../components/Seo';
import { urlBase } from '../contexto/urlBase';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { metadatosPaises } from '../seo/metadatos';
import { copy } from '../seo/copys';
import { NOMBRE } from '../seo/sitio';
import { accommodationService } from '../services/api';
import { MdChevronRight, MdPublic, MdAutorenew, MdPlace } from 'react-icons/md';

/**
 * Índice de países: /es/paises
 *
 * Es el nivel intermedio de la jerarquía (home → países → país → alojamiento).
 * Existe por dos razones concretas: da un destino real al enlace "Países" de las
 * migas de pan en lugar de una URL rota, y reparte autoridad desde una sola
 * página hacia todas las landings de país, que si no dependerían únicamente de
 * los enlaces desde la home.
 */
export default function CountriesPage() {
  const idioma = useIdioma();
  const r = useRuta();
  const textos = copy(idioma);

  const [countries, setCountries] = useState([]);
  const [estado, setEstado] = useState('cargando');

  useEffect(() => {
    let vigente = true;

    const cargar = async () => {
      try {
        const res = await accommodationService.getCountries(idioma);

        if (!vigente) return;
        setCountries(res.data || []);
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

  const meta = metadatosPaises({ baseUrl: urlBase(), idioma, countries });

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
            <Link to={r('/')} className="hover:text-rose-600 font-medium">
              {textos.navegacion.inicio}
            </Link>
          </li>
          <li aria-hidden="true">
            <MdChevronRight className="w-3.5 h-3.5" />
          </li>
          <li>
            <span className="font-semibold text-slate-700" aria-current="page">
              {textos.navegacion.paises}
            </span>
          </li>
        </ol>
      </nav>

      <section className="max-w-6xl mx-auto px-4 sm:px-6 pt-6">
        <div className="bg-gradient-to-br from-rose-600 to-pink-600 rounded-3xl p-8 sm:p-10 text-white shadow-lg shadow-rose-600/20">
          <p className="text-xs font-bold uppercase tracking-widest text-rose-100 flex items-center gap-2">
            <MdPublic className="w-4 h-4" />
            {NOMBRE}
          </p>
          <h1 className="text-3xl sm:text-4xl font-extrabold mt-2">{textos.pais.indiceH1}</h1>
          <p className="mt-3 text-sm sm:text-base text-rose-50 max-w-3xl leading-relaxed">
            {textos.pais.indiceResumen}
          </p>
        </div>
      </section>

      <section className="max-w-6xl mx-auto px-4 sm:px-6 mt-10">
        {estado === 'cargando' ? (
          <div className="py-20 flex flex-col items-center justify-center gap-3 text-slate-400">
            <MdAutorenew className="w-10 h-10 animate-spin text-rose-600" />
            <span className="text-sm font-semibold text-slate-600">
              {textos.pais.indiceCargando}
            </span>
          </div>
        ) : countries.length === 0 ? (
          <p className="text-sm text-slate-500 py-10 text-center">
            {textos.pais.indiceVacio}
          </p>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {countries.map((pais) => (
              <Link
                key={pais.slug}
                to={r(`/paises/${pais.slug}`)}
                className="group bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1 hover:border-rose-200 transition-all duration-300 p-6"
              >
                <div className="flex items-center justify-between">
                  <div className="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <MdPlace className="w-5 h-5" />
                  </div>
                  <span className="text-[11px] font-bold text-slate-400 group-hover:text-rose-500 transition-colors">
                    {textos.pais.indiceConteo({ total: pais.accommodations_count })}
                  </span>
                </div>

                <h2 className="mt-4 text-lg font-extrabold text-slate-900 group-hover:text-rose-600 transition-colors">
                  {pais.name}
                </h2>

                {pais.presentation_text && (
                  <p className="mt-2 text-xs text-slate-500 line-clamp-3 leading-relaxed">
                    {pais.presentation_text}
                  </p>
                )}
              </Link>
            ))}
          </div>
        )}
      </section>
    </main>
  );
}
