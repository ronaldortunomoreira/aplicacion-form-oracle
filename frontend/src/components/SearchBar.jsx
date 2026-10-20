import React, { useState } from 'react';
import { MdPlace, MdCalendarToday, MdPeople, MdClose, MdSearch, MdCancel } from 'react-icons/md';
import { useIdioma } from '../hooks/useIdioma';
import { copy } from '../seo/copys';

export default function SearchBar({
  searchQuery,
  setSearchQuery,
  checkIn,
  setCheckIn,
  checkOut,
  setCheckOut,
  guests,
  setGuests,
  destinations = [],
  paisElegido = null,
  ciudadElegida = null,
  onSelectCity,
  onClearCity,
  onSearch,
  onReset
}) {
  const idioma = useIdioma();
  const textos = copy(idioma);
  const [showDestinations, setShowDestinations] = useState(false);

  // Today in YYYY-MM-DD
  const today = new Date().toISOString().split('T')[0];

  // Elegir un destino fija los dos pasos de la cascada, no solo el texto.
  //
  // El autocomplete viene filtrado por el país elegido, así que cada destino
  // arrastra su `country_slug`. Mandarlo permite que la ciudad se aplique como
  // filtro exacto en lugar de como texto libre: con `search` la búsqueda también
  // miraría el nombre del hotel y la dirección, y "Medellín" devolvería además
  // cualquier hotel cuya dirección contenga esa palabra.
  const handleDestinationSelect = (dest) => {
    setSearchQuery('');
    onSelectCity?.(dest.city, dest.country_slug);
    setShowDestinations(false);
  };

  const handleFormSubmit = (e) => {
    e.preventDefault();
    setShowDestinations(false);
    onSearch();
  };

  return (
    <div className="relative z-30 max-w-5xl mx-auto px-4 sm:px-6 -mt-8 mb-8">
      <form 
        onSubmit={handleFormSubmit}
        className="bg-white rounded-3xl p-3 sm:p-4 shadow-xl shadow-slate-200/60 border border-slate-200/80 flex flex-col lg:flex-row items-center gap-3"
      >
        
        {/* 1. Destination Input with Autocomplete
            El texto es búsqueda libre por nombre o dirección. Las ciudades son un
            filtro aparte, en cascada con el país, porque mezclarlas en el mismo
            campo hacía que "Cochabamba" se buscara también en el nombre del hotel
            y en la dirección, no solo como ciudad. */}
        <div className="relative w-full lg:flex-1">
          <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 ml-3">
            {textos.buscador.destino}
          </label>
          <div className="relative flex items-center">
            <MdPlace className="w-5 h-5 text-rose-500 absolute left-3.5 pointer-events-none" />
            <input
              type="text"
              placeholder={textos.buscador.placeholder}
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              onFocus={() => setShowDestinations(true)}
              className="w-full pl-11 pr-9 py-2.5 bg-slate-50 hover:bg-slate-100/80 focus:bg-white text-slate-900 placeholder:text-slate-400 font-medium text-sm rounded-2xl border border-transparent focus:border-rose-400 focus:outline-hidden transition-all"
            />
            {searchQuery && (
              <button 
                type="button" 
                onClick={() => setSearchQuery('')}
                className="absolute right-3 text-slate-400 hover:text-slate-600"
              >
                <MdClose className="w-4 h-4" />
              </button>
            )}
          </div>

          {/* Ciudad elegida: se muestra como etiqueta cerrada para que quede
              claro que el filtro está activo y se pueda quitar sin tocar el
              selector de país. */}
          {ciudadElegida && (
            <div className="mt-2 ml-3 flex items-center gap-1.5 inline-flex">
              <span className="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-full text-xs font-bold">
                <MdPlace className="w-3 h-3" />
                {ciudadElegida}
                <button
                  type="button"
                  onClick={onClearCity}
                  title={textos.buscador.limpiarCiudad}
                  className="hover:text-rose-900 cursor-pointer"
                >
                  <MdClose className="w-3 h-3" />
                </button>
              </span>
            </div>
          )}

          {/* Autocomplete dropdown */}
          {showDestinations && destinations.length > 0 && (
            <>
              <div 
                className="fixed inset-0 z-40" 
                onClick={() => setShowDestinations(false)}
              />
              <div className="absolute left-0 top-full mt-2 w-full sm:w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 z-50 animate-in fade-in zoom-in-95 duration-150">
                <div className="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                  {paisElegido ? textos.buscador.ciudadesDe : textos.buscador.populares}
                </div>
                {destinations.map((dest) => (
                  <button
                    key={`${dest.country_slug}-${dest.city}`}
                    type="button"
                    onClick={() => handleDestinationSelect(dest)}
                    aria-pressed={ciudadElegida === dest.city}
                    className={`w-full text-left flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
                      ciudadElegida === dest.city
                        ? 'bg-rose-50 text-rose-700'
                        : 'hover:bg-rose-50 text-slate-700 hover:text-rose-700'
                    }`}
                  >
                    <div className="flex items-center gap-2.5">
                      <MdPlace className="w-4 h-4 text-rose-500 shrink-0" />
                      <span>{dest.city}, <span className="text-slate-400">{dest.country}</span></span>
                    </div>
                    <span className="text-xs bg-slate-100 px-2 py-0.5 rounded-full text-slate-500">
                      {dest.count}
                    </span>
                  </button>
                ))}
              </div>
            </>
          )}

          {/* Sin país elegido el autocomplete sería la lista entera de ciudades de
              todos los países, que no es una cascada: se indica que hay que
              elegir antes un país. */}
          {showDestinations && destinations.length === 0 && (
            <>
              <div 
                className="fixed inset-0 z-40" 
                onClick={() => setShowDestinations(false)}
              />
              <div className="absolute left-0 top-full mt-2 w-full sm:w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 p-4 z-50 text-sm text-slate-500 animate-in fade-in zoom-in-95 duration-150">
                {textos.buscador.eligePaisPrimero}
              </div>
            </>
          )}
        </div>

        <div className="h-8 w-px bg-slate-200 hidden lg:block" />

        {/* 2. MdCheck-in Date */}
        <div className="w-full lg:w-44">
          <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 ml-3">
            {textos.buscador.llegada}
          </label>
          <div className="relative flex items-center">
            <MdCalendarToday className="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" />
            <input
              type="date"
              min={today}
              value={checkIn}
              onChange={(e) => {
                setCheckIn(e.target.value);
                if (checkOut && e.target.value >= checkOut) {
                  // automatically advance checkout by 1 day
                  const nextDay = new Date(e.target.value);
                  nextDay.setDate(nextDay.getDate() + 1);
                  setCheckOut(nextDay.toISOString().split('T')[0]);
                }
              }}
              className="w-full pl-10 pr-3 py-2.5 bg-slate-50 hover:bg-slate-100/80 focus:bg-white text-slate-800 font-medium text-sm rounded-2xl border border-transparent focus:border-rose-400 focus:outline-hidden transition-all"
            />
          </div>
        </div>

        {/* 3. MdCheck-out Date */}
        <div className="w-full lg:w-44">
          <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 ml-3">
            {textos.buscador.salida}
          </label>
          <div className="relative flex items-center">
            <MdCalendarToday className="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" />
            <input
              type="date"
              min={checkIn || today}
              value={checkOut}
              onChange={(e) => setCheckOut(e.target.value)}
              className="w-full pl-10 pr-3 py-2.5 bg-slate-50 hover:bg-slate-100/80 focus:bg-white text-slate-800 font-medium text-sm rounded-2xl border border-transparent focus:border-rose-400 focus:outline-hidden transition-all"
            />
          </div>
        </div>

        <div className="h-8 w-px bg-slate-200 hidden lg:block" />

        {/* 4. Guests count */}
        <div className="w-full lg:w-36">
          <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 ml-3">
            {textos.buscador.huespedes}
          </label>
          <div className="relative flex items-center">
            <MdPeople className="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" />
            <select
              value={guests}
              onChange={(e) => setGuests(Number(e.target.value))}
              className="w-full pl-10 pr-3 py-2.5 bg-slate-50 hover:bg-slate-100/80 focus:bg-white text-slate-800 font-medium text-sm rounded-2xl border border-transparent focus:border-rose-400 focus:outline-hidden transition-all appearance-none cursor-pointer"
            >
              {[1, 2, 3, 4, 5, 6, 8, 10].map((num) => (
                <option key={num} value={num}>
                  {num}{' '}
                  {num === 1 ? textos.buscador.huespedUno : textos.buscador.huespedesVarios}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* 5. MdSearch Action Button */}
        <div className="w-full lg:w-auto flex items-center gap-2">
          <button
            type="submit"
            className="w-full lg:w-auto px-6 py-3 bg-[#154495] hover:bg-[#0f3a7a] text-white font-bold text-sm rounded-2xl shadow-lg shadow-[#154495]/40 flex items-center justify-center gap-2 active:scale-98 transition-all duration-150 cursor-pointer"
          >
            <MdSearch className="w-4 h-4" />
            <span>{textos.buscador.buscar}</span>
          </button>

          {/* La ciudad cuenta como filtro activo: si no se incluye aquí, el botón de
              limpiar no aparecería con una ciudad elegida y el usuario no
              encontraría forma de quitarla desde la barra. */}
          {(searchQuery || checkIn || checkOut || guests > 1 || ciudadElegida) && (
            <button
              type="button"
              onClick={onReset}
              title={textos.buscador.limpiar}
              className="p-3 bg-slate-400 hover:bg-slate-500 text-white rounded-2xl transition-colors cursor-pointer"
            >
              <MdCancel className="w-4 h-4" />
            </button>
          )}
        </div>

      </form>
    </div>
  );
}
