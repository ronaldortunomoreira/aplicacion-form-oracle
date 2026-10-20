import React from 'react';
import CompanyRegistration from '../components/CompanyRegistration';
import Seo from '../components/Seo';
import { urlBase } from '../contexto/urlBase';
import { useIdioma } from '../hooks/useIdioma';
import { metadatosEmpresas } from '../seo/metadatos';

export default function RegisterCompanyPage() {
  const idioma = useIdioma();
  const meta = metadatosEmpresas({ baseUrl: urlBase(), idioma });

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
      <CompanyRegistration />
    </main>
  );
}