import React from 'react';
import {
  MdPlace,
  MdStar,
  MdPeople,
  MdKingBed,
  MdAccessTime,
  MdGppBad,
} from 'react-icons/md';
import { useIdioma } from '../hooks/useIdioma';
import { copy } from '../seo/copys';
import { formatearMoneda } from '../utils/moneda';
import GaleriaFotos from './GaleriaFotos';
import MapaGoogle from './MapaGoogle';

/**
 * Contenido del detalle de un alojamiento, sin la capa de modal.
 *
 * Lo comparten el modal de la home y la página `/alojamiento/:slug`, para que
 * ambos muestren exactamente la misma información y no diverjan con el tiempo.
 *
 * `mostrarMapa` separa lo que solo tiene sentido en la página propia del
 * alojamiento: el modal de la home se abre encima de los resultados y el mapa
 * es un bloque de píxeles de alto que empuja el precio y el botón de reservar
 * fuera de la vista sin aportar nada, porque el visitante ya tiene la lista
 * delante.
 */
export default function AccommodationDetail({ accommodation, onBookUnit, mostrarMapa = false }) {
  const idioma = useIdioma();
  const textos = copy(idioma);

  if (!accommodation) return null;

  const images = accommodation.images || [];
  const typeName = accommodation.accommodation_type?.name || textos.detalle.tipoFallback;

  const unitTypeLabels = {
    entire_place: {
      label: textos.detalle.tipoLugarCompleto,
      color: 'bg-amber-100 text-amber-800',
    },
    private_room: {
      label: textos.detalle.tipoHabitacionPrivada,
      color: 'bg-indigo-100 text-indigo-800',
    },
    shared_room: {
      label: textos.detalle.tipoDormitorio,
      color: 'bg-emerald-100 text-emerald-800',
    },
  };

  return (
    <div>
      {/* Galería */}
      <GaleriaFotos
        images={images}
        alt={accommodation.name}
        textos={textos.detalle.galeria}
      />

      {/* Detalle */}
      <div className="p-6 sm:p-8 space-y-8">

        {/* Cabecera */}
        <div>
          <div className="flex flex-wrap items-center gap-2 mb-2">
            <span className="px-3 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-bold">
              {typeName}
            </span>
            {accommodation.star_rating && (
              <span className="px-2.5 py-1 bg-amber-100 text-amber-900 rounded-full text-xs font-bold flex items-center gap-1">
                <MdStar className="w-3.5 h-3.5 fill-amber-500 text-amber-500" />
                {textos.detalle.estrellas({ total: accommodation.star_rating })}
              </span>
            )}
            <div className="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 rounded-full text-xs font-bold text-slate-800">
              <MdStar className="w-3.5 h-3.5 fill-amber-500 text-amber-500" />
              <span>{Number(accommodation.guest_rating).toFixed(1)} / 10</span>
              <span className="text-slate-400 font-normal">
                {textos.detalle.opiniones({ total: accommodation.reviews_count })}
              </span>
            </div>
          </div>

          <h2 className="text-2xl sm:text-3xl font-extrabold text-slate-900">
            {accommodation.name}
          </h2>

          <div className="flex items-center gap-1.5 text-sm font-medium text-slate-500 mt-2">
            <MdPlace className="w-4 h-4 text-rose-500 shrink-0" />
            <span>
              {accommodation.address}, {accommodation.city}, {accommodation.country}
            </span>
          </div>
        </div>

        {/* Descripción */}
        <div>
          <h3 className="text-base font-bold text-slate-900 mb-2">{textos.detalle.acercaDe}</h3>
          <p className="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
            {accommodation.description}
          </p>
        </div>

        {/* Horarios y políticas */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100 text-sm">
          <div className="flex items-start gap-3">
            <MdAccessTime className="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
            <div>
              <span className="font-bold text-slate-900 block">{textos.detalle.horarios}</span>
              <p className="text-xs text-slate-600 mt-0.5">
                {textos.detalle.llegada}{' '}
                <span className="font-semibold">{accommodation.check_in_time}</span>
              </p>
              <p className="text-xs text-slate-600">
                {textos.detalle.salida}{' '}
                <span className="font-semibold">{accommodation.check_out_time}</span>
              </p>
            </div>
          </div>

          <div className="flex items-start gap-3">
            <MdGppBad className="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
            <div>
              <span className="font-bold text-slate-900 block">{textos.detalle.politicas}</span>
              {accommodation.policies ? (
                <div className="text-xs text-slate-600 space-y-0.5 mt-0.5">
                  {Object.entries(accommodation.policies).map(([key, value]) => (
                    <p key={key}>• {value}</p>
                  ))}
                </div>
              ) : (
                <p className="text-xs text-slate-600">
                  {textos.detalle.politicaPorDefecto}
                </p>
              )}
            </div>
          </div>
        </div>

        {/* Mapa, solo en la página del alojamiento */}
        {mostrarMapa && (
          <MapaGoogle accommodation={accommodation} textos={textos} idioma={idioma} />
        )}

        {/* Unidades disponibles */}
        <div>
          <div className="mb-4">
            <h3 className="text-xl font-bold text-slate-900">
              {textos.detalle.habitacionesTitulo}
            </h3>
            <p className="text-xs text-slate-500">
              {textos.detalle.habitacionesSubtitulo}
            </p>
          </div>

          <div className="space-y-4">
            {accommodation.room_units && accommodation.room_units.length > 0 ? (
              accommodation.room_units.map((unit) => {
                const typeBadge = unitTypeLabels[unit.unit_type] || {
                  label: unit.unit_type,
                  color: 'bg-slate-100 text-slate-800',
                };

                return (
                  <div
                    key={unit.id}
                    className="p-5 rounded-2xl border border-slate-200 hover:border-rose-300 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-5 bg-white"
                  >
                    <div className="space-y-2 flex-1">
                      <div className="flex flex-wrap items-center gap-2">
                        <span
                          className={`px-2.5 py-0.5 rounded-full text-xs font-bold ${typeBadge.color}`}
                        >
                          {typeBadge.label}
                        </span>

                        <span
                          className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                            unit.bathroom_type === 'private'
                              ? 'bg-blue-50 text-blue-700'
                              : 'bg-amber-50 text-amber-800'
                          }`}
                        >
                          {unit.bathroom_type === 'private'
                            ? textos.detalle.banoPrivado
                            : textos.detalle.banoCompartido}
                        </span>
                      </div>

                      <h4 className="font-extrabold text-slate-900 text-base">{unit.name}</h4>

                      <div className="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                        <span className="flex items-center gap-1 font-medium">
                          <MdPeople className="w-3.5 h-3.5 text-slate-400" />
                          {textos.detalle.hastaHuespedes({
                            total: unit.capacity_adults + unit.capacity_children,
                          })}
                        </span>

                        {unit.bed_distribution && (
                          <span className="flex items-center gap-1 font-medium">
                            <MdKingBed className="w-3.5 h-3.5 text-slate-400" />
                            {Array.isArray(unit.bed_distribution)
                              ? unit.bed_distribution.join(', ')
                              : textos.detalle.camas}
                          </span>
                        )}
                      </div>

                      {unit.amenities && (
                        <div className="flex flex-wrap gap-1.5 pt-1">
                          {unit.amenities.map((amenity, i) => (
                            <span
                              key={i}
                              className="text-[11px] bg-slate-50 text-slate-600 px-2 py-0.5 rounded-md border border-slate-100"
                            >
                              ✓ {amenity}
                            </span>
                          ))}
                        </div>
                      )}
                    </div>

                    <div className="md:text-right shrink-0 flex md:flex-col justify-between items-center md:items-end gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
                      <div>
                        <div className="flex items-baseline gap-1 md:justify-end">
                          <span className="text-2xl font-black text-slate-900">
                            {formatearMoneda(unit.base_price_per_night, accommodation.currency, idioma)}
                          </span>
                          <span className="text-xs text-slate-500 font-medium">
                            {textos.detalle.porNocheEtiqueta}
                          </span>
                        </div>
                        {unit.cleaning_fee > 0 && (
                          <span className="text-[11px] text-slate-400 block">
                            {textos.detalle.limpiezaUnica({
                              precio: formatearMoneda(unit.cleaning_fee, accommodation.currency, idioma),
                            })}
                          </span>
                        )}
                      </div>

                      <button
                        type="button"
                        onClick={() => onBookUnit(unit)}
                        className="px-5 py-2.5 bg-[#154495] hover:bg-[#0f3a7a] active:scale-95 text-white font-bold text-xs rounded-xl shadow-md shadow-[#154495]/20 transition-all cursor-pointer"
                      >
                        {textos.detalle.reservarUnidad}
                      </button>
                    </div>
                  </div>
                );
              })
            ) : (
              <p className="text-xs text-slate-500 italic">
                {textos.detalle.sinHabitaciones}
              </p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
