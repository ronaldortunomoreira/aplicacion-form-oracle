import React from 'react';
import { Link } from 'react-router-dom';
import Seo from '../components/Seo';
import { urlBase } from '../contexto/urlBase';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { metadatosNoEncontrado } from '../seo/metadatos';
import { copy } from '../seo/copys';
import { MdExplore } from 'react-icons/md';

export default function NotFoundPage() {
  const idioma = useIdioma();
  const r = useRuta();
  const textos = copy(idioma);
  const meta = metadatosNoEncontrado({ baseUrl: urlBase(), idioma });

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
        <p className="text-6xl font-black text-rose-600">404</p>
        <div>
          <h1 className="text-2xl font-extrabold text-slate-900">{textos.noEncontrado.h1}</h1>
          <p className="text-sm text-slate-500 mt-2">
            {textos.noEncontrado.resumen}
          </p>
        </div>
        <Link
          to={r('/')}
          className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold text-xs hover:bg-rose-700 shadow-md shadow-rose-600/20 transition-all"
        >
          <MdExplore className="w-4 h-4" />
          {textos.noEncontrado.irAExplorar}
        </Link>
      </div>
    </main>
  );
}
