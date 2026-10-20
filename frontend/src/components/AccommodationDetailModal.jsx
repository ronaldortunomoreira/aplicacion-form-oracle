import React from 'react';
import { MdClose } from 'react-icons/md';
import AccommodationDetail from './AccommodationDetail';

/**
 * Modal de detalle. El contenido vive en <AccommodationDetail>, que también usa
 * la página `/alojamiento/:slug`; aquí solo se añade la capa superpuesta.
 */
export default function AccommodationDetailModal({ accommodation, onClose, onBookUnit }) {
  if (!accommodation) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-200 overflow-y-auto">
      <div
        className="bg-white rounded-3xl max-w-4xl w-full max-h-[92dvh] overflow-y-auto shadow-2xl border border-slate-100 relative my-auto scrollbar-thin"
        onClick={(e) => e.stopPropagation()}
      >
        <button
          onClick={onClose}
          aria-label="Cerrar detalle del alojamiento"
          className="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-white/90 backdrop-blur-md shadow-md hover:bg-white text-slate-700 hover:text-slate-900 flex items-center justify-center transition-all cursor-pointer"
        >
          <MdClose className="w-5 h-5" />
        </button>

        <div className="rounded-t-3xl overflow-hidden">
          <AccommodationDetail
            accommodation={accommodation}
            onBookUnit={onBookUnit}
          />
        </div>
      </div>
    </div>
  );
}
