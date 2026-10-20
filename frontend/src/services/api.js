import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
});

export const accommodationService = {
  // Get all categories (Hoteles, Hostales, Cabañas, etc.)
  getTypes: async () => {
    const response = await api.get('/accommodation-types');
    return response.data;
  },

  // Ciudades para el autocomplete del buscador. Acepta el país (slug o ISO2)
  // para devolver solo las suyas: es el segundo paso de la cascada país ->
  // ciudad. Sin `country`, devuelve todas las ciudades agrupadas por país.
  //
  // Se llama /cities y no /destinations: el catálogo editorial de destinos
  // turísticos (destinationService) vive ahora en /destinations.
  getCities: async (country = null) => {
    const response = await api.get('/cities', {
      params: country && country !== 'all' ? { country } : undefined,
    });
    return response.data;
  },

  // List active countries with their accommodation count. Alimenta el selector de
  // país del buscador y el pie con enlaces a las páginas /paises/:slug.
  getCountries: async () => {
    const response = await api.get('/countries');
    return response.data;
  },

  // Country detail with its accommodations, for /paises/:slug
  getCountry: async (slug, params = {}) => {
    const response = await api.get(`/countries/${slug}`, { params });
    return response.data;
  },

  // List accommodations with filter params
  getAccommodations: async (params = {}) => {
    const response = await api.get('/accommodations', { params });
    return response.data;
  },

  // Get single accommodation with rooms & pricing
  getAccommodation: async (id, params = {}) => {
    const response = await api.get(`/accommodations/${id}`, { params });
    return response.data;
  },

  // Get single accommodation by slug. Resuelve la URL /alojamiento/:slug, que
  // es descriptiva y por tanto mejor para buscadores que un id numérico.
  getAccommodationBySlug: async (slug, params = {}) => {
    const response = await api.get(`/accommodations/slug/${slug}`, { params });
    return response.data;
  },
};

export const destinationService = {
  // Catálogo editorial de destinos turísticos (Salar de Uyuni, Tulum...), no el
  // autocomplete de ciudades. Acepta `country` (slug o ISO2) y `lang` para
  // servir la presentación en el idioma de la página.
  getDestinations: async (params = {}) => {
    const response = await api.get('/destinations', { params });
    return response.data;
  },

  // Ficha de un destino por slug, con los alojamientos de su ciudad ya
  // enlazados: es lo que convierte /destinos/:slug en una página de aterrizaje.
  getDestination: async (slug, params = {}) => {
    const response = await api.get(`/destinations/${slug}`, { params });
    return response.data;
  },
};

export const reservationService = {
  // Reserva de paquete turístico. Usa la instancia `api` (URL absoluta de la
  // API y cabecera Accept: application/json) como el resto de peticiones: con
  // axios "pelado" y una URL relativa la petición se colaba al servidor de Vite.
  reservePackage: async (data) => {
    const response = await api.post('/reservations/package', data);
    return response.data;
  },
  // Country catalogue for the booking form. Same endpoint as the hotel
  // registration form: both need the dial code and an example number per
  // country, and both want every country, not just the five with published
  // accommodations that /countries returns.
  countries: async () => {
    const response = await api.get('/company-countries');
    return response.data;
  },

  // Price breakdown preview
  preview: async (payload) => {
    const response = await api.post('/reservations/preview', payload);
    return response.data;
  },

  // Create booking
  create: async (payload) => {
    const response = await api.post('/reservations', payload);
    return response.data;
  },

  // Lookup booking by code & email
  lookup: async (code, email) => {
    const response = await api.post('/reservations/lookup', { code, email });
    return response.data;
  },

  // Cancel booking
  cancel: async (code, email, reason = '') => {
    const response = await api.put(`/reservations/${code}/cancel`, { email, reason });
    return response.data;
  },
};

export const companyService = {
  // Requests submitted from the public form, newest first.
  list: async () => {
    const response = await api.get('/companies');
    return response.data;
  },

  // Send a request. Returns the stored row so the caller can prepend it.
  create: async (payload) => {
    const response = await api.post('/companies', payload);
    return response.data;
  },

  // Country catalogue for the registration form, with the dial code and an
  // example number per country. Deliberately a different endpoint than
  // /countries: that one only lists countries that have published
  // accommodations, and this one covers every country we can validate.
  countries: async () => {
    const response = await api.get('/company-countries');
    return response.data;
  },
};

export const travelPackageService = {
  getPackages: async (params = {}) => {
    const response = await api.get('/travel-packages', { params });
    return response.data;
  },
  getPackage: async (slug, params = {}) => {
    const response = await api.get(`/travel-packages/${slug}`, { params });
    return response.data;
  },
};