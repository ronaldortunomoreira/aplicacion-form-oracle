import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import AccommodationDetail from '../components/AccommodationDetail';
import Seo from '../components/Seo';
import { useReserva } from '../contexto/ReservaContexto';
import { urlBase } from '../contexto/urlBase';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { metadatosAlojamiento, metadatosNoEncontrado } from '../seo/metadatos';
import { accommodationService } from '../services/api';
import { MdChevronRight, MdAutorenew, MdSentimentDissatisfied } from 'react-icons/md';

export default function AccommodationPage() {
  const { slug } = useParams();
  const idioma = useIdioma();
  const r = useRuta();
  const { checkIn, checkOut, abrirReserva } = useReserva();

  const [accommodation, setAccommodation] = useState(null);
  const [estado, setEstado] = useState('cargando');

  useEffect(() => {
    let vigente = true;

    const cargar = async () => {
      setEstado('cargando');

      try {
        const res = await accommodationService.getAccommodationBySlug(slug, {
          check_in: checkIn || undefined,
          check_out: checkOut || undefined,
        });

        if (!vigente) return;
        setAccommodation(res.data);
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
  }, [slug, checkIn, checkOut]);

  // Mientras carga o cuando el slug no existe, los metadatos reales del
  // alojamiento aún no están disponibles, así que se sirven los de "no encontrado"
  // para que Google no indexe una página de error.
  const meta =
    accommodation && estado === 'listo'
      ? metadatosAlojamiento({ baseUrl: urlBase(), idioma, accommodation })
      : metadatosNoEncontrado({ baseUrl: urlBase(), idioma });

  if (estado === 'cargando') {
    return (
      <main className="flex-1">
        <Seo
          titulo={meta.titulo}
          descripcion={meta.descripcion}
          canonical={meta.canonical}
          robots="noindex, follow"
          ogType={meta.ogType}
          imagen={meta.imagen}
          imagenAlt={meta.imagenAlt}
        />
        <div className="py-32 flex flex-col items-center justify-center gap-3 text-slate-400">
          <MdAutorenew className="w-10 h-10 animate-spin text-rose-600" />
          <span className="text-sm font-semibold text-slate-600">Cargando alojamiento...</span>
        </div>
      </main>
    );
  }

  if (!accommodation) {
    return (
      <main className="flex-1">
        <Seo
          titulo={meta.titulo}
          descripcion={meta.descripcion}
          canonical={meta.canonical}
          robots="noindex, follow"
          ogType={meta.ogType}
          imagen={meta.imagen}
          imagenAlt={meta.imagenAlt}
        />
        <div className="max-w-2xl mx-auto px-4 py-24 text-center space-y-5">
          <div className="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
            <MdSentimentDissatisfied className="w-8 h-8" />
          </div>
          <div>
            <h1 className="text-2xl font-extrabold text-slate-900">No encontramos este alojamiento</h1>
            <p className="text-sm text-slate-500 mt-2">
              Puede que el enlace esté desactualizado o que la propiedad ya no esté disponible.
            </p>
          </div>
          <Link
            to={r('/')}
            className="inline-block px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold text-xs hover:bg-rose-700 shadow-md shadow-rose-600/20 transition-all"
          >
            Ver todos los alojamientos
          </Link>
        </div>
      </main>
    );
  }

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
      <nav aria-label="Ruta de navegación" className="max-w-5xl mx-auto px-4 sm:px-6 pt-6">
        <ol className="flex items-center gap-1.5 text-xs text-slate-500">
          <li>
            <Link to={r('/')} className="hover:text-rose-600 font-medium">
              Inicio
            </Link>
          </li>
          <li aria-hidden="true">
            <MdChevronRight className="w-3.5 h-3.5" />
          </li>
          {/*
            Enlace al país del alojamiento. Solo aparece si tiene país asignado:
            es el nivel intermedio que conecta la ficha con la landing del país
            en lugar de dejar a los dos páginas huérfanas entre sí.
          */}
          {accommodation.country_slug && (
            <>
              <li>
                <Link
                  to={r(`/paises/${accommodation.country_slug}`)}
                  className="hover:text-rose-600 font-medium"
                >
                  {accommodation.country}
                </Link>
              </li>
              <li aria-hidden="true">
                <MdChevronRight className="w-3.5 h-3.5" />
              </li>
            </>
          )}
          <li className="min-w-0 flex-1">
            <span className="block font-semibold text-slate-700 truncate" aria-current="page">
              {accommodation.name}
            </span>
          </li>
        </ol>
      </nav>

      <div className="max-w-5xl mx-auto px-4 sm:px-6 pt-4">
        <div className="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm">
          <AccommodationDetail
            accommodation={accommodation}
            onBookUnit={(unit) => abrirReserva(accommodation, unit)}
            mostrarMapa
          />
        </div>
      </div>
    </main>
  );
}
