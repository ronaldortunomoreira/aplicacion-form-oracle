import React from 'react';
import { useSearchParams } from 'react-router-dom';
import MyBookings from '../components/MyBookings';
import Seo from '../components/Seo';
import { urlBase } from '../contexto/urlBase';
import { useIdioma } from '../hooks/useIdioma';
import { metadatosMisReservas } from '../seo/metadatos';

export default function MyBookingsPage() {
  const [searchParams] = useSearchParams();
  const idioma = useIdioma();

  // El código y el correo llegan por query string para que el enlace que envía
  // BookingModal funcione también tras recargar la página.
  const codigo = searchParams.get('codigo') || '';
  const correo = searchParams.get('correo') || '';

  const meta = metadatosMisReservas({ baseUrl: urlBase(), idioma });

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
      <MyBookings initialCode={codigo} initialEmail={correo} />
    </main>
  );
}
