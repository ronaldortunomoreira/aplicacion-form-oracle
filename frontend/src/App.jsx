import React from 'react';
import { Navigate, Route, Routes, useLocation } from 'react-router-dom';
import Navbar from './components/Navbar';
import HomePage from './pages/HomePage';
import CountriesPage from './pages/CountriesPage';
import CountryPage from './pages/CountryPage';
import DestinationsPage from './pages/DestinationsPage';
import DestinationPage from './pages/DestinationPage';
import TravelPackagesPage from './pages/TravelPackagesPage';
import TravelPackagePage from './pages/TravelPackagePage';
import AccommodationPage from './pages/AccommodationPage';
import MyBookingsPage from './pages/MyBookingsPage';
import RegisterCompanyPage from './pages/RegisterCompanyPage';
import NotFoundPage from './pages/NotFoundPage';
import { ProveedorReserva } from './contexto/ReservaContexto';

/**
 * Contenedor de rutas.
 *
 * Cada vista es una ruta real, para que un buscador pueda indexar los
 * alojamientos y cada página tenga una URL compartible. El sitio es monolingüe
 * (español), así que las URLs viven en la raíz y no llevan prefijo de idioma.
 */
const RUTAS = [
  { path: '/', element: <HomePage /> },
  { path: '/paises', element: <CountriesPage /> },
  { path: '/paises/:slug', element: <CountryPage /> },
  { path: '/destinos', element: <DestinationsPage /> },
  { path: '/destinos/:slug', element: <DestinationPage /> },
  { path: '/paquetes', element: <TravelPackagesPage /> },
  { path: '/paquetes/:slug', element: <TravelPackagePage /> },
  { path: '/alojamiento/:slug', element: <AccommodationPage /> },
  { path: '/mis-reservas', element: <MyBookingsPage /> },
  { path: '/registro-hotel', element: <RegisterCompanyPage /> },
];

/**
 * Redirección de las URLs antiguas con prefijo de idioma.
 *
 * `/es/paises/espana` y `/en/paises/espana` pasan a `/paises/espana`, que es la
 * única forma canónica ahora que el sitio no es multilingüe. Se conservan para
 * no romper enlaces ya publicados.
 */
function RedireccionHeredada() {
  const { pathname, search } = useLocation();
  const sinPrefijo = pathname.replace(/^\/(es|en)(?=\/|$)/, '') || '/';

  return <Navigate to={`${sinPrefijo}${search}`} replace />;
}

export default function App() {
  return (
    <ProveedorReserva>
      <div className="min-h-screen bg-slate-50 flex flex-col font-sans">
        <Navbar />

        <Routes>
          {RUTAS.map(({ path, element }) => (
            <Route key={path} path={path} element={element} />
          ))}

          <Route path="/es/*" element={<RedireccionHeredada />} />
          <Route path="/en/*" element={<RedireccionHeredada />} />

          <Route path="*" element={<NotFoundPage />} />
        </Routes>

        <footer className="bg-white border-t border-slate-200 py-8 mt-auto">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500 space-y-2">
            <p>
              Plataforma integral de gestión de reservas para hoteles, hostales, cabañas,
              apartamentos y alojamientos turísticos.
            </p>
          </div>
        </footer>
      </div>
    </ProveedorReserva>
  );
}
