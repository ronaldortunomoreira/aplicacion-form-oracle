/**
 * Constructores de metadatos de página.
 *
 * Funciones puras y sin dependencias de React ni de Vite: el componente <Seo>
 * las usa en el navegador y el script de prerender las usa en Node, de modo que
 * el HTML generado en la compilación y las etiquetas que inyecta React en
 * navegación coinciden exactamente.
 *
 * Todas las URL se construyen con prefijo de idioma (`/es/...`) y todas las
 * páginas declaran sus alternativas `hreflang`. El canonical y el sitemap
 * apuntan siempre a la variante con idioma, de modo que las URL antiguas sin
 * prefijo no compitan con ellas.
 */

import {
  etiquetaAmenidad,
  NOMBRE,
  TIPOS_SCHEMA,
  etiquetaTipo,
} from './sitio.js';
import {
  IDIOMA_POR_DEFECTO,
  configIdioma,
  rutaConIdioma,
} from './idiomas.js';
import { copy, etiquetaAmenidadDe } from './copys.js';
import { formatearMoneda } from '../utils/moneda.js';

/** Une una URL base con una ruta interna, evitando barras dobles. */
export function absoluta(baseUrl, ruta) {
  const base = (baseUrl || '').replace(/\/+$/, '');
  const limpia = ruta.startsWith('/') ? ruta : `/${ruta}`;

  return `${base}${limpia}`;
}

/**
 * Alternativas `hreflang` de una página.
 *
 * El sitio es monolingüe, así que no hay variantes que declarar: devolver una
 * lista vacía evita emitir `hreflang` que apunten a idiomas inexistentes.
 */
export function alternatesDe() {
  return [];
}

/** Recorta a un máximo de caracteres sin cortar a mitad de una palabra. */
function recortar(texto, max) {
  if (texto.length <= max) return texto;

  const cortado = texto.slice(0, max);
  const ultimoEspacio = cortado.lastIndexOf(' ');

  return `${(ultimoEspacio > max * 0.6 ? cortado.slice(0, ultimoEspacio) : cortado).trim()}…`;
}

/**
 * Precio más bajo de las unidades, o el `starting_price` que ya calcula el
 * endpoint de listado. Lo exportan también el script de prerender, para que el
 * precio visible y el del JSON-LD no puedan divergir.
 */
export function precioDesde(accommodation) {
  if (accommodation.starting_price != null) return accommodation.starting_price;

  const precios = (accommodation.room_units || [])
    .map((u) => Number(u.base_price_per_night))
    .filter((n) => !Number.isNaN(n));

  return precios.length ? Math.min(...precios) : null;
}

/**
 * Código ISO 4217 del alojamiento, o null si no tiene país asignado.
 *
 * `Offer.priceCurrency` es un campo obligatorio de schema.org: Google lo usa para
 * validar el precio y, sin él, el dato estructurado no sirve para los precios
 * enriquecidos. Antes iba 'USD' fijo, así que cada hotel de Cochabamba se
 * declaraba en dólares. Si no hay moneda no se inventa: se omite el campo,
 * porque un 'USD' falso es peor que un dato ausente.
 */
export function monedaDe(accommodation) {
  return accommodation?.currency || null;
}

/**
 * El precio ya formateado y con su moneda, tal y como lo ve el visitante. Las
 * descripciones y el `priceRange` del JSON-LD usan esta misma función que la
 * tarjeta, para que el texto indexado y el visible no puedan divergir.
 */
export function precioDesdeTexto(accommodation, idioma) {
  const precio = precioDesde(accommodation);

  if (precio == null) return null;

  return formatearMoneda(precio, monedaDe(accommodation), idioma);
}

/**
 * Une elementos como lista en prosa: "a, b y c" en español, "a, b and c" en
 * inglés.
 *
 * La conjunción no se puede dejar fija porque acaba dentro de la description y
 * del JSON-LD, que Google lee tal cual: una página en inglés con "y" entre
 * ciudades queda mal traducida. `y` viene del copy del idioma.
 */
