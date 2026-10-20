/**
 * Genera el HTML estÃ¡tico de cada ruta pÃºblica.
 *
 * Por quÃ©: una SPA de React entrega un `<div id="root"></div>` vacÃ­o. Google
 * puede ejecutar JavaScript e indexarla, pero se tarda dÃ­as o semanas, y los
 * buscadores que no ejecutan JS (y los previsualizadores de redes sociales) no
 * ven absolutamente nada. Este script escribe un archivo HTML por ruta, con el
 * title, la description, el canonical, Open Graph, JSON-LD y el texto visible
 * ya dentro del documento.
 *
 * El texto visible se incrusta dentro de `#root` y React lo sustituye al
 * montar. No se oculta con CSS: contenido oculto es una violaciÃ³n de las
 * directrices de spam de Google, y aquÃ­ se renderizarÃ­a de todos modos.
 *
 * Estructura de URLs que genera:
 *   dist/index.html                     redirecciÃ³n a la raÃ­z canÃ³nica
 *   dist/es/index.html                  home en espaÃ±ol
 *   dist/es/alojamiento/<slug>/         ficha
 *   dist/es/paises/<slug>/              landing de paÃ­s
 *   dist/es/destinos/                   Ã­ndice de destinos turÃ­sticos
 *   dist/es/destinos/<slug>/            ficha de destino
 *   dist/es/registro-hotel/             alta de alojamientos
 *   dist/es/paquetes', '/es/paquetes', '/en/paquetes', '/es/mis-reservas/               noindex
 *
 * Solo se generan los idiomas que estÃ©n a la vez activos y traducidos
 * (`IDIOMAS_PUBLICABLES`). AÃ±adir un idioma al registro sin escribir sus textos
 * no produce ninguna URL, que es justo lo que evita publicar duplicados.
 *
 * Uso:  node scripts/prerender.mjs
 *
 * Variables de entorno:
 *   VITE_SITE_URL / SITE_URL  Dominio pÃºblico para canonical/sitemap.
 *                             Se acepta cualquiera de las dos: el script corre
 *                             en Node plano pero lee el mismo .env que Vite
 *                             (--env-file-if-exists), asÃ­ que un Ãºnico
 *                             VITE_SITE_URL sirve para la app y para el build.
 *   VITE_API_URL / API_URL    Endpoint de la API
 *   PRERENDER_SOFT_FAIL=1     No abortar si la API no responde (solo desarrollo)
 */

import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

import {
  metadatosAlojamiento,
  metadatosDestino,
  metadatosDestinos,
  metadatosEmpresas,
  metadatosHome,
  metadatosMisReservas,
  metadatosPais,
  metadatosPaises,
  precioDesde,
  precioDesdeTexto,
  monedaDe,
} from '../src/seo/metadatos.js';
import { etiquetaAmenidad, NOMBRE } from '../src/seo/sitio.js';
import { rutaConIdioma } from '../src/seo/idiomas.js';
import { copy, IDIOMAS_PUBLICABLES, problemaDeParidad } from '../src/seo/copys.js';
import { formatearMoneda } from '../src/utils/moneda.js';

const RAIZ = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const DIST = join(RAIZ, 'dist');

const SITE_URL = (
  process.env.VITE_SITE_URL ||
  process.env.SITE_URL ||
  'https://hospedaya.com'
).replace(/\/+$/, '');
const API_URL = (
  process.env.VITE_API_URL ||
  process.env.API_URL ||
  'http://127.0.0.1:8000/api'
).replace(/\/+$/, '');
const SOFT_FAIL = process.env.PRERENDER_SOFT_FAIL === '1';

/* ------------------------------------------------------------------ */
/* Utilidades                                                          */
/* ------------------------------------------------------------------ */

