import { IDIOMA_POR_DEFECTO, rutaConIdioma } from '../seo/idiomas.js';

/**
 * Idioma del sitio.
 *
 * La plataforma es monolingüe (español), así que siempre devuelve el mismo
 * código. Se mantiene como hook para no tener que reescribir cada componente
 * que ya lo consumía.
 */
export function useIdioma() {
  return IDIOMA_POR_DEFECTO;
}

/**
 * Constructor de enlaces internos.
 *
 *   const r = useRuta();
 *   <Link to={r('/paises/espana')}>  ->  /paises/espana
 *
 * Se conserva como punto único donde se arma una ruta interna, aunque el sitio
 * ya no lleve prefijo de idioma.
 */
export function useRuta() {
  return (rutaInterna) => rutaConIdioma(rutaInterna);
}