function listaEnProsa(items, conjuncion = 'y') {
  const limpio = items.filter(Boolean);

  if (limpio.length <= 1) return limpio[0] || '';
  if (limpio.length === 2) return `${limpio[0]} ${conjuncion} ${limpio[1]}`;

  return `${limpio.slice(0, -1).join(', ')} ${conjuncion} ${limpio[limpio.length - 1]}`;
}

/**
 * Las etiquetas de amenidad se muestran capitalizadas en JSON-LD, pero dentro de
 * una frase van en minúscula, salvo los nombres propios como WiFi.
 */
function enProsa(etiqueta) {
  return etiqueta.toLowerCase().replace(/^wifi$/, 'WiFi');
}

/**
 * Etiquetas de amenidad ya traducidas al idioma de la página.
 *
 * Se resuelve aquí y no en `etiquetaAmenidad()` porque esa función es el
 * respaldo en español: sin este paso, la description de una ficha en inglés
 * acabaría con "It offers WiFi, piscina and desayuno incluido".
 */
function amenidadesTexto(accommodation, max = 4, { mayusculas = true } = {}) {
  return (accommodation.amenities || [])
    .map((slug) => etiquetaAmenidadDe(slug))
    .filter(Boolean)
    .slice(0, max)
    .map((etiqueta) => (mayusculas ? etiqueta : enProsa(etiqueta)));
}

/**
 * `addressCountry` admite texto o código ISO, pero el código es lo que espera
 * schema.org para resultados geográficos, así que se prefiere `country_iso2` y
 * solo se recurre al nombre si el alojamiento no tiene país asignado.
 */
function direccionPostal(accommodation) {
  return {
    '@type': 'PostalAddress',
    streetAddress: accommodation.address || undefined,
    addressLocality: accommodation.city || undefined,
    addressRegion: accommodation.state || undefined,
    addressCountry: accommodation.country_iso2 || accommodation.country || undefined,
  };
}

/** Nodo de migas de pan reutilizado por varias páginas. */
function migasDePan(url, pasos) {
  return {
    '@type': 'BreadcrumbList',
    itemListElement: pasos.map((paso, indice) => ({
      '@type': 'ListItem',
      position: indice + 1,
      name: paso.name,
      item: paso.url || url,
    })),
  };
}

/* ------------------------------------------------------------------ */
/* Home                                                                */
/* ------------------------------------------------------------------ */

export function metadatosHome({ baseUrl, idioma = IDIOMA_POR_DEFECTO, total = 0, ciudades = [] }) {
  const textos = copy(idioma);
  const ruta = '/';
  const url = absoluta(baseUrl, rutaConIdioma(ruta, idioma));
  const config = configIdioma(idioma);
  const listaCiudades = ciudades.slice(0, 6);

  return {
    titulo: textos.home.titulo,
    // La plantilla de copys.js ya cierra con la lista de ciudades, así que no se
    // añade un segundo resumen aquí: repetir los mismos nombres en la description
    // solo consume caracteres sin aportar nada. El fallback evita el literal
    // "undefined" cuando todavía no hay ciudades cargadas.
    descripcion: recortar(
      textos.home.metadatos({
        total,
        ciudades: listaCiudades.join(', ') || 'destinos destacados',
      }),
      300
    ),
    canonical: url,
    robots: 'index, follow',
    ogType: 'website',
    ogLocale: config.ogLocale,
    imagen: absoluta(baseUrl, '/og-default.png'),
    imagenAlt: textos.home.imagenAlt,
    alternates: alternatesDe(baseUrl, ruta),
    jsonLd: [
      {
        '@type': 'WebSite',
        '@id': `${url}#website`,
        name: NOMBRE,
        alternateName: textos.tituloLargo,
        description: textos.descripcion,
        url,
        inLanguage: idioma,
        publisher: { '@id': `${url}#organizacion` },
      },
      {
        '@type': 'Organization',
        '@id': `${url}#organizacion`,
        name: NOMBRE,
        url: absoluta(baseUrl, rutaConIdioma('/', idioma)),
        logo: {
          '@type': 'ImageObject',
          url: absoluta(baseUrl, '/og-default.png'),
        },
      },
    ],
  };
}