function esc(valor) {
  if (valor == null) return '';

  return String(valor)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/** Para JSON dentro de <script>: neutraliza cualquier cierre de etiqueta. */
function jsonSeguro(datos) {
  return JSON.stringify(datos).replace(/</g, '\\u003c');
}

async function pedir(ruta) {
  const url = `${API_URL}${ruta}`;
  const respuesta = await fetch(url, { headers: { Accept: 'application/json' } });

  if (!respuesta.ok) throw new Error(`${url} respondiÃ³ ${respuesta.status}`);

  const cuerpo = await respuesta.json();

  if (cuerpo && cuerpo.success === false) throw new Error(`${url} devolviÃ³ success: false`);

  return cuerpo.data ?? cuerpo;
}

/* ------------------------------------------------------------------ */
/* Cabecera SEO                                                        */
/* ------------------------------------------------------------------ */

/**
 * Tags que este script es dueÃ±o de cara al HTML base, para reescribirlos.
 * Todos con /g: `String.replace` con una regex sin la bandera g solo cambia la
 * primera coincidencia, y el HTML base trae varias etiquetas og: y twitter:.
 */
const ETIQUETAS_GESTIONADAS = [
  /<title>[\s\S]*?<\/title>/gi,
  /<meta\s+name="description"[^>]*>/gi,
  /<meta\s+name="robots"[^>]*>/gi,
  /<link\s+rel="canonical"[^>]*>/gi,
  /<link\s+rel="alternate"[^>]*>/gi,
  /<meta\s+property="og:[^"]*"[^>]*>/gi,
  /<meta\s+name="twitter:[^"]*"[^>]*>/gi,
  /<script[^>]*type="application\/ld\+json"[^>]*>[\s\S]*?<\/script>/gi,
];

function limpiaEtiquetas(shell) {
  return ETIQUETAS_GESTIONADAS.reduce((html, patron) => html.replace(patron, ''), shell);
}

function cabeceraSeo(meta) {
  const etiquetas = [
    `<title>${esc(meta.titulo)}</title>`,
    `<meta name="description" content="${esc(meta.descripcion)}" />`,
    `<meta name="robots" content="${esc(meta.robots)}" />`,
    `<link rel="canonical" href="${esc(meta.canonical)}" />`,
  ];

  /*
   * hreflang: declara que /es/alojamiento/x y /en/alojamiento/x son la misma
   * pÃ¡gina en dos idiomas. Sin esto, Google verÃ­a dos pÃ¡ginas para la misma
   * bÃºsqueda y elegirÃ­a una como duplicado, repartiendo la autoridad entre las
   * dos. `x-default` lo usa cuando el idioma del visitante no estÃ¡ en la lista.
   */
  (meta.alternates || []).forEach(({ hreflang, href }) => {
    etiquetas.push(
      `    <link rel="alternate" hreflang="${esc(hreflang)}" href="${esc(href)}" />`
    );
  });

  etiquetas.push(
    `<meta property="og:site_name" content="${esc(NOMBRE)}" />`,
    `<meta property="og:type" content="${esc(meta.ogType)}" />`,
    `<meta property="og:locale" content="${esc(meta.ogLocale || 'es_ES')}" />`,
    `<meta property="og:url" content="${esc(meta.canonical)}" />`,
    `<meta property="og:title" content="${esc(meta.titulo)}" />`,
    `<meta property="og:description" content="${esc(meta.descripcion)}" />`
  );

  // og:locale:alternate, para que las redes sociales compartan la versiÃ³n en el
  // idioma del visitante en lugar de siempre la primera que se vio.
  (meta.alternates || [])
    .filter(({ hreflang, href }) => hreflang !== 'x-default' && href !== meta.canonical)
    .forEach(({ ogLocale, href }) => {
      if (!ogLocale) return;
      etiquetas.push(
        `<meta property="og:locale:alternate" content="${esc(`${ogLocale} ${href}`)}" />`
      );
    });

  if (meta.imagen) {
    etiquetas.push(`<meta property="og:image" content="${esc(meta.imagen)}" />`);
  }

  if (meta.imagenAlt) {
    etiquetas.push(`<meta property="og:image:alt" content="${esc(meta.imagenAlt)}" />`);
  }

  etiquetas.push(
    `<meta name="twitter:card" content="summary_large_image" />`,
    `<meta name="twitter:title" content="${esc(meta.titulo)}" />`,
    `<meta name="twitter:description" content="${esc(meta.descripcion)}" />`
  );

  if (meta.imagen) {
    etiquetas.push(`<meta name="twitter:image" content="${esc(meta.imagen)}" />`);
  }

  if (meta.jsonLd?.length) {
    const jsonLd = jsonSeguro({ '@context': 'https://schema.org', '@graph': meta.jsonLd });
    etiquetas.push(`<script type="application/ld+json">${jsonLd}</script>`);
  }

  return etiquetas.join('\n    ');
}

