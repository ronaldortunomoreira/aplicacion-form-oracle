/**
 * Datos de marca y copy por defecto del sitio.
 *
 * Este módulo no lee `import.meta.env` ni `process.env` a propósito: lo
 * consumen tanto la app de Vite como el script de prerender, que corre en Node
 * sin Vite. Quien lo use aporta la URL base como argumento.
 */

import { etiquetaAmenidadDe } from './copys.js';

export const NOMBRE = 'HospedaYa';

export const NOMBRE_LARGO = 'HospedaYa - Hospedajes y Alojamientos en España y Latinoamérica';

export const DESCRIPCION =
  'Reserva hoteles, hostales, cabañas, apartamentos turísticos y glamping. ' +
  'Compara precios, consulta disponibilidad en tiempo real y confirma tu reserva sin cargos ocultos.';

/** Etiqueta legible del slug del tipo de alojamiento, para titles y breadcrumbs. */
export const TIPOS_ETIQUETA = {
  hotel: 'Hotel',
  hostal: 'Hostal',
  cabana: 'Cabaña',
  apartamento: 'Apartamento turístico',
  glamping: 'Glamping',
};

/**
 * Tipo de schema.org que mejor describe cada categoría. `LodgingBusiness` es
 * el tipo genérico válido para cualquiera de ellas.
 */
export const TIPOS_SCHEMA = {
  hotel: 'Hotel',
  hostal: 'Hostel',
  cabana: 'Cabin',
  apartamento: 'Apartment',
  glamping: 'House',
};

/**
 * Etiqueta legible de cada amenidad, reutilizada en textos y JSON-LD.
 *
 * Cubre todas las amenidades que hay en la base de datos. Una que no esté aquí
 * se muestra con el slug prettificado, para que añadir una nueva no rompa la
 * página.
 */
export const AMENIDAD_ETIQUETA = {
  ac: 'Aire acondicionado',
  bar: 'Bar',
  bbq: 'Parrilla',
  beachfront: 'Frente al mar',
  breakfast: 'Desayuno incluido',
  coworking: 'Coworking',
  fireplace: 'Chimenea',
  gym: 'Gimnasio',
  heating: 'Calefacción',
  hottub: 'Jacuzzi',
  kitchen: 'Cocina',
  laundry: 'Lavandería',
  lockers: 'Casilleros',
  mountain_view: 'Vista a la montaña',
  parking: 'Parqueo',
  pets: 'Admite mascotas',
  pool: 'Piscina',
  restaurant: 'Restaurante',
  spa: 'Spa',
  wifi: 'WiFi',
  workspace: 'Espacio de trabajo',
};

/**
 * Etiqueta legible de una amenidad en español, con reserva al slug prettificado.
 *
 * Las etiquetas de cada idioma se resuelven con `etiquetaAmenidadDe()` en
 * `copys.js`; esta es la versión sin idioma, para el código que no tiene contexto
 * de idioma (o que solo escribe en el idioma por defecto).
 */
export function etiquetaAmenidad(slug) {
  return etiquetaAmenidadDe(slug);
}

/**
 * Traduce un slug de tipo a la etiqueta legible. Devuelve el propio slug si no
 * está en el mapa, para no romper si se añade una categoría nueva.
 */
export function etiquetaTipo(slug) {
  if (!slug) return 'Alojamiento';

  return TIPOS_ETIQUETA[slug] || slug.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}
