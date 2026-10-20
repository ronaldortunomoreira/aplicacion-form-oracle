import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import Seo from '../components/Seo';
import { travelPackageService } from '../services/api';
import { useIdioma, useRuta } from '../hooks/useIdioma';
import { copy } from '../seo/copys';
import { MdChevronRight, MdAccessTime, MdPeople, MdPlace } from 'react-icons/md';
import GaleriaFotos from '../components/GaleriaFotos';
import TravelPackageBookingModal from '../components/TravelPackageBookingModal';

export default function TravelPackagePage() {
  const { slug } = useParams();
  const r = useRuta();
  const navigate = useNavigate();
  const idioma = useIdioma();
  const textos = copy(idioma);
  const [pkg, setPkg] = useState(null);
  const [estado, setEstado] = useState('cargando');
  const [showBookingModal, setShowBookingModal] = useState(false);

  useEffect(() => {
    let v = true;
    async function cargar() {
      try {
        const res = await travelPackageService.getPackage(slug);
        if (v) { setPkg(res.data); setEstado('listo'); }
      } catch {
        if (v) setEstado('error');
      }
    }
    cargar();
    return () => { v = false; };
  }, [slug, idioma]);

  if (!pkg) return null;
  return (
    <main className="flex-1 pb-16">
      <Seo titulo={pkg.name} descripcion={pkg.presentation_text || pkg.description} />
      <nav className="max-w-6xl mx-auto px-4 sm:px-6 pt-6 text-xs text-slate-500 flex items-center gap-1.5">
        <Link to={r('/')} className="hover:text-teal-600">Inicio</Link><MdChevronRight className="w-3.5 h-3.5" />
        <Link to={r('/paquetes')} className="hover:text-teal-600">Paquetes</Link><MdChevronRight className="w-3.5 h-3.5" />
        <span className="min-w-0 font-semibold text-slate-700 truncate">{pkg.name}</span>
      </nav>
      <section className="max-w-6xl mx-auto px-4 sm:px-6 pt-6">
        {/* Galería de fotos del paquete. Las fotos se colocan en
            public/images/paquetes/<slug>/ y se muestran igual que en los
            alojamientos (mosaico en escritorio, carrusel en móvil). */}
        {pkg.images && pkg.images.length > 0 && (
          <div className="overflow-hidden rounded-3xl shadow-lg shadow-teal-600/20 mb-6">
            <GaleriaFotos images={pkg.images} alt={pkg.name} textos={textos.detalle.galeria} acento="border-teal-500" />
          </div>
        )}

        <div className="relative overflow-hidden rounded-3xl shadow-lg shadow-teal-600/20">
          {/* La portada se usa como fondo solo si no hay galería, para no repetirla. */}
          {!(pkg.images && pkg.images.length > 0) && pkg.image && <img src={pkg.image} alt={pkg.name} className="absolute inset-0 w-full h-full object-cover" />}
          <div className="relative bg-gradient-to-br from-slate-900/85 to-teal-900/70 p-6 sm:p-10 text-white">
            <h1 className="text-3xl sm:text-4xl font-extrabold">{pkg.name}</h1>
            <div className="mt-3 flex flex-wrap gap-3 text-xs font-semibold">
              <span className="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm px-3 py-1.5 rounded-full"><MdPlace className="w-3.5 h-3.5" /> {pkg.city}{pkg.country?', '+pkg.country:''}</span>
              {pkg.duration && <span className="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm px-3 py-1.5 rounded-full"><MdAccessTime className="w-3.5 h-3.5" /> {pkg.duration}</span>}
              <span className="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm px-3 py-1.5 rounded-full"><MdPeople className="w-3.5 h-3.5" /> {pkg.min_people}-{pkg.max_people}</span>
            </div>
            {pkg.presentation_text && <p className="mt-4 max-w-3xl text-emerald-50/90">{pkg.presentation_text}</p>}
          </div>
        </div>
      </section>
      <section className="max-w-6xl mx-auto px-4 sm:px-6 mt-8 grid gap-6 md:grid-cols-3">
        <div className="md:col-span-2 space-y-6">
          {pkg.description && <div className="bg-white rounded-2xl p-6 border border-slate-200"><h2 className="font-bold mb-2">Descripción</h2><p className="text-slate-600 text-sm leading-relaxed">{pkg.description}</p></div>}
          {pkg.includes?.length>0 && <div className="bg-white rounded-2xl p-6 border border-slate-200"><h2 className="font-bold mb-2">Incluye</h2><ul className="list-disc pl-5 text-sm text-slate-600 space-y-1">{pkg.includes.map((i,k)=><li key={k}>{i}</li>)}</ul></div>}
          {pkg.excludes?.length>0 && <div className="bg-white rounded-2xl p-6 border border-slate-200"><h2 className="font-bold mb-2">No incluye</h2><ul className="list-disc pl-5 text-sm text-slate-600 space-y-1">{pkg.excludes.map((i,k)=><li key={k}>{i}</li>)}</ul></div>}
          {pkg.itinerary?.length>0 && <div className="bg-white rounded-2xl p-6 border border-slate-200"><h2 className="font-bold mb-2">Itinerario</h2><ol className="space-y-2">{pkg.itinerary.map((it,k)=><li key={k} className="flex gap-3"><span className="font-semibold text-teal-600">{it.hora}</span><span className="text-slate-600 text-sm">{it.actividad}</span></li>)}</ol></div>}
        </div>
        <div className="bg-white rounded-2xl p-6 border border-slate-200 h-fit md:sticky md:top-20">
          <p className="text-xs text-slate-500">Desde</p>
          <p className="text-2xl font-extrabold">{pkg.starting_price?.toLocaleString('es-ES')} {pkg.currency}</p>
          <button 
            onClick={() => setShowBookingModal(true)}
            className="mt-4 w-full px-4 py-2.5 rounded-xl bg-teal-600 text-white font-bold hover:bg-teal-700 transition-colors"
          >
            Reservar paquete
          </button>
        </div>
      </section>
      {showBookingModal && (
        <TravelPackageBookingModal
          travelPackage={pkg}
          onClose={() => setShowBookingModal(false)}
          onViewMyBookings={(code, email) => {
            setShowBookingModal(false);
            const params = new URLSearchParams();
            if (code) params.set('codigo', code);
            if (email) params.set('correo', email);
            const query = params.toString();
            navigate(query ? `${r('/mis-reservas')}?${query}` : r('/mis-reservas'));
          }}
        />
      )}
    </main>
  );
}