/* ------------------------------------------------------------------ */
/* Texto visible                                                      */
/* ------------------------------------------------------------------ */

const ESTILO_BASE =
  'font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#0f172a;max-width:1040px;margin:0 auto;padding:24px;line-height:1.6';

/** URL absoluta de una ruta interna con prefijo de idioma. */
function url(idioma, rutaInterna) {
  return `${SITE_URL}${rutaConIdioma(rutaInterna, idioma)}`;
}

function contenidoHome(idioma, accommodations, paises = []) {
  const textos = copy(idioma);
  const ciudades = [...new Set(accommodations.map((a) => a.city).filter(Boolean))].slice(0, 8);

  const items = accommodations
    .map((a) => {
      const precio =
        a.starting_price != null ? ` â€” ${textos.alojamiento.descripcionPrecio({ precio: a.starting_price })}` : '';

      return `      <li style="margin:0 0 10px">
        <a href="${esc(url(idioma, `/alojamiento/${a.slug}`))}" style="color:#e11d48;font-weight:700">${esc(a.name)}</a>
        <span> Â· ${esc([a.city, a.country].filter(Boolean).join(', '))}${esc(precio)}</span>
        ${a.headline ? `<p style="margin:2px 0 0;font-size:14px;color:#475569">${esc(a.headline)}</p>` : ''}
      </li>`;
    })
    .join('\n');

  /*
   * Enlaces a las pÃ¡ginas de paÃ­s. Son el enlazado interno que da coherencia al
   * sitio: desde la home se llega a cada paÃ­s y de cada paÃ­s a sus
   * alojamientos, en vez de dejar las landings huÃ©rfanas.
   */
  const enlacesPaises = paises.length
    ? `      <h2>${esc(textos.navegacion.paises)}</h2>
      <ul style="padding-left:18px">
${paises
  .map(
    (p) =>
      `        <li style="margin:0 0 6px"><a href="${esc(url(idioma, `/paises/${p.slug}`))}" style="color:#e11d48;font-weight:700">${esc(p.name)}</a>` +
      ` <span style="color:#64748b">(${esc(p.accommodations_count)})</span></li>`
  )
  .join('\n')}
      </ul>`
    : '';

  return `<div style="${ESTILO_BASE}">
      <h1>${esc(textos.home.h1)}</h1>
      <p>${esc(textos.home.resumen({ ciudades: ciudades.join(', ') }))}</p>
${enlacesPaises}
      <h2>${esc(textos.home.tituloAlojamientos)}</h2>
      <ul style="padding-left:18px">
${items}
      </ul>
    </div>`;
}

function contenidoPaises(idioma, countries) {
  const textos = copy(idioma);

  const items = countries
    .map(
      (p) => `      <li style="margin:0 0 10px">
        <a href="${esc(url(idioma, `/paises/${p.slug}`))}" style="color:#e11d48;font-weight:700">${esc(p.name)}</a>
        <span style="color:#64748b"> â€” ${esc(textos.pais.indiceConteo({ total: p.accommodations_count }))}</span>
        ${p.presentation_text ? `<p style="margin:2px 0 0;font-size:14px;color:#475569">${esc(p.presentation_text)}</p>` : ''}
      </li>`
    )
    .join('\n');

  return `<div style="${ESTILO_BASE}">
      <nav style="font-size:13px;color:#64748b;margin-bottom:12px"><a href="${esc(url(idioma, '/'))}">${esc(textos.navegacion.inicio)}</a> &rsaquo; ${esc(textos.navegacion.paises)}</nav>
      <h1>${esc(textos.pais.indiceH1)}</h1>
      <p>${esc(textos.pais.indiceResumen)}</p>
      <ul style="padding-left:18px">
${items}
      </ul>
    </div>`;
}

