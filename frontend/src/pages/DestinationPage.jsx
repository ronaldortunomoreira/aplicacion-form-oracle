import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import Seo from '../components/Seo';
import AccommodationCard from '../components/AccommodationCard';
import { urlBase } from '../contexto/urlBase';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { metadatosDestino, metadatosNoEncontrado } from '../seo/metadatos';
import { copy } from '../seo/copys';
import { NOMBRE } from '../seo/sitio';
import { destinationService } from '../services/api';
import { MdChevronRight, MdExplore, MdSentimentDissatisfied, MdAutorenew, MdPlace } from 'react-icons/md';

/**
 * Ficha de destino turístico: /es/destinos/salar-de-uyuni
 *
 * Existe por posicionamiento: captura búsquedas del tipo "qué ver en MdClose" y las
 * convierte en reservas al listar los alojamientos de la ciudad del destino. La
 * relación con los alojamientos la resuelve la API por país y ciudad, así que la
 * base de datos no duplica el vínculo.
 */
export default function DestinationPage() {
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
        const res = await destinationService.getDestination(slug);

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

  // Igual que en las páginas de país, una ruta que no resuelve se sirve con los
  // metadatos de "no encontrado": una página de error nunca debe indexarse.
  const meta =
    datos && estado === 'listo'
      ? metadatosDestino({
          baseUrl: urlBase(),
          idioma,
          destination: datos.destination,
          accommodations: datos.accommodations,
        })
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
          <MdAutorenew className="w-10 h-10 animate-spin text-teal-600" />
          <span className="text-sm font-semibold text-slate-600">{textos.destinos.cargando}</span>
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
            <MdExplore className="w-8 h-8" />
          </div>
          <div>
            <h1 className="text-2xl font-extrabold text-slate-900">
              {textos.destinos.noEncontrado}
            </h1>
            <p className="text-sm text-slate-500 mt-2">{textos.destinos.noEncontradoAyuda}</p>
          </div>
          <Link
            to={r('/destinos')}
            className="inline-block px-5 py-2.5 rounded-xl bg-teal-600 text-white font-bold text-xs hover:bg-teal-700 shadow-md shadow-teal-600/20 transition-all"
          >
            {textos.destinos.volverAlIndice}
          </Link>
        </div>
      </main>
    );
  }

  const { destination, accommodations, accommodations_count: total } = datos;

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
            <Link to={r('/')} className="hover:text-teal-600 font-medium">
              {textos.navegacion.inicio}
            </Link>
          </li>
          <li aria-hidden="true">
            <MdChevronRight className="w-3.5 h-3.5" />
          </li>
          <li>
            <Link to={r('/destinos')} className="hover:text-teal-600 font-medium">
              {textos.navegacion.destinos}
            </Link>
          </li>
          <li aria-hidden="true">
            <MdChevronRight className="w-3.5 h-3.5" />
          </li>
          <li className="min-w-0 flex-1">
            <span className="block font-semibold text-slate-700 truncate" aria-current="page">
              {destination.name}
            </span>
          </li>
        </ol>
      </nav>

      {/* Encabezado con la imagen del destino */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 pt-6">
        <div className="relative overflow-hidden rounded-3xl shadow-lg shadow-teal-600/20">
          {destination.image && (
            <img
              src={destination.image}
              alt={destination.name}
              className="absolute inset-0 w-full h-full object-cover"
            />
          )}

          <div className="relative bg-gradient-to-br from-slate-900/85 to-teal-900/70 p-8 sm:p-10 text-white">
            <p className="text-xs font-bold uppercase tracking-widest text-emerald-50 flex items-center gap-2">
              <MdExplore className="w-4 h-4" />
              {NOMBRE}
            </p>
            <h1 className="text-3xl sm:text-4xl font-extrabold mt-2">{destination.name}</h1>

            <div className="mt-3 flex flex-wrap items-center gap-3 text-xs font-semibold">
              {destination.country && (
                <Link
                  to={destination.country_slug ? r(`/paises/${destination.country_slug}`) : r('/paises')}
                  className="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm px-3 py-1.5 rounded-full hover:bg-white/25 transition-colors"
                >
                  <MdPlace className="w-3.5 h-3.5" />
                  {textos.destinos.enPais({ pais: destination.country })}
                </Link>
              )}
              <span className="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm px-3 py-1.5 rounded-full">
                {textos.destinos.totalAlojamientos({ total })}
              </span>
            </div>

            {destination.presentation_text && (
              <p className="mt-4 text-sm sm:text-base text-emerald-50/90 max-w-3xl leading-relaxed">
                {destination.presentation_text}
              </p>
            )}
          </div>
        </div>
      </section>

      {/* Alojamientos publicados en la ciudad del destino */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 mt-10">
        <h2 className="text-xl font-extrabold text-slate-900">
          {textos.destinos.tituloLista({ destino: destination.name })}
        </h2>

        {accommodations.length === 0 ? (
          <div className="mt-6 flex flex-col items-center gap-3 text-slate-400 py-16">
            <MdSentimentDissatisfied className="w-10 h-10" />
            <p className="text-sm text-slate-600">
              {textos.destinos.sinAlojamientos({ destino: destination.name })}
            </p>
            <Link to={r('/destinos')} className="text-sm font-bold text-teal-600 hover:text-teal-700">
              {textos.destinos.volverAlIndice}
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