/* ------------------------------------------------------------------ */
/* Detalle de alojamiento                                              */
/* ------------------------------------------------------------------ */

export function metadatosAlojamiento({ baseUrl, idioma = IDIOMA_POR_DEFECTO, accommodation }) {
  const textos = copy(idioma);
  const config = configIdioma(idioma);
  const tipo = accommodation.accommodation_type || {};
  const etiqueta = etiquetaTipo(tipo.slug);
  const ruta = `/alojamiento/${accommodation.slug}`;
  const url = absoluta(baseUrl, rutaConIdioma(ruta, idioma));
  const home = absoluta(baseUrl, rutaConIdioma('/', idioma));
  const precio = precioDesde(accommodation);
  // Versión ya formateada con la moneda, para descripciones y priceRange.
  const precioTexto = precioDesdeTexto(accommodation, idioma);
  const moneda = monedaDe(accommodation);
  const unidades = accommodation.room_units || [];

  const amenities = amenidadesTexto(accommodation, 4, { mayusculas: false, idioma });
  const ubicacion = [accommodation.city, accommodation.country].filter(Boolean).join(', ');

  const titulo = textos.alojamiento.titulo({
    nombre: accommodation.name,
    ciudad: accommodation.city || accommodation.country || NOMBRE,
    marca: NOMBRE,
  });

  const intro = (accommodation.headline || accommodation.description || '').trim();
  const partes = [
    intro ? (intro.endsWith('.') ? intro : `${intro}.`) : '',
    `${etiqueta} ${textos.alojamiento.enCiudad({ ciudad: ubicacion })}.`,
    precio != null ? textos.alojamiento.descripcionPrecio({ precio: precioTexto }) : '',
    amenities.length
      ? textos.alojamiento.cuentaCon({ amenities: listaEnProsa(amenities, textos.conjuncion) })
      : '',
    unidades.length ? textos.alojamiento.unidades({ unidades: unidades.length }) : '',
  ].filter(Boolean);

  const descripcion = recortar(partes.join(' '), 300);

  const imagenes = (accommodation.images || []).filter(Boolean);

  const nodoNegocio = {
    '@type': TIPOS_SCHEMA[tipo.slug] || 'LodgingBusiness',
    '@id': `${url}#alojamiento`,
    name: accommodation.name,
    description: accommodation.description || accommodation.headline || '',
    url,
    inLanguage: idioma,
    address: direccionPostal(accommodation),
    // AggregateRating deliberadamente ausente: guest_rating y reviews_count son
    // datos de ejemplo, no reseñas reales. Declararlos en JSON-LD incumple la
    // política de reseñas falsas de Google y puede traer una penalización
    // manual. Cuando haya reseñas verificadas, añadirlo aquí.
    numberOfAccommodationUnits: unidades.length || undefined,
    amenityFeature: (accommodation.amenities || []).map((nombre) => ({
      '@type': 'LocationFeatureSpecification',
      name: etiquetaAmenidad(nombre),
      value: true,
    })),
  };

  if (imagenes.length) {
    nodoNegocio.image = imagenes;
  }

  if (precio != null) {
    // Texto legible: se usa el mismo formateo que la ficha para que el dato
    // indexado coincida con el que ve el visitante.
    nodoNegocio.priceRange = precioTexto;
  }

  if (accommodation.latitude != null && accommodation.longitude != null) {
    nodoNegocio.geo = {
      '@type': 'GeoCoordinates',
      latitude: accommodation.latitude,
      longitude: accommodation.longitude,
    };
  }

  if (unidades.length) {
    nodoNegocio.hasOfferCatalog = {
      '@type': 'OfferCatalog',
      name: textos.alojamiento.descripcionUnidades({ unidades: unidades.length }),
      itemListElement: unidades.map((unidad) => ({
        '@type': 'Offer',
        name: unidad.name,
        price: unidad.base_price_per_night,
        // Obligatorio en schema.org. Se omite si el alojamiento no tiene
        // país: un priceCurrency inventado contaminaría el dato estructurado.
        ...(moneda ? { priceCurrency: moneda } : {}),
        availability: 'https://schema.org/InStock',
        url,
        itemOffered: {
          '@type': 'Accommodation',
          name: unidad.name,
          additionalType: unidad.unit_type,
          occupancy: {
            '@type': 'QuantitativeValue',
            value:
              Number(unidad.capacity_adults || 0) + Number(unidad.capacity_children || 0),
          },
        },
      })),
    };
  }

  return {
    titulo,
    descripcion,
    canonical: url,
    robots: 'index, follow',
    ogType: 'article',
    ogLocale: config.ogLocale,
    imagen: imagenes[0] || absoluta(baseUrl, '/og-default.png'),
    imagenAlt: `${accommodation.name} en ${ubicacion}`,
    alternates: alternatesDe(baseUrl, ruta),
    jsonLd: [
      nodoNegocio,
      {
        '@type': 'Offer',
        '@id': `${url}#oferta`,
        price: precio ?? undefined,
        ...(moneda ? { priceCurrency: moneda } : {}),
        availability: 'https://schema.org/InStock',
        url,
        seller: { '@id': `${url}#organizacion` },
      },
      migasDePan(url, [
        { name: textos.navegacion.inicio, url: home },
        {
          name: textos.navegacion.alojamientos,
          url: home,
        },
        { name: accommodation.name, url },
      ]),
    ],
  };
}