function contenidoPais(idioma, country, accommodations) {
  const textos = copy(idioma);
  const ciudades = [...new Set(accommodations.map((a) => a.city).filter(Boolean))].slice(0, 8);

  const items = accommodations
    .map((a) => {
      const precio =
        a.starting_price != null ? ` â€” ${textos.alojamiento.descripcionPrecio({ precio: a.starting_price })}` : '';

      return `      <li style="margin:0 0 10px">
        <a href="${esc(url(idioma, `/alojamiento/${a.slug}`))}" style="color:#e11d48;font-weight:700">${esc(a.name)}</a>
        <span> Â· ${esc([a.city].filter(Boolean).join(', '))}${esc(precio)}</span>
        ${a.headline ? `<p style="margin:2px 0 0;font-size:14px;color:#475569">${esc(a.headline)}</p>` : ''}
      </li>`;
    })
    .join('\n');

  return `<div style="${ESTILO_BASE}">
      <nav style="font-size:13px;color:#64748b;margin-bottom:12px"><a href="${esc(url(idioma, '/'))}">${esc(textos.navegacion.inicio)}</a> &rsaquo; ${esc(country.name)}</nav>
      <h1>${esc(textos.pais.h1({ pais: country.name }))}</h1>
      ${country.presentation_text ? `<p>${esc(country.presentation_text)}</p>` : ''}
      ${ciudades.length ? `<p style="color:#475569">${esc(ciudades.join(', '))}</p>` : ''}
      ${
        accommodations.length
          ? `<h2>${esc(textos.pais.tituloLista({ pais: country.name }))}</h2>
      <ul style="padding-left:18px">
${items}
      </ul>`
          : `<p>${esc(textos.pais.sinAlojamientos({ pais: country.name }))}</p>`
      }
    </div>`;
}

function contenidoDestinos(idioma, destinations) {
  const textos = copy(idioma);

  const items = destinations
    .map(
      (d) => `      <li style="margin:0 0 10px">
        <a href="${esc(url(idioma, `/destinos/${d.slug}`))}" style="color:#e11d48;font-weight:700">${esc(d.name)}</a>
        <span style="color:#64748b"> â€” ${esc([d.city, d.country].filter(Boolean).join(', '))}</span>
        ${d.presentation_text ? `<p style="margin:2px 0 0;font-size:14px;color:#475569">${esc(d.presentation_text)}</p>` : ''}
      </li>`
    )
    .join('\n');

  return `<div style="${ESTILO_BASE}">
      <nav style="font-size:13px;color:#64748b;margin-bottom:12px"><a href="${esc(url(idioma, '/'))}">${esc(textos.navegacion.inicio)}</a> &rsaquo; ${esc(textos.navegacion.destinos)}</nav>
      <h1>${esc(textos.destinos.indiceH1)}</h1>
      <p>${esc(textos.destinos.indiceResumen)}</p>
      <ul style="padding-left:18px">
${items}
      </ul>
    </div>`;
}

function contenidoDestino(idioma, destination, accommodations) {
  const textos = copy(idioma);

  const items = accommodations
    .map((a) => {
      const precio =
        a.starting_price != null ? ` â€” ${textos.alojamiento.descripcionPrecio({ precio: a.starting_price })}` : '';

      return `      <li style="margin:0 0 10px">
        <a href="${esc(url(idioma, `/alojamiento/${a.slug}`))}" style="color:#e11d48;font-weight:700">${esc(a.name)}</a>
        <span> Â· ${esc([a.city].filter(Boolean).join(', '))}${esc(precio)}</span>
        ${a.headline ? `<p style="margin:2px 0 0;font-size:14px;color:#475569">${esc(a.headline)}</p>` : ''}
      </li>`;
    })
    .join('\n');

  // El paÃ­s como nivel intermedio, igual que en la app y en las fichas.
  const migaPais = destination.country_slug
    ? ` &rsaquo; <a href="${esc(url(idioma, `/paises/${destination.country_slug}`))}">${esc(destination.country || '')}</a>`
    : '';

  return `<div style="${ESTILO_BASE}">
      <nav style="font-size:13px;color:#64748b;margin-bottom:12px"><a href="${esc(url(idioma, '/'))}">${esc(textos.navegacion.inicio)}</a> &rsaquo; <a href="${esc(url(idioma, '/destinos'))}">${esc(textos.navegacion.destinos)}</a>${migaPais} &rsaquo; ${esc(destination.name)}</nav>
      <h1>${esc(destination.name)}</h1>
      ${destination.presentation_text ? `<p>${esc(destination.presentation_text)}</p>` : ''}
      ${
        accommodations.length
          ? `<h2>${esc(textos.destinos.tituloLista({ destino: destination.name }))}</h2>
      <ul style="padding-left:18px">
${items}
      </ul>`
          : `<p>${esc(textos.destinos.sinAlojamientos({ destino: destination.name }))}</p>`
      }
    </div>`;
}

