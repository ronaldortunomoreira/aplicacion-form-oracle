import { useEffect } from 'react';

/**
 * Aplica los metadatos de la página actual sobre el documento.
 *
 * El HTML que ve un crawler en la primera respuesta lo genera el script de
 * prerender; este componente existe para que la navegación interna entre rutas
 * (que no recarga el documento) mantenga title, description, canonical, Open
 * Graph y JSON-LD coherentes.
 *
 * Usa los mismos constructores puros de `src/seo/metadatos.js` que el prerender,
 * de modo que ambos caminos producen exactamente las mismas etiquetas.
 */

const MARCA_JSONLD = 'data-seo-jsonld';

function obtieneMeta(selector) {
  return document.head.querySelector(selector);
}

/** Crea la etiqueta si no existe y le asigna el valor. */
function escribeMeta(selector, crear, atributos) {
  let elemento = obtieneMeta(selector);

  if (!elemento) {
    elemento = crear();
    document.head.appendChild(elemento);
  }

  Object.entries(atributos).forEach(([nombre, valor]) => elemento.setAttribute(nombre, valor));

  return elemento;
}

/**
 * Los datos vienen de la base de datos, así que un `</script>` en una
 * descripción rompería el documento. En JSON, `<` puede ir en un literal
 * Unicode sin cambiar el valor, que es el escape estándar para este caso.
 */
function jsonSeguro(datos) {
  return JSON.stringify(datos).replace(/</g, '\\u003c');
}

export default function Seo({
  titulo,
  descripcion,
  canonical,
  robots,
  ogType = 'website',
  ogLocale = 'es_ES',
  imagen,
  imagenAlt = '',
  alternates = [],
  jsonLd = [],
}) {
  useEffect(() => {
    if (titulo) document.title = titulo;

    if (descripcion) escribeMeta('meta[name="description"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('name', 'description');
      return meta;
    }, { content: descripcion });

    if (robots) escribeMeta('meta[name="robots"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('name', 'robots');
      return meta;
    }, { content: robots });

    if (canonical) {
      escribeMeta('link[rel="canonical"]', () => {
        const link = document.createElement('link');
        link.setAttribute('rel', 'canonical');
        return link;
      }, { href: canonical });
    }

    // Las alternativas hreflang se reescriben enteras en cada navegación: si
    // solo se actualizaran las que ya existen, las de la página anterior se
    // quedarían colgando y Google vería idiomas que ya no offered.
    document.head
      .querySelectorAll('link[rel="alternate"][data-seo-hreflang]')
      .forEach((nodo) => nodo.remove());

    alternates.forEach(({ hreflang, href }) => {
      const link = document.createElement('link');
      link.setAttribute('rel', 'alternate');
      link.setAttribute('data-seo-hreflang', '');
      link.setAttribute('hreflang', hreflang);
      link.setAttribute('href', href);
      document.head.appendChild(link);
    });

    if (ogType) escribeMeta('meta[property="og:type"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('property', 'og:type');
      return meta;
    }, { content: ogType });

    if (canonical) escribeMeta('meta[property="og:url"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('property', 'og:url');
      return meta;
    }, { content: canonical });

    if (titulo) escribeMeta('meta[property="og:title"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('property', 'og:title');
      return meta;
    }, { content: titulo });

    if (descripcion) escribeMeta('meta[property="og:description"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('property', 'og:description');
      return meta;
    }, { content: descripcion });

    if (imagen) escribeMeta('meta[property="og:image"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('property', 'og:image');
      return meta;
    }, { content: imagen });

    if (imagenAlt) escribeMeta('meta[property="og:image:alt"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('property', 'og:image:alt');
      return meta;
    }, { content: imagenAlt });

    if (ogLocale) escribeMeta('meta[property="og:locale"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('property', 'og:locale');
      return meta;
    }, { content: ogLocale });

    // og:locale:alternate avisa a Facebook y a otras redes de que la misma
    // página existe en otros idiomas, para que puedan mostrar la versión
    // adecuada en lugar de compartir siempre la que se vio primero.
    document.head
      .querySelectorAll('meta[property="og:locale:alternate"][data-seo-hreflang]')
      .forEach((nodo) => nodo.remove());

    alternates
      .filter(({ hreflang }) => hreflang !== 'x-default')
      .filter(({ href }) => href !== canonical)
      .forEach(({ ogLocale, href }) => {
        if (!ogLocale) return;

        const meta = document.createElement('meta');
        meta.setAttribute('property', 'og:locale:alternate');
        meta.setAttribute('data-seo-hreflang', '');
        meta.setAttribute('content', `${ogLocale} ${href}`);
        document.head.appendChild(meta);
      });

    if (imagen) escribeMeta('meta[name="twitter:card"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('name', 'twitter:card');
      return meta;
    }, { content: 'summary_large_image' });

    if (titulo) escribeMeta('meta[name="twitter:title"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('name', 'twitter:title');
      return meta;
    }, { content: titulo });

    if (descripcion) escribeMeta('meta[name="twitter:description"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('name', 'twitter:description');
      return meta;
    }, { content: descripcion });

    if (imagen) escribeMeta('meta[name="twitter:image"]', () => {
      const meta = document.createElement('meta');
      meta.setAttribute('name', 'twitter:image');
      return meta;
    }, { content: imagen });

    // Un solo script de datos estructurados, actualizado en el sitio.
    document.head
      .querySelectorAll(`script[${MARCA_JSONLD}]`)
      .forEach((nodo) => nodo.remove());

    if (jsonLd.length) {
      const script = document.createElement('script');
      script.type = 'application/ld+json';
      script.setAttribute(MARCA_JSONLD, '');
      script.textContent = jsonSeguro({
        '@context': 'https://schema.org',
        '@graph': jsonLd,
      });
      document.head.appendChild(script);
    }
  }, [
    titulo,
    descripcion,
    canonical,
    robots,
    ogType,
    ogLocale,
    imagen,
    imagenAlt,
    alternates,
    jsonLd,
  ]);

  return null;
}