/* ------------------------------------------------------------------ */
/* Página de país                                                     */
/* ------------------------------------------------------------------ */

export function metadatosPais({
  baseUrl,
  idioma = IDIOMA_POR_DEFECTO,
  country,
  accommodations = [],
}) {
  const textos = copy(idioma);
  const config = configIdioma(idioma);
  const ruta = `/paises/${country.slug}`;
  const url = absoluta(baseUrl, rutaConIdioma(ruta, idioma));
  const home = absoluta(baseUrl, rutaConIdioma('/', idioma));

  const presentacion = (country.presentation_text || '').trim();
  const ciudades = [...new Set(accommodations.map((a) => a.city).filter(Boolean))].slice(0, 8);

  const descripcion = recortar(
    presentacion ||
      `${textos.pais.tituloLista({ pais: country.name })}. ${ciudades.join(', ')}.`,
    300
  );

  const nodos = [
    {
      '@type': 'CollectionPage',
      '@id': `${url}#pagina`,
      name: textos.pais.titulo({ pais: country.name, marca: NOMBRE }),
      description: descripcion,
      url,
      inLanguage: idioma,
      isPartOf: { '@id': `${home}#website` },
      about: {
        '@type': 'Country',
        '@id': `${url}#pais`,
        name: country.name,
        alternateName: country.iso2,
      },
    },
    migasDePan(url, [
      { name: textos.navegacion.inicio, url: home },
      {
        name: textos.navegacion.paises,
        url: absoluta(baseUrl, rutaConIdioma('/paises', idioma)),
      },
      { name: country.name, url },
    ]),
  ];

  if (accommodations.length) {
    nodos.push({
      '@type': 'ItemList',
      '@id': `${url}#lista`,
      name: textos.pais.tituloLista({ pais: country.name }),
      numberOfItems: accommodations.length,
      itemListOrder: 'https://schema.org/ItemListUnordered',
      itemListElement: accommodations.map((accommodation, indice) => ({
        '@type': 'ListItem',
        position: indice + 1,
        name: accommodation.name,
        item: absoluta(
          baseUrl,
          rutaConIdioma(`/alojamiento/${accommodation.slug}`, idioma)
        ),
      })),
    });
  }

  return {
    titulo: textos.pais.titulo({ pais: country.name, marca: NOMBRE }),
    descripcion,
    canonical: url,
    robots: 'index, follow',
    ogType: 'website',
    // og:locale de una página de país combina el idioma de la página con la
    // región: es_ES para /es/paises/espana, no solo es.
    ogLocale: country.locale || config.ogLocale,
    imagen: absoluta(baseUrl, '/og-default.png'),
    imagenAlt: textos.pais.titulo({ pais: country.name, marca: NOMBRE }),
    alternates: alternatesDe(baseUrl, ruta),
    jsonLd: nodos,
  };
}