function contenidoAlojamiento(idioma, a) {
  const textos = copy(idioma);
  const unidades = a.room_units || [];
  const amenidades = (a.amenities || []).map(etiquetaAmenidad).filter(Boolean);
  // Se reutiliza el mismo cÃ¡lculo que los metadatos: el endpoint de detalle no
  // incluye `starting_price`, asÃ­ que sin esto el precio visible salÃ­a vacÃ­o.
  const precio = precioDesde(a);
  // Con su moneda, para que el HTML generado no anuncie USD lo que es BOB.
  const precioTexto = precioDesdeTexto(a, idioma);
  const moneda = monedaDe(a);

  const listaUnidades = unidades
    .map(
      (u) =>
        `        <li>${esc(u.name)} â€” ${esc(textos.alojamiento.porNoche({ precio: formatearMoneda(u.base_price_per_night, moneda, idioma), huespedes: Number(u.capacity_adults || 0) + Number(u.capacity_children || 0) }))}</li>`
    )
    .join('\n');

  // Migas de pan con el paÃ­s como nivel intermedio, igual que en la app.
  const migaPais = a.country_slug
    ? ` &rsaquo; <a href="${esc(url(idioma, `/paises/${a.country_slug}`))}">${esc(a.country || '')}</a>`
    : '';

  return `<div style="${ESTILO_BASE}">
      <nav style="font-size:13px;color:#64748b;margin-bottom:12px"><a href="${esc(url(idioma, '/'))}">${esc(textos.navegacion.inicio)}</a>${migaPais} &rsaquo; ${esc(a.name)}</nav>
      <h1>${esc(a.name)}</h1>
      <p style="color:#475569"><strong>${esc(a.city || '')}${a.country ? `, ${esc(a.country)}` : ''}</strong>${a.address ? ` â€” ${esc(a.address)}` : ''}</p>
      <p>${esc(a.headline || a.description || '')}</p>
      <p>${esc(a.description || '')}</p>
      ${
        precio != null
          ? `<p><strong>${esc(textos.alojamiento.precioDesde({ precio: precioTexto }))}</strong></p>`
          : ''
      }
      ${
        amenidades.length
          ? `<p><strong>${esc(textos.alojamiento.servicios)}</strong> ${esc(amenidades.join(', '))}.</p>`
          : ''
      }
      ${
        unidades.length
          ? `<h2>${esc(textos.alojamiento.tituloUnidades)}</h2>
      <ul>
${listaUnidades}
      </ul>`
          : ''
      }
    </div>`;
}

function contenidoMisReservas(textos) {
  return `<div style="${ESTILO_BASE}">
      <h1>${esc(textos.misReservas.h1)}</h1>
      <p>${esc(textos.misReservas.resumen)}</p>
    </div>`;
}

/**
 * Alta de solicitudes.
 *
 * El formulario y el listado no se prerenderizan: son estado de React y piden
 * datos a la API, asÃ­ que en HTML estÃ¡tico quedarÃ­an vacÃ­os y desincronizados.
 * Se incrusta solo el texto que los explica, que es lo que indexa un buscador y
 * lo que lee quien llega sin JavaScript antes de entender que necesita el
 * navegador para enviar la solicitud.
 */
function contenidoEmpresas(textos) {
  const t = textos.empresas;

  return `<div style="${ESTILO_BASE}">
      <h1>${esc(t.h1)}</h1>
      <p>${esc(t.resumen)}</p>
      <ul style="padding-left:18px">
        <li>${esc(t.nombre)}</li>
        <li>${esc(t.correo)}</li>
        <li>${esc(t.pais)}</li>
        <li>${esc(t.whatsapp)}</li>
        <li>${esc(t.detalle)}</li>
      </ul>
    </div>`;
}

/* ------------------------------------------------------------------ */
/* Escritura de rutas                                                  */
/* ------------------------------------------------------------------ */

