import React, { useState } from 'react';
import { NavLink, Link } from 'react-router-dom';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { copy } from '../seo/copys';
import { MdAutoAwesome, MdMenu, MdClose, MdTravelExplore, MdInventory2, MdLandscape, MdEvent, MdBusiness } from 'react-icons/md';

/**
 * Navegación principal con enlaces reales.
 *
 * Cada destino es un `NavLink` a una URL concreta, no un botón que cambia un
 * estado: así Google puede seguir los enlaces, llega a cada ruta con una URL
 * compartible y el botón "atrás" del navegador funciona.
 *
 * Los destinos pasan por `useRuta`, que les añade el prefijo de idioma. Escribir
 * `to="/mis-reservas"` a mano funciona como alias, pero dejaría al visitante en
 * una URL sin idioma que no es la canónica.
 *
 * En móvil/tablet (< lg) la fila de enlaces se reemplaza por un botón de menú
 * que despliega las opciones apiladas: los cinco enlaces con etiqueta no caben
 * en un ancho de teléfono y desbordarían la página en horizontal.
 */
export default function Navbar() {
  const idioma = useIdioma();
  const r = useRuta();
  const textos = copy(idioma);
  const [abierto, setAbierto] = useState(false);

  const enlaces = [
    { to: '/', end: true, icon: MdTravelExplore, label: textos.navegacion.explorar },
    { to: '/paquetes', icon: MdInventory2, label: 'Paquetes' },
    { to: '/destinos', icon: MdLandscape, label: textos.navegacion.destinos },
    { to: '/mis-reservas', icon: MdEvent, label: textos.navegacion.misReservas, destacado: true },
    { to: '/registro-hotel', icon: MdBusiness, label: textos.navegacion.registrarHotel },
  ];

  // Estilos por enlace: el de "Mis Reservas" lleva fondo sólido para destacar.
  const claseEscritorio = (destacado) => ({ isActive }) =>
    `flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm transition-all duration-150 ${
      destacado
        ? isActive
          ? 'bg-gradient-to-b from-slate-300 to-slate-500 text-white shadow-md shadow-slate-400/30'
          : 'text-slate-700 bg-slate-100 hover:bg-slate-200'
        : isActive
          ? 'bg-slate-200 text-slate-800 shadow-xs'
          : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
    }`;

  const claseMovil = (destacado) => ({ isActive }) =>
    `flex items-center gap-3 px-3 py-3 rounded-xl font-semibold text-base transition-colors ${
      destacado
        ? isActive
          ? 'bg-gradient-to-b from-slate-300 to-slate-500 text-white'
          : 'bg-slate-100 text-slate-800 hover:bg-slate-200'
        : isActive
          ? 'bg-slate-200 text-slate-800'
          : 'text-slate-700 hover:bg-slate-100'
    }`;

  return (
    <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-18">

          <Link to={r('/')} className="flex items-center gap-3 select-none group">
            <div className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-rose-500 via-rose-600 to-amber-500 flex items-center justify-center text-white shadow-md shadow-rose-500/20 group-hover:scale-105 transition-transform duration-200">
              <MdAutoAwesome className="w-6 h-6 animate-pulse" />
            </div>
            <div>
              <div className="text-xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 via-rose-950 to-slate-800 bg-clip-text text-transparent">
                Hospeda<span className="text-rose-600">Ya</span>
              </div>
              <p className="text-xs text-slate-500 font-medium hidden sm:block">
                {textos.navegacion.subtitulo}
              </p>
            </div>
          </Link>

          {/* Escritorio: enlaces en línea. Solo desde lg, donde caben sin desbordar. */}
          <nav className="hidden lg:flex items-center gap-2">
            {enlaces.map(({ to, end, icon: Icono, label, destacado }) => (
              <NavLink key={to} to={r(to)} end={end} className={claseEscritorio(destacado)}>
                <Icono className="w-4 h-4" />
                <span>{label}</span>
              </NavLink>
            ))}
          </nav>

          {/* Móvil/tablet: botón que abre el menú apilado. */}
          <button
            type="button"
            onClick={() => setAbierto((v) => !v)}
            aria-label={abierto ? 'Cerrar menú' : 'Abrir menú'}
            aria-expanded={abierto}
            aria-controls="menu-principal-movil"
            className="lg:hidden w-11 h-11 rounded-2xl flex items-center justify-center text-white bg-gradient-to-b from-slate-300 to-slate-500 hover:from-slate-400 hover:to-slate-600 transition-colors cursor-pointer"
          >
            {abierto ? <MdClose className="w-5 h-5" /> : <MdMenu className="w-5 h-5" />}
          </button>

        </div>

        {/* Menú móvil desplegable. */}
        {abierto && (
          <nav
            id="menu-principal-movil"
            className="lg:hidden pb-4 pt-2 space-y-1"
          >
            {enlaces.map(({ to, end, icon: Icono, label, destacado }) => (
              <NavLink
                key={to}
                to={r(to)}
                end={end}
                onClick={() => setAbierto(false)}
                className={claseMovil(destacado)}
              >
                <Icono className="w-5 h-5" />
                <span>{label}</span>
              </NavLink>
            ))}
          </nav>
        )}
      </div>
    </header>
  );
}