/* ------------------------------------------------------------------ */
/* Índice de países                                                   */
/* ------------------------------------------------------------------ */

export function metadatosPaises({ baseUrl, idioma = IDIOMA_POR_DEFECTO, countries = [] }) {
  const textos = copy(idioma);
  const config = configIdioma(idioma);
  const ruta = '/paises';
  const url = absoluta(baseUrl, rutaConIdioma(ruta, idioma));
  const home = absoluta(baseUrl, rutaConIdioma('/', idioma));

  const nombres = countries.map((c) => c.name).filter(Boolean);

  const descripcion = recortar(
    textos.pais.indiceMetadatos({
      pais: textos.pais.indiceH1,
      paises: nombres.length ? listaEnProsa(nombres, textos.conjuncion) : '',
    }),
    300
  );

  return {
    titulo: textos.pais.indiceTitulo({ marca: NOMBRE }),
    descripcion,
    canonical: url,
    robots: 'index, follow',
    ogType: 'website',
    ogLocale: config.ogLocale,
    imagen: absoluta(baseUrl, '/og-default.png'),
    imagenAlt: textos.pais.indiceImagenAlt({ marca: NOMBRE }),
    alternates: alternatesDe(baseUrl, ruta),
    jsonLd: [
      {
        '@type': 'CollectionPage',
        '@id': `${url}#pagina`,
        name: textos.pais.indiceTitulo({ marca: NOMBRE }),
        description: descripcion,
        url,
        inLanguage: idioma,
        isPartOf: { '@id': `${home}#website` },
      },
      migasDePan(url, [
        { name: textos.navegacion.inicio, url: home },
        { name: textos.navegacion.paises, url },
      ]),
      ...(countries.length
        ? [
            {
              '@type': 'ItemList',
              '@id': `${url}#lista`,
              name: textos.navegacion.paises,
              numberOfItems: countries.length,
              itemListOrder: 'https://schema.org/ItemListUnordered',
              itemListElement: countries.map((pais, indice) => ({
                '@type': 'ListItem',
                position: indice + 1,
                name: pais.name,
                item: absoluta(baseUrl, rutaConIdioma(`/paises/${pais.slug}`, idioma)),
              })),
            },
          ]
        : []),
    ],
  };
}

/* ------------------------------------------------------------------ */
/* Destinos turísticos                                                */
/* ------------------------------------------------------------------ */

/**
 * Índice de destinos: /es/destinos
 *
 * Es el nivel donde vive el catálogo editorial. Como el índice de países,
 * reparte autoridad hacia cada ficha de destino en lugar de dejarlas
 * enlazadas solo desde la home.
 */
export function metadatosDestinos({ baseUrl, idioma = IDIOMA_POR_DEFECTO, destinations = [] }) {
  const textos = copy(idioma);
  const config = configIdioma(idioma);
  const ruta = '/destinos';
  const url = absoluta(baseUrl, rutaConIdioma(ruta, idioma));
  const home = absoluta(baseUrl, rutaConIdioma('/', idioma));

  const nombres = destinations.map((d) => d.name).filter(Boolean);

  const descripcion = recortar(
    textos.destinos.indiceMetadatos({
      destinos: nombres.length
        ? listaEnProsa(nombres, textos.conjuncion)
        : textos.destinos.indiceH1,
    }),
    300
  );

  return {
    titulo: textos.destinos.indiceTitulo({ marca: NOMBRE }),
    descripcion,
    canonical: url,
    robots: 'index, follow',
    ogType: 'website',
    ogLocale: config.ogLocale,
    imagen: absoluta(baseUrl, '/og-default.png'),
    imagenAlt: textos.destinos.indiceImagenAlt({ marca: NOMBRE }),
    alternates: alternatesDe(baseUrl, ruta),
    jsonLd: [
      {
        '@type': 'CollectionPage',
        '@id': `${url}#pagina`,
        name: textos.destinos.indiceTitulo({ marca: NOMBRE }),
        description: descripcion,
        url,
        inLanguage: idioma,
        isPartOf: { '@id': `${home}#website` },
      },
      migasDePan(url, [
        { name: textos.navegacion.inicio, url: home },
        { name: textos.navegacion.destinos, url },
      ]),
      ...(destinations.length
        ? [
            {
              '@type': 'ItemList',
              '@id': `${url}#lista`,
              name: textos.navegacion.destinos,
              numberOfItems: destinations.length,
              itemListOrder: 'https://schema.org/ItemListUnordered',
              itemListElement: destinations.map((destino, indice) => ({
                '@type': 'ListItem',
                position: indice + 1,
                name: destino.name,
                item: absoluta(
                  baseUrl,
                  rutaConIdioma(`/destinos/${destino.slug}`, idioma)
                ),
              })),
            },
          ]
        : []),
    ],
  };
}

