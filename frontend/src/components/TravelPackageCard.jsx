import React from 'react';
import { Link } from 'react-router-dom';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { MdCalendarToday, MdPlace, MdPeople } from 'react-icons/md';

export default function TravelPackageCard({ pkg }) {
  const r = useRuta();
  const idioma = useIdioma();
  const cover = pkg.image || 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';
  return (
    <article className="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group relative">
      <div className="relative aspect-16/10 w-full overflow-hidden bg-slate-100">
        <img src={cover} alt={pkg.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
        {pkg.duration && (
          <span className="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold bg-slate-900/80 text-white backdrop-blur-md shadow-sm flex items-center gap-1">
            <MdCalendarToday className="w-3.5 h-3.5" /> {pkg.duration}
          </span>
        )}
      </div>
      <div className="p-5 flex-1 flex flex-col">
        <div className="flex items-center gap-1 text-xs font-semibold text-slate-500 mb-1.5">
          <MdPlace className="w-3.5 h-3.5 text-teal-600 shrink-0" />
          <span>{pkg.city}{pkg.country ? `, ${pkg.country}` : ''}</span>
        </div>
        <h3 className="font-bold text-slate-900 text-lg leading-snug line-clamp-1 group-hover:text-teal-600 transition-colors">
          <Link to={r(`/paquetes/${pkg.slug}`)} className="after:absolute after:inset-0 after:content-[''] focus-visible:outline-2 focus-visible:outline-teal-600 focus-visible:outline-offset-2">
            {pkg.name}
          </Link>
        </h3>
        {pkg.presentation_text && <p className="text-xs text-slate-500 line-clamp-2 mt-1.5">{pkg.presentation_text}</p>}
        <div className="mt-3 flex items-center gap-3 text-xs text-slate-500">
          <span className="inline-flex items-center gap-1"><MdPeople className="w-3.5 h-3.5" /> {pkg.min_people}-{pkg.max_people}</span>
        </div>
        <div className="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
          <div>
            <span className="text-[11px] font-medium text-slate-400 block">Desde</span>
            <div className="flex items-baseline gap-1">
              <span className="text-xl font-extrabold text-slate-900">{pkg.starting_price?.toLocaleString('es-ES')} {pkg.currency}</span>
            </div>
          </div>
            <span className="relative z-10 inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#154495] group-hover:bg-[#0f3a7a] text-white font-bold text-xs shadow-md transition-all">Ver paquete</span>
        </div>
      </div>
    </article>
  );
}
