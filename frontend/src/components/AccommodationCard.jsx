import React from 'react';
import { Link } from 'react-router-dom';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { copy } from '../seo/copys';
import { formatearMoneda } from '../utils/moneda';
import {
  MdStar,
  MdPlace,
  MdWifi,
  MdWaves,
  MdRestaurant,
  MdDirectionsCar,
  MdPets,
  MdLocalCafe,
  MdArrowForward,
  MdLocalFireDepartment,
} from 'react-icons/md';

/**
 * Solo el icono es fijo; la etiqueta sale del copy del idioma, porque si se
 * dejara en español la tarjeta en inglés mostraría "Piscina" junto a "Pool".
 */
const amenityIcons = {
  wifi: MdWifi,
  pool: MdWaves,
  kitchen: MdRestaurant,
  parking: MdDirectionsCar,
  pets: MdPets,
  breakfast: MdLocalCafe,
  fireplace: MdLocalFireDepartment,
};

export default function AccommodationCard({ accommodation }) {
  const idioma = useIdioma();
  const r = useRuta();
  const textos = copy(idioma);

  const coverImage =
    accommodation.images && accommodation.images.length > 0
      ? accommodation.images[0]
      : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80';

  const typeName = accommodation.accommodation_type?.name || textos.alojamiento.generico;
  const typeSlug = accommodation.accommodation_type?.slug;

  // Badge color based on property type
  const typeBadgeColors = {
    hotel: 'bg-indigo-600/90 text-white',
    hostal: 'bg-emerald-600/90 text-white',
    cabana: 'bg-amber-700/90 text-white',
    apartamento: 'bg-cyan-700/90 text-white',
    glamping: 'bg-purple-700/90 text-white',
  };

  /*
   * Toda la tarjeta es un enlace a la URL descriptiva del alojamiento. Antes era
   * un <div onClick> que abría un modal: Google no podía seguirlo porque no
   * había ningún <a href>. El texto visible del enlace es el nombre de la
   * propiedad, así que el anclaje de texto describe el destino.
   */
  return (
    <article className="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group relative">

      {/* Image Container with Badges */}
      <div className="relative aspect-16/10 w-full overflow-hidden bg-slate-100">
        <img
          src={coverImage}
          alt={accommodation.name}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          loading="lazy"
        />

        {/* Category Pill Badge */}
        <div className="absolute top-3 left-3 flex items-center gap-1.5">
          <span
            className={`px-3 py-1 rounded-full text-xs font-bold tracking-wide backdrop-blur-md shadow-sm ${
              typeBadgeColors[typeSlug] || 'bg-slate-900/80 text-white'
            }`}
          >
            {typeName}
          </span>

          {accommodation.star_rating && (
            <span className="px-2 py-1 bg-amber-400 text-amber-950 font-black text-xs rounded-full shadow-xs flex items-center gap-0.5">
              <span>{accommodation.star_rating}</span>
              <MdStar className="w-3 h-3 fill-amber-950" />
            </span>
          )}
        </div>

        {/* Rating Badge */}
        <div className="absolute top-3 right-3 bg-white/95 backdrop-blur-md px-2.5 py-1 rounded-xl shadow-md flex items-center gap-1.5 border border-slate-100">
          <MdStar className="w-3.5 h-3.5 text-amber-500 fill-amber-500" />
          <span className="text-xs font-extrabold text-slate-900">
            {Number(accommodation.guest_rating).toFixed(1)}
          </span>
          <span className="text-[10px] text-slate-400">({accommodation.reviews_count})</span>
        </div>
      </div>

      {/* Content Section */}
      <div className="p-5 flex-1 flex flex-col justify-between">
        <div>
          {/* Location */}
          <div className="flex items-center gap-1 text-xs font-semibold text-slate-500 mb-1.5">
            <MdPlace className="w-3.5 h-3.5 text-rose-500 shrink-0" />
            <span>
              {accommodation.city}, {accommodation.country}
            </span>
          </div>

          <h3 className="font-bold text-slate-900 text-lg leading-snug line-clamp-1 group-hover:text-rose-600 transition-colors">
            {/* Enlaced con after:absolute para que todo el bloque sea pulsable */}
            <Link
              to={r(`/alojamiento/${accommodation.slug}`)}
              className="after:absolute after:inset-0 after:content-[''] focus-visible:outline-2 focus-visible:outline-rose-600 focus-visible:outline-offset-2"
            >
              {accommodation.name}
            </Link>
          </h3>

          <p className="text-xs text-slate-500 line-clamp-2 mt-1.5 leading-relaxed">
            {accommodation.headline || accommodation.description}
          </p>

          {/* Amenities Pills Preview */}
          {accommodation.amenities && (
            <div className="flex flex-wrap gap-1.5 mt-3.5">
              {accommodation.amenities.slice(0, 4).map((amenity) => {
                const Icon = amenityIcons[amenity];
                const label = textos.amenidadesTarjeta[amenity];
                if (!Icon || !label) return null;
                return (
                  <span
                    key={amenity}
                    className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-medium"
                  >
                    <Icon className="w-3 h-3 text-slate-500" />
                    <span>{label}</span>
                  </span>
                );
              })}
            </div>
          )}
        </div>

        {/* Price & Action Footer */}
        <div className="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
          <div>
            <span className="text-[11px] font-medium text-slate-400 block">
              {textos.alojamiento.precioDesdeCorto}
            </span>
            <div className="flex items-baseline gap-1">
              <span className="text-xl font-extrabold text-slate-900">
                {formatearMoneda(accommodation.starting_price, accommodation.currency, idioma)}
              </span>
              <span className="text-xs text-slate-500 font-medium">
                {textos.alojamiento.porNocheEtiqueta}
              </span>
            </div>
          </div>

          {/* relative z-10 para que quede por encima del ::after del enlace */}
          <Link
            to={r(`/alojamiento/${accommodation.slug}`)}
            className="relative z-10 inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#154495] group-hover:bg-[#0f3a7a] text-white font-bold text-xs shadow-md shadow-[#154495]/20 group-hover:shadow-[#154495]/30 transition-all duration-200"
          >
            <span>{textos.alojamiento.verUnidades}</span>
            <MdArrowForward className="w-3.5 h-3.5" />
          </Link>
        </div>
      </div>
    </article>
  );
}