/**
 * Ficha de destino: /es/destinos/salar-de-uyuni
 *
 * El `presentation_text` de la base de datos ya es un texto pensado para SEO, así
 * que se usa tal cual como description en lugar de recomponerlo: así lo que lee
 * Google y lo que ve el visitante son el mismo párrafo.
 */
export function metadatosDestino({
  baseUrl,
  idioma = IDIOMA_POR_DEFECTO,
  destination,
  accommodations = [],
}) {
  const textos = copy(idioma);
  const config = configIdioma(idioma);
  const ruta = `/destinos/${destination.slug}`;
  const url = absoluta(baseUrl, rutaConIdioma(ruta, idioma));
  const home = absoluta(baseUrl, rutaConIdioma('/', idioma));
  const indice = absoluta(baseUrl, rutaConIdioma('/destinos', idioma));
  const pais = destination.country || '';

  const presentacion = (destination.presentation_text || '').trim();

  const descripcion = recortar(
    presentacion ||
      `${textos.destinos.tituloLista({ destino: destination.name })}. ` +
        `${textos.destinos.totalAlojamientos({ total: accommodations.length })}.`,
    300
  );

  const nodoDestino = {
    '@type': 'TouristDestination',
    '@id': `${url}#destino`,
    name: destination.name,
    description: presentacion || undefined,
    url,
    inLanguage: idioma,
    isPartOf: { '@id': `${home}#website` },
  };

  if (destination.latitude != null && destination.longitude != null) {
    nodoDestino.geo = {
      '@type': 'GeoCoordinates',
      latitude: destination.latitude,
      longitude: destination.longitude,
    };
  }

  if (destination.country) {
    // `addressCountry` prefiere el ISO2, igual que en las fichas de alojamiento.
    nodoDestino.address = {
      '@type': 'PostalAddress',
      addressCountry: destination.country_iso2 || destination.country,
    };
  }

  const nodos = [
    nodoDestino,
    migasDePan(url, [
      { name: textos.navegacion.inicio, url: home },
      { name: textos.navegacion.destinos, url: indice },
      { name: destination.name, url },
    ]),
  ];

  if (accommodations.length) {
    nodos.push({
      '@type': 'ItemList',
      '@id': `${url}#lista`,
      name: textos.destinos.tituloLista({ destino: destination.name }),
      numberOfItems: accommodations.length,
      itemListOrder: 'https://schema.org/ItemListUnordered',
      itemListElement: accommodations.map((accommodation, indiceItem) => ({
        '@type': 'ListItem',
        position: indiceItem + 1,
        name: accommodation.name,
        item: absoluta(
          baseUrl,
          rutaConIdioma(`/alojamiento/${accommodation.slug}`, idioma)
        ),
      })),
    });
  }

  return {
    titulo: textos.destinos.titulo({ destino: destination.name, pais, marca: NOMBRE }),
    descripcion,
    canonical: url,
    robots: 'index, follow',
    ogType: 'website',
    ogLocale: config.ogLocale,
    imagen: destination.image || absoluta(baseUrl, '/og-default.png'),
    imagenAlt: `${destination.name}${pais ? `, ${pais}` : ''}`,
    alternates: alternatesDe(baseUrl, ruta),
    jsonLd: nodos,
  };
}

/* ------------------------------------------------------------------ */
/* Solicitudes, mis reservas y 404                                    */
/* ------------------------------------------------------------------ */

