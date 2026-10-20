import React from 'react';
import {
  MdFilterList,
  MdRestartAlt,
  MdAutoAwesome,
  MdBathtub,
  MdMeetingRoom,
  MdAttachMoney,
  MdStar,
  MdCheck
} from 'react-icons/md';
import { useIdioma } from '../hooks/useIdioma';
import { copy } from '../seo/copys';
import { formatearMoneda } from '../utils/moneda';

export default function FilterSidebar({
  filters,
  onFilterChange,
  onResetFilters,
  moneda = null,
  mostrarPrecio = false
}) {
  const idioma = useIdioma();
  const textos = copy(idioma);

  // Las etiquetas salen del copy, pero el id del amenity se mantiene porque es
  // el valor que viaja a la API y el que comparan los filtros ya activos.
  const amenitiesList = [
    { id: 'wifi' },
    { id: 'kitchen' },
    { id: 'pool' },
    { id: 'parking' },
    { id: 'pets' },
    { id: 'breakfast' },
    { id: 'spa' },
    { id: 'ac' },
  ];

  const handleAmenityToggle = (amenityId) => {
    const current = filters.amenities || [];
    const updated = current.includes(amenityId)
      ? current.filter((item) => item !== amenityId)
      : [...current, amenityId];
    onFilterChange('amenities', updated);
  };

  return (
    <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
      
      {/* Header */}
      <div className="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
        <div className="flex items-center gap-2">
          <MdFilterList className="w-4 h-4 text-rose-600" />
          <h3 className="font-bold text-slate-900 text-sm">{textos.filtros.titulo}</h3>
        </div>
        <button
          type="button"
          onClick={onResetFilters}
          className="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 cursor-pointer transition-colors"
        >
          <MdRestartAlt className="w-3 h-3" />
          <span>{textos.filtros.restablecer}</span>
        </button>
      </div>

      <div className="space-y-6">
        
        {/* 1. Price Range
            Solo aparece con un país elegido. Cada precio está en la moneda de su
            país, así que un rango de 20 a 300 no significa lo mismo en euros que
            en bolivianos, y el backend ignora este filtro si no llega `country`.
            Fuera del caso el control estorba: parece filtrar y no filtra. */}
        {mostrarPrecio && (
          <div>
            <label className="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-2 flex items-center gap-1.5">
              <MdAttachMoney className="w-3.5 h-3.5 text-slate-400" />
              {textos.filtros.precioMaximo}
            </label>
            <div className="flex items-center justify-between text-xs text-slate-500 font-medium mb-1">
              <span>{formatearMoneda(20, moneda, idioma)}</span>
              <span className="font-bold text-rose-600">
                {formatearMoneda(filters.max_price || 300, moneda, idioma)}
              </span>
              <span>{formatearMoneda(300, moneda, idioma)}+</span>
            </div>
            <input
              type="range"
              min="20"
              max="300"
              step="10"
              value={filters.max_price || 300}
              onChange={(e) => onFilterChange('max_price', Number(e.target.value))}
              className="w-full accent-rose-600 cursor-pointer h-1.5 bg-slate-200 rounded-lg appearance-none"
            />
          </div>
        )}

        {/* 2. Space / Unit Type */}
        <div>
          <label className="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-2.5 flex items-center gap-1.5">
            <MdMeetingRoom className="w-3.5 h-3.5 text-slate-400" />
            {textos.filtros.tipoAlojamiento}
          </label>
          <div className="grid grid-cols-1 gap-1.5">
            {[
              { value: '', label: textos.filtros.cualquiera },
              { value: 'entire_place', label: textos.filtros.lugarEntero },
              { value: 'private_room', label: textos.filtros.habitacionPrivada },
              { value: 'shared_room', label: textos.filtros.literaCompartida },
            ].map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => onFilterChange('unit_type', option.value)}
                className={`w-full text-left px-3 py-2 rounded-xl text-xs font-semibold transition-all ${
                  filters.unit_type === option.value
                    ? 'bg-rose-50 text-rose-700 border border-rose-200'
                    : 'text-slate-600 hover:bg-slate-50 border border-transparent'
                }`}
              >
                {option.label}
              </button>
            ))}
          </div>
        </div>

        {/* 3. Bathroom Type (Special for Hostels & Hotels) */}
        <div>
          <label className="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-2.5 flex items-center gap-1.5">
            <MdBathtub className="w-3.5 h-3.5 text-slate-400" />
            {textos.filtros.tipoBano}
          </label>
          <div className="flex flex-wrap gap-2">
            {[
              { value: '', label: textos.filtros.todos },
              { value: 'private', label: textos.filtros.privado },
              { value: 'shared', label: textos.filtros.compartido },
            ].map((opt) => (
              <button
                key={opt.value}
                type="button"
                onClick={() => onFilterChange('bathroom_type', opt.value)}
                className={`flex-1 min-w-[4.5rem] py-1.5 px-2 rounded-xl text-xs font-semibold text-center transition-all ${
                  filters.bathroom_type === opt.value
                    ? 'bg-slate-900 text-white shadow-xs'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                }`}
              >
                {opt.label}
              </button>
            ))}
          </div>
        </div>

        {/* 4. Minimum Guest Rating */}
        <div>
          <label className="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-2 flex items-center gap-1.5">
            <MdStar className="w-3.5 h-3.5 text-amber-500 fill-amber-500" />
            {textos.filtros.puntuacion}
          </label>
          <div className="flex flex-wrap gap-2">
            {[
              { value: '', label: textos.filtros.cualquiera },
              { value: '9.0', label: `9.0+ ${textos.filtros.fantastic}` },
              { value: '9.5', label: `9.5+ ${textos.filtros.excepcional}` },
            ].map((rating) => (
              <button
                key={rating.value}
                type="button"
                onClick={() => onFilterChange('min_rating', rating.value)}
                className={`flex-1 min-w-[4.5rem] py-1.5 px-1 rounded-xl text-xs font-semibold text-center transition-all ${
                  filters.min_rating === rating.value
                    ? 'bg-amber-500 text-white shadow-xs'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                }`}
              >
                {rating.label}
              </button>
            ))}
          </div>
        </div>

        {/* 5. Key Amenities */}
        <div>
          <label className="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-2 flex items-center gap-1.5">
            <MdAutoAwesome className="w-3.5 h-3.5 text-rose-500" />
            {textos.filtros.amenidades}
          </label>
          <div className="space-y-1.5">
            {amenitiesList.map((item) => {
              const isChecked = (filters.amenities || []).includes(item.id);
              return (
                <label
                  key={item.id}
                  className="flex items-center gap-2.5 text-xs font-medium text-slate-600 hover:text-slate-900 cursor-pointer select-none py-0.5"
                >
                  <div className={`w-4 h-4 rounded-md border flex items-center justify-center transition-colors ${
                    isChecked 
                      ? 'bg-rose-600 border-rose-600 text-white' 
                      : 'border-slate-300 bg-white hover:border-slate-400'
                  }`}>
                    {isChecked && <MdCheck className="w-3 h-3 stroke-[3]" />}
                  </div>
                  <input
                    type="checkbox"
                    className="sr-only"
                    checked={isChecked}
                    onChange={() => handleAmenityToggle(item.id)}
                  />
                  <span>{textos.filtros.amenidadesLista[item.id]}</span>
                </label>
              );
            })}
          </div>
        </div>

      </div>

    </div>
  );
}
