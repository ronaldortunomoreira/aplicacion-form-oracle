/**
 * URL base del sitio para canonical, Open Graph y breadcrumbs.
 *
 * En producción hay que definir VITE_SITE_URL (por ejemplo
 * https://hospedaya.com) para que las etiquetas apunten al dominio real. Si no
 * está definida se usa el origen desde el que se sirve la app, que es lo
 * correcto en desarrollo.
 *
 * Vive fuera de `seo/metadatos.js` porque ese módulo también corre en Node
 * (script de prerender) y allí no existe `import.meta.env`.
 */
export function urlBase() {
  if (import.meta.env.VITE_SITE_URL) return import.meta.env.VITE_SITE_URL;

  if (typeof window !== 'undefined' && window.location?.origin) return window.location.origin;

  return '';
}
