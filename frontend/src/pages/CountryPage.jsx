import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import Seo from '../components/Seo';
import AccommodationCard from '../components/AccommodationCard';
import { urlBase } from '../contexto/urlBase';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { metadatosNoEncontrado, metadatosPais } from '../seo/metadatos';
import { copy } from '../seo/copys';
import { NOMBRE } from '../seo/sitio';
import { accommodationService } from '../services/api';
import { MdChevronRight, MdSentimentDissatisfied, MdPublic, MdAutorenew, MdPlace } from 'react-icons/md';

/**
 * Página de país: /es/paises/espana
 *
 * Existe por dos motivos. El de posicionamiento: una landing por país captura
 * búsquedas del tipo "hoteles en España" que las fichas individuales no
 * cubren. Y el de navegación: desde aquí se entra a los alojamientos de un
 * país, lo que crea un nivel intermedio de enlazado interno.
 *
 * El país no forma parte de la URL de cada alojamiento. Si lo estuviera,
 * cambiar el país de una propiedad generaría una URL nueva, y con ella la
 * obligación de mantener una redirección desde la anterior.
 */
export default function CountryPage() {
  const { slug } = useParams();
  const idioma = useIdioma();
  const r = useRuta();
  const textos = copy(idioma);

  const [datos, setDatos] = useState(null);
  const [estado, setEstado] = useState('cargando');

  useEffect(() => {
    let vigente = true;

    const cargar = async () => {
      setEstado('cargando');

      try {
        const res = await accommodationService.getCountry(slug);

        if (!vigente) return;
        setDatos(res.data);
        setEstado('listo');
      } catch (err) {
        if (!vigente) return;
        setEstado(err.response?.status === 404 ? 'no-encontrado' : 'error');
      }
    };

    cargar();

    return () => {
      vigente = false;
    };
  }, [slug, idioma]);

  // Mientras carga, o si el país no existe, se sirven los metadatos de "no
  // encontrado": una página de error nunca debe indexarse.
  const meta =
    datos && estado === 'listo'
      ? metadatosPais({ baseUrl: urlBase(), idioma, country: datos.country, accommodations: datos.accommodations })
      : metadatosNoEncontrado({ baseUrl: urlBase(), idioma });

  const metaCargando = { ...meta, robots: 'noindex, follow' };

  if (estado === 'cargando') {
    return (
      <main className="flex-1">
        <Seo
          titulo={metaCargando.titulo}
          descripcion={metaCargando.descripcion}
          canonical={metaCargando.canonical}
          robots={metaCargando.robots}
          ogType={metaCargando.ogType}
          ogLocale={metaCargando.ogLocale}
          imagen={metaCargando.imagen}
          imagenAlt={metaCargando.imagenAlt}
        />
        <div className="py-32 flex flex-col items-center justify-center gap-3 text-slate-400">
          <MdAutorenew className="w-10 h-10 animate-spin text-rose-600" />
          <span className="text-sm font-semibold text-slate-600">{textos.pais.cargando}</span>
        </div>
      </main>
    );
  }

  if (!datos) {
    return (
      <main className="flex-1">
        <Seo
          titulo={meta.titulo}
          descripcion={meta.descripcion}
          canonical={meta.canonical}
          robots={meta.robots}
          ogType={meta.ogType}
          ogLocale={meta.ogLocale}
          imagen={meta.imagen}
          imagenAlt={meta.imagenAlt}
        />
        <div className="max-w-2xl mx-auto px-4 py-24 text-center space-y-5">
          <div className="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
            <MdPublic className="w-8 h-8" />
          </div>
          <div>
            <h1 className="text-2xl font-extrabold text-slate-900">{textos.pais.noEncontrado}</h1>
            <p className="text-sm text-slate-500 mt-2">
              {textos.pais.noEncontradoAyuda}
            </p>
          </div>
          <Link
            to={r('/')}
            className="inline-block px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold text-xs hover:bg-rose-700 shadow-md shadow-rose-600/20 transition-all"
          >
            {textos.pais.verTodos}
          </Link>
        </div>
      </main>
    );
  }

  const { country, accommodations, accommodations_count: total } = datos;

  const ciudades = [...new Set(accommodations.map((a) => a.city).filter(Boolean))];

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

      {/* Migas de pan: reflejan el BreadcrumbList del JSON-LD */}
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
          <li className="min-w-0 flex-1">
            <span className="block font-semibold text-slate-700 truncate" aria-current="page">
              {country.name}
            </span>
          </li>
        </ol>
      </nav>

      {/* Encabezado de la página de país */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 pt-6">
        <div className="bg-gradient-to-br from-rose-600 to-pink-600 rounded-3xl p-8 sm:p-10 text-white shadow-lg shadow-rose-600/20">
          <p className="text-xs font-bold uppercase tracking-widest text-rose-100 flex items-center gap-2">
            <MdPublic className="w-4 h-4" />
            {NOMBRE}
          </p>
          <h1 className="text-3xl sm:text-4xl font-extrabold mt-2">{textos.pais.h1({ pais: country.name })}</h1>

          {country.presentation_text && (
            <p className="mt-3 text-sm sm:text-base text-rose-50 max-w-3xl leading-relaxed">
              {country.presentation_text}
            </p>
          )}

          <div className="mt-5 flex flex-wrap items-center gap-3 text-xs font-semibold">
            <span className="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm px-3 py-1.5 rounded-full">
              {textos.pais.totalAlojamientos({ total })}
            </span>
            {ciudades.length > 0 && (
              <span className="inline-flex items-center gap-1.5 max-w-full bg-white/15 backdrop-blur-sm px-3 py-1.5 rounded-full">
                <MdPlace className="w-3.5 h-3.5 shrink-0" />
                <span className="min-w-0 break-words">{ciudades.join(', ')}</span>
              </span>
            )}
          </div>
        </div>
      </section>

      {/* Listado de alojamientos del país */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 mt-10">
        <h2 className="text-xl font-extrabold text-slate-900">
          {textos.pais.tituloLista({ pais: country.name })}
        </h2>

        {accommodations.length === 0 ? (
          <div className="mt-6 flex flex-col items-center gap-3 text-slate-400 py-16">
            <MdSentimentDissatisfied className="w-10 h-10" />
            <p className="text-sm text-slate-600">{textos.pais.sinAlojamientos({ pais: country.name })}</p>
            <Link to={r('/')} className="text-sm font-bold text-rose-600 hover:text-rose-700">
              {textos.pais.enlacePais} {NOMBRE}
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            {accommodations.map((accommodation) => (
              <AccommodationCard key={accommodation.id} accommodation={accommodation} />
            ))}
          </div>
        )}
      </section>
    </main>
  );
}