function conContenido(shell, contenido) {
  // React monta sobre #root, asÃ­ que se inyecta dentro de Ã©l: el resultado
  // visible tras el arranque es idÃ©ntico al HTML prerenderizado.
  return shell.replace('<div id="root"></div>', `<div id="root">${contenido}</div>`);
}

function buildRuta({ shell, meta, contenido, rutaArchivo }) {
  let html = limpiaEtiquetas(shell);
  html = html.replace('</head>', `    ${cabeceraSeo(meta)}\n  </head>`);
  html = conContenido(html, contenido);

  return { html, rutaArchivo };
}

async function escribe({ shell, meta, contenido, ruta }) {
  const rutaArchivo = join(DIST, ruta, 'index.html');
  const { html } = buildRuta({ shell, meta, contenido, rutaArchivo });

  await mkdir(dirname(rutaArchivo), { recursive: true });
  await writeFile(rutaArchivo, html, 'utf8');

  return ruta;
}

/* ------------------------------------------------------------------ */
/* sitemap.xml y robots.txt                                            */
/* ------------------------------------------------------------------ */

/**
 * sitemap.xml con las variantes de idioma.
 *
 * Cada URL se declara una vez por idioma, y `xhtml:link` relaciona entre sÃ­ las
 * variantes de la misma pÃ¡gina. Google usa esas relaciones en lugar de adivinar
 * por los hreflang del HTML, asÃ­ que conviene que el sitemap y la pÃ¡gina
 * coincidan exactamente.
 */
