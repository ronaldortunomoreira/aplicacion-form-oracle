/**
 * Configuración de idioma del sitio.
 *
 * La plataforma es monolingüe: se publica únicamente en español. Este módulo se
 * conserva como el único punto donde se declara ese idioma, para que la app, los
 * metadatos y el script de prerender compartan la misma constante, pero ya no
 * existen varios idiomas, ni prefijos `/es` `/en` en las URLs, ni alternativas
 * `hreflang`.
 *
 * Las funciones de abajo mantienen su nombre para no tocar a sus consumidores,
 * pero están reducidas al caso español.
 */

/** Idioma del sitio. */
export const IDIOMA_POR_DEFECTO = 'es';

export const IDIOMAS = [
  {
    codigo: 'es',
    nombre: 'Español',
    hreflang: 'es',
    ogLocale: 'es_ES',
    activo: true,
  },
];

export const IDIOMAS_ACTIVOS = IDIOMAS;

/** Configuración del idioma del sitio. */
export function configIdioma() {
  return IDIOMAS[0];
}

/** true si el código corresponde al idioma publicado. */
export function esIdiomaActivo(codigo) {
  return codigo === IDIOMA_POR_DEFECTO;
}

/** Configuración del idioma del sitio. */
export function idiomaActivo() {
  return IDIOMAS[0];
}

/**
 * Normaliza una ruta interna a su forma canónica.
 *
 * Ya no añade prefijo de idioma: el sitio se sirve en la raíz, de modo que
 * `/paises/espana` es la URL final y no `/es/paises/espana`.
 */
export function rutaConIdioma(ruta) {
  const limpia = (ruta || '').replace(/^\/+/, '');

  return limpia ? `/${limpia}` : '/';
}

/** Ya no hay prefijos de idioma en las URLs. */
export function tienePrefijoIdioma() {
  return false;
}

/**
 * Devuelve la ruta tal cual, asociada al idioma del sitio.
 *
 * Se conserva por compatibilidad con `useIdioma`, que solo necesita saber el
 * código de idioma activo.
 */
export function separaIdioma(pathname) {
  return { idioma: IDIOMA_POR_DEFECTO, ruta: pathname || '/' };
}