/**
 * Página de alta de solicitudes.
 *
 * Va indexada: es la vía de entrada de quien tiene un alojamiento y quiere que
 * lo publiquen, y el texto que la explica sí se prerenderiza, así que hay algo
 * que un buscador pueda leer sin ejecutar JavaScript.
 */
export function metadatosEmpresas({ baseUrl, idioma = IDIOMA_POR_DEFECTO }) {
  const textos = copy(idioma);
  const config = configIdioma(idioma);
  const ruta = '/registro-hotel';
  const url = absoluta(baseUrl, rutaConIdioma(ruta, idioma));
  const home = absoluta(baseUrl, rutaConIdioma('/', idioma));

  return {
    titulo: textos.empresas.titulo({ marca: NOMBRE }),
    descripcion: recortar(textos.empresas.metadatos, 300),
    canonical: url,
    robots: 'index, follow',
    ogType: 'website',
    ogLocale: config.ogLocale,
    imagen: absoluta(baseUrl, '/og-default.png'),
    imagenAlt: textos.empresas.h1,
    alternates: alternatesDe(baseUrl, ruta),
    jsonLd: [
      {
        '@type': 'WebPage',
        '@id': `${url}#pagina`,
        name: textos.empresas.titulo({ marca: NOMBRE }),
        description: textos.empresas.metadatos,
        url,
        inLanguage: idioma,
        isPartOf: { '@id': `${home}#website` },
      },
      migasDePan(url, [
        { name: textos.navegacion.inicio, url: home },
        { name: textos.empresas.h1, url },
      ]),
    ],
  };
}

export function metadatosMisReservas({ baseUrl, idioma = IDIOMA_POR_DEFECTO }) {
  const textos = copy(idioma);
  const config = configIdioma(idioma);
  const ruta = '/mis-reservas';

  return {
    titulo: textos.misReservas.titulo({ marca: NOMBRE }),
    descripcion: textos.misReservas.metadatos,
    canonical: absoluta(baseUrl, rutaConIdioma(ruta, idioma)),
    robots: 'noindex, follow',
    ogType: 'website',
    ogLocale: config.ogLocale,
    imagen: absoluta(baseUrl, '/og-default.png'),
    imagenAlt: NOMBRE,
    alternates: alternatesDe(baseUrl, ruta),
    jsonLd: [],
  };
}

export function metadatosNoEncontrado({ baseUrl, idioma = IDIOMA_POR_DEFECTO }) {
  const textos = copy(idioma);
  const config = configIdioma(idioma);

  return {
    titulo: textos.noEncontrado.titulo({ marca: NOMBRE }),
    descripcion: textos.noEncontrado.metadatos,
    canonical: absoluta(baseUrl, rutaConIdioma('/', idioma)),
    robots: 'noindex, follow',
    ogType: 'website',
    ogLocale: config.ogLocale,
    imagen: absoluta(baseUrl, '/og-default.png'),
    imagenAlt: NOMBRE,
    alternates: alternatesDe(baseUrl, '/'),
    jsonLd: [],
  };
}

/**
 * Metadatos de la redirección de la raíz a la variante con idioma.
 *
 * Se usan en el `index.html` de la raíz. Va con `noindex` para que la raíz no
 * compita con `/es/`, y el canonical apunta allí: la idea es que Google consolid
 * todo el peso en la URL con idioma.
 */
export function metadatosRedireccionIdioma({ baseUrl, destino, idioma = IDIOMA_POR_DEFECTO }) {
  const textos = copy(idioma);
  const config = configIdioma(idioma);

  return {
    // La redirección desemboca en la home, así que hereda su título: si el
    // crawler mostrara esta página en un resultado, no debe hablar de reservas.
    titulo: textos.home.titulo,
    descripcion: textos.descripcion,
    canonical: absoluta(baseUrl, rutaConIdioma('/', idioma)),
    robots: 'noindex, follow',
    ogType: 'website',
    ogLocale: config.ogLocale,
    imagen: absoluta(baseUrl, '/og-default.png'),
    imagenAlt: textos.home.imagenAlt,
    jsonLd: [],
    destino,
  };
}