function sitemap(entradas) {
  const hoy = new Date().toISOString().slice(0, 10);

  const urls = entradas
    .map(({ loc, priority, changefreq, alternates = [] }) => {
      const enlaces = alternates
        .map(
          (a) =>
            `    <xhtml:link rel="alternate" hreflang="${esc(a.hreflang)}" href="${esc(a.href)}" />`
        )
        .join('\n');

      return `  <url>
    <loc>${esc(loc)}</loc>
${enlaces}
    <lastmod>${hoy}</lastmod>
    <changefreq>${changefreq}</changefreq>
    <priority>${priority}</priority>
  </url>`;
    })
    .join('\n');

  // El prefijo xhtml declara el espacio de nombres XML, sin el cual los
  // <xhtml:link> no son vÃ¡lidos y el archivo se rechaza.
  return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
${urls}
</urlset>
`;
}

function robots() {
  return `# HospedaYa
User-agent: *
Allow: /
Disallow: /mis-reservas

# Rutas internas sin valor de búsqueda
Disallow: /assets/

Sitemap: ${SITE_URL}/sitemap.xml
`;
}

/* ------------------------------------------------------------------ */
/* Orquestación                                                        */
/* ------------------------------------------------------------------ */

async function main() {
  // La paridad de claves se comprueba antes de generar nada. Sin esto, una clave
  // olvidada al traducir se renderiza como `undefined` y llega al HTML estÃ¡tico
  // publicado, donde ya no hay consola que lo delate: es contenido roto
  // indexado. Que rompa el build es mÃ¡s barato de detectar.
  const problema = problemaDeParidad();

  if (problema) {
    console.error('[prerender] FALLO: los idiomas publicables no estÃ¡n sincronizados.');
    console.error(problema);
    console.error('[prerender] AÃ±ade las claves que faltan o desactiva el idioma en idiomas.js.');
    process.exit(1);
  }

  const shellPath = join(DIST, 'index.html');

  try {
    await readFile(shellPath, 'utf8');
  } catch {
    console.error(`[prerender] No existe ${shellPath}`);
    console.error('[prerender] El prerender necesita el bundle ya compilado. Usa');
    console.error('[prerender]   npm run build     (vite build + prerender)');
    console.error('[prerender] o ejecuta antes `vite build`.');
    process.exit(1);
  }

  const shell = await readFile(shellPath, 'utf8');

  let alojamientos = [];
  let paises = [];

  try {
    [alojamientos, paises] = await Promise.all([
      pedir('/accommodations'),
      pedir('/countries'),
    ]);
  } catch (err) {
    const mensaje = `No se pudo obtener los datos desde ${API_URL}: ${err.message}`;

    if (!SOFT_FAIL) {
      console.error(`\n[prerender] ${mensaje}`);
      console.error(
        '[prerender] El prerender necesita la API disponible en tiempo de compilaciÃ³n.\n' +
          '[prerender] ArrÃ¡ncala, o define API_URL, o usa PRERENDER_SOFT_FAIL=1 para\n' +
          '[prerender] generar solo las rutas que no dependen de la base de datos.\n'
      );
      process.exit(1);
    }

    console.warn(`[prerender] AVISO: ${mensaje}`);
    console.warn('[prerender] Se generarÃ¡ Ãºnicamente la home, sin pÃ¡ginas de alojamiento.');
  }

  const generadas = [];
  const entradasSitemap = [];

  // El detalle de cada alojamiento se pide recurso a recurso para que las
  // unidades incluyan disponibilidad y precio estimado, igual que en la app.
  // Se resuelve una sola vez y se reutiliza en todos los idiomas.
  const completos = [];

  for (const resumen of alojamientos) {
    let completo = resumen;

    try {
      completo = await pedir(`/accommodations/slug/${encodeURIComponent(resumen.slug)}`);
    } catch (err) {
      console.warn(`[prerender] AVISO: se usa el resumen de "${resumen.slug}" (${err.message})`);
    }

    completos.push(completo);
  }

  /*
   * Un recorrido por cada idioma publicado. El bucle exterior es el idioma
   * porque casi todo lo que se genera (HTML, canonical, hreflang, sitemap) es
   * especÃ­fico de idioma, y tenerlos juntos evita que una futura traducciÃ³n se
   * genere a medias en unas rutas y no en otras.
   */
  for (const idioma of IDIOMAS_PUBLICABLES) {
    // Home
    const metaHome = metadatosHome({
      baseUrl: SITE_URL,
      idioma,
      total: alojamientos.length,
      ciudades: [...new Set(alojamientos.map((a) => a.city).filter(Boolean))],
    });

    generadas.push(
      await escribe({
        shell,
        meta: metaHome,
        contenido: contenidoHome(idioma, alojamientos, paises),
        ruta: rutaConIdioma('/', idioma),
      })
    );

    entradasSitemap.push({
      loc: url(idioma, '/'),
      priority: '1.0',
      changefreq: 'daily',
      alternates: metaHome.alternates,
    });

    // Fichas de alojamiento
    for (const completo of completos) {
      const meta = metadatosAlojamiento({ baseUrl: SITE_URL, idioma, accommodation: completo });

      generadas.push(
        await escribe({
          shell,
          meta,
          contenido: contenidoAlojamiento(idioma, completo),
          ruta: rutaConIdioma(`/alojamiento/${completo.slug}`, idioma),
        })
      );

      entradasSitemap.push({
        loc: url(idioma, `/alojamiento/${completo.slug}`),
        priority: '0.8',
        changefreq: 'weekly',
        alternates: meta.alternates,
      });
    }

    // Ãndice de paÃ­ses: reparte autoridad hacia todas las landings de paÃ­s.
    const metaPaises = metadatosPaises({ baseUrl: SITE_URL, idioma, countries: paises });

    generadas.push(
      await escribe({
        shell,
        meta: metaPaises,
        contenido: contenidoPaises(idioma, paises),
        ruta: rutaConIdioma('/paises', idioma),
      })
    );

    entradasSitemap.push({
      loc: url(idioma, '/paises'),
      priority: '0.9',
      changefreq: 'weekly',
      alternates: metaPaises.alternates,
    });

    // Landings de paÃ­s
    for (const resumenPais of paises) {
      let detalle = { country: resumenPais, accommodations: [] };

      try {
        detalle = await pedir(`/countries/${encodeURIComponent(resumenPais.slug)}?lang=${idioma}`);
      } catch (err) {
        console.warn(
          `[prerender] AVISO: pÃ¡gina de paÃ­s "${resumenPais.slug}" (${err.message})`
        );
      }

      const meta = metadatosPais({
        baseUrl: SITE_URL,
        idioma,
        country: detalle.country,
        accommodations: detalle.accommodations || [],
      });

      generadas.push(
        await escribe({
          shell,
          meta,
          contenido: contenidoPais(idioma, detalle.country, detalle.accommodations || []),
          ruta: rutaConIdioma(`/paises/${resumenPais.slug}`, idioma),
        })
      );

      entradasSitemap.push({
        loc: url(idioma, `/paises/${resumenPais.slug}`),
        priority: '0.9',
        changefreq: 'weekly',
        alternates: meta.alternates,
      });
    }

    /*
     * Destinos turÃ­sticos. La lista se pide por idioma porque la presentaciÃ³n de
     * cada destino estÃ¡ traducida: reutilizar la respuesta en espaÃ±ol dejarÃ­a la
     * pÃ¡gina en inglÃ©s con los pÃ¡rrafos en espaÃ±ol.
     */
    let destinosIdioma = [];

    try {
      destinosIdioma = await pedir(`/destinations?lang=${idioma}`);
    } catch (err) {
      console.warn(`[prerender] AVISO: Ã­ndice de destinos (${err.message})`);
    }

    const metaDestinos = metadatosDestinos({ baseUrl: SITE_URL, idioma, destinations: destinosIdioma });

    generadas.push(
      await escribe({
        shell,
        meta: metaDestinos,
        contenido: contenidoDestinos(idioma, destinosIdioma),
        ruta: rutaConIdioma('/destinos', idioma),
      })
    );

    entradasSitemap.push({
      loc: url(idioma, '/destinos'),
      priority: '0.9',
      changefreq: 'weekly',
      alternates: metaDestinos.alternates,
    });

    // Fichas de destino, cada una con los alojamientos de su ciudad.
    for (const resumenDestino of destinosIdioma) {
      let detalleDestino = { destination: resumenDestino, accommodations: [] };

      try {
        detalleDestino = await pedir(
          `/destinations/${encodeURIComponent(resumenDestino.slug)}?lang=${idioma}`
        );
      } catch (err) {
        console.warn(`[prerender] AVISO: destino "${resumenDestino.slug}" (${err.message})`);
      }

      const meta = metadatosDestino({
        baseUrl: SITE_URL,
        idioma,
        destination: detalleDestino.destination,
        accommodations: detalleDestino.accommodations || [],
      });

      generadas.push(
        await escribe({
          shell,
          meta,
          contenido: contenidoDestino(
            idioma,
            detalleDestino.destination,
            detalleDestino.accommodations || []
          ),
          ruta: rutaConIdioma(`/destinos/${resumenDestino.slug}`, idioma),
        })
      );

      entradasSitemap.push({
        loc: url(idioma, `/destinos/${resumenDestino.slug}`),
        priority: '0.8',
        changefreq: 'weekly',
        alternates: meta.alternates,
      });
    }

    // Registro de alojamientos: indexable, asÃ­ que entra en el sitemap.
    const metaEmpresas = metadatosEmpresas({ baseUrl: SITE_URL, idioma });

    generadas.push(
      await escribe({
        shell,
        meta: metaEmpresas,
        contenido: contenidoEmpresas(copy(idioma)),
        ruta: rutaConIdioma('/registro-hotel', idioma),
      })
    );

    entradasSitemap.push({
      loc: url(idioma, '/registro-hotel'),
      priority: '0.7',
      changefreq: 'weekly',
      alternates: metaEmpresas.alternates,
    });

    // Mis reservas: se genera pero no entra en el sitemap (es noindex).
    generadas.push(
      await escribe({
        shell,
        meta: metadatosMisReservas({ baseUrl: SITE_URL, idioma }),
        contenido: contenidoMisReservas(copy(idioma)),
        ruta: rutaConIdioma('/mis-reservas', idioma),
      })
    );
  }

  await writeFile(join(DIST, 'sitemap.xml'), sitemap(entradasSitemap), 'utf8');
  await writeFile(join(DIST, 'robots.txt'), robots(), 'utf8');

  console.log(`[prerender] ${generadas.length} rutas HTML + sitemap.xml + robots.txt`);
  console.log(`[prerender] idiomas publicados: ${IDIOMAS_PUBLICABLES.join(', ')}`);
  console.log(`[prerender] canonical y sitemap apuntan a ${SITE_URL}`);
  if (alojamientos.length === 0) {
    console.warn('[prerender] AVISO: no se generÃ³ ningÃºn alojamiento en el sitemap.');
  }
}
main().catch((err) => {
  console.error('[prerender] Error inesperado:', err);
  process.exit(1);
});













