import React, { useState, useEffect } from 'react';
import { 
  MdSearch, 
  MdCalendarToday, 
  MdPlace, 
  MdCheckCircle, 
  MdCancel, 
  MdErrorOutline, 
  MdPrint, 
  MdRestartAlt,
  MdAutoAwesome,
  MdHelpOutline,
  MdAccessTime
} from 'react-icons/md';
import { reservationService } from '../services/api';
import { useIdioma } from '../hooks/useIdioma';
import { copy } from '../seo/copys';
import { formatearMoneda } from '../utils/moneda';

export default function MyBookings({ initialCode = '', initialEmail = '' }) {
  const idioma = useIdioma();
  const textos = copy(idioma);
  const [code, setCode] = useState(initialCode);
  const [email, setEmail] = useState(initialEmail);
  const [loading, setLoading] = useState(false);
  const [reservation, setReservation] = useState(null);
  const [errorMsg, setErrorMsg] = useState('');
  const [cancelModalOpen, setCancelModalOpen] = useState(false);
  const [cancelReason, setCancelReason] = useState('');
  const [cancelling, setCancelling] = useState(false);
  const [actionSuccess, setActionSuccess] = useState('');

  // Auto search if initial props are provided
  useEffect(() => {
    if (initialCode && initialEmail) {
      handleSearch();
    }
  }, [initialCode, initialEmail]);

  const handleSearch = async (e) => {
    if (e) e.preventDefault();
    setErrorMsg('');
    setActionSuccess('');

    if (!code || !email) {
      setErrorMsg('Por favor introduce tanto el código de reserva como tu correo electrónico.');
      return;
    }

    try {
      setLoading(true);
      const res = await reservationService.lookup(code, email);
      if (res.success) {
        setReservation(res.data);
      }
    } catch (err) {
      setReservation(null);
      setErrorMsg(err.response?.data?.message || 'No se encontró ninguna reserva activa con esos datos.');
    } finally {
      setLoading(false);
    }
  };

  const handleCancelReservation = async () => {
    try {
      setCancelling(true);
      const res = await reservationService.cancel(reservation.reservation_code, email, cancelReason);
      if (res.success) {
        setReservation(res.data);
        setCancelModalOpen(false);
        setActionSuccess(textos.reservas.cancelacionCorrecta);
      }
    } catch (err) {
      setErrorMsg(err.response?.data?.message || textos.reservas.errorCancelar);
    } finally {
      setCancelling(false);
    }
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 py-10 space-y-8 animate-in fade-in duration-200">
      
      {/* Title */}
      <div className="text-center space-y-2">
        <h1 className="text-3xl sm:text-4xl font-black text-slate-900">
          {textos.reservas.h1}
        </h1>
        <p className="text-sm text-slate-500 max-w-lg mx-auto">
          {textos.reservas.resumen}
        </p>
      </div>

      {/* Lookup Form */}
      <form 
        onSubmit={handleSearch}
        className="bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 border border-slate-200/80 space-y-4"
      >
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5 ml-1">
              Código de Reserva
            </label>
            <input
              type="text"
              placeholder={textos.reservas.codigoPlaceholder}
              value={code}
              onChange={(e) => setCode(e.target.value.toUpperCase())}
              className="w-full px-4 py-3 bg-slate-50 focus:bg-white text-slate-800 font-bold tracking-wider text-sm rounded-2xl border border-slate-200 focus:border-rose-500 focus:outline-hidden"
              required
            />
          </div>

          <div>
            <label className="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5 ml-1">
              Correo Electrónico
            </label>
            <input
              type="email"
              placeholder={textos.reservas.correoPlaceholder}
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-4 py-3 bg-slate-50 focus:bg-white text-slate-800 font-medium text-sm rounded-2xl border border-slate-200 focus:border-rose-500 focus:outline-hidden"
              required
            />
          </div>
        </div>

        <button
          type="submit"
          disabled={loading}
          className="w-full py-3.5 px-6 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm shadow-md shadow-slate-900/10 flex items-center justify-center gap-2 active:scale-98 transition-all cursor-pointer disabled:opacity-50"
        >
          <MdSearch className="w-4 h-4" />
          <span>{loading ? 'Consultando...' : 'Buscar Reserva'}</span>
        </button>
      </form>

      {/* Error Message */}
      {errorMsg && (
        <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium flex items-center gap-2.5">
          <MdErrorOutline className="w-5 h-5 text-rose-600 shrink-0" />
          <span>{errorMsg}</span>
        </div>
      )}

      {/* Action Success */}
      {actionSuccess && (
        <div className="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium flex items-center gap-2.5">
          <MdCheckCircle className="w-5 h-5 text-emerald-600 shrink-0" />
          <span>{actionSuccess}</span>
        </div>
      )}

      {/* Reservation Details Display */}
      {reservation && (
        <div className="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-xl shadow-slate-200/50">
          
          {/* Header Banner */}
          <div className="p-6 sm:p-8 bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <span className="text-xs text-slate-400 font-semibold uppercase tracking-wider block">
                Comprobante Oficial de Reserva
              </span>
              <div className="flex flex-wrap items-center gap-2 sm:gap-3 mt-1">
                <span className="text-xl sm:text-3xl font-black text-rose-400 tracking-wider">
                  {reservation.reservation_code}
                </span>
                <span className={`px-3 py-1 rounded-full text-xs font-extrabold uppercase ${
                  [1, 2].includes(reservation.estado)
                    ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40'
                    : 'bg-rose-500/20 text-rose-300 border border-rose-500/40'
                }`}>
                  {[1, 2].includes(reservation.estado) ? '✓ Confirmada' : '✗ Cancelada'}
                </span>
              </div>
            </div>

            <button
              type="button"
              onClick={() => window.print()}
              className="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold flex items-center gap-2 cursor-pointer transition-colors"
            >
              <MdPrint className="w-4 h-4" />
              <span>{textos.reservas.imprimir}</span>
            </button>
          </div>

          {/* Details Body */}
          <div className="p-6 sm:p-8 space-y-6">
            
            {/* Property details */}
            <div className="flex flex-col sm:flex-row gap-5 items-start">
              {reservation.room_unit?.accommodation?.images?.[0] && (
                <img
                  src={reservation.room_unit.accommodation.images[0]}
                  alt="Alojamiento"
                  className="w-full sm:w-48 aspect-16/10 object-cover rounded-2xl"
                />
              )}
              <div className="space-y-1 flex-1">
                <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700">
                  {reservation.room_unit?.accommodation?.accommodation_type?.name}
                </span>
                <h3 className="text-xl font-bold text-slate-900">
                  {reservation.room_unit?.accommodation?.name}
                </h3>
                <p className="text-xs text-slate-500 flex items-center gap-1">
                  <MdPlace className="w-3.5 h-3.5 text-rose-500" />
                  {reservation.room_unit?.accommodation?.address}, {reservation.room_unit?.accommodation?.city}, {reservation.room_unit?.accommodation?.country}
                </p>
                <p className="text-xs font-semibold text-slate-700 pt-2">
                  Unidad reservada: <span className="font-extrabold text-slate-900">{reservation.room_unit?.name}</span>
                </p>
              </div>
            </div>

            {/* Grid of info */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
              <div>
                <span className="text-slate-400 block mb-0.5">Fecha de Llegada</span>
                <span className="font-extrabold text-slate-800 text-sm">{reservation.check_in_date}</span>
              </div>

              <div>
                <span className="text-slate-400 block mb-0.5">Fecha de Salida</span>
                <span className="font-extrabold text-slate-800 text-sm">{reservation.check_out_date}</span>
              </div>

              <div>
                <span className="text-slate-400 block mb-0.5">Duración</span>
                <span className="font-extrabold text-slate-800 text-sm">{reservation.nights} noches</span>
              </div>

              <div>
                <span className="text-slate-400 block mb-0.5">{textos.reservas.totalAPagar}</span>
                <span className="font-black text-rose-600 text-base">
                  {/* Moneda congelada en la reserva, no la del catálogo. */}
                  {formatearMoneda(reservation.total_price, reservation.currency, idioma)}
                </span>
              </div>
            </div>

            {/* Guest info */}
            <div className="border-t border-slate-100 pt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-slate-600">
              <div>
                <span className="text-slate-400 block">Titular de la Reserva</span>
                <span className="font-bold text-slate-800">{reservation.guest_name}</span>
              </div>
              <div>
                <span className="text-slate-400 block">Correo de Contacto</span>
                <span className="font-bold text-slate-800">{reservation.guest_email}</span>
              </div>
              <div>
                <span className="text-slate-400 block">Teléfono</span>
                <span className="font-bold text-slate-800">
                  {/* El teléfono se guarda sin prefijo; las reservas anteriores a
                      esa columna lo muestran tal cual se escribió. */}
                  {reservation.guest_country_code
                    ? `${reservation.guest_country_code} ${reservation.guest_phone}`
                    : reservation.guest_phone}
                </span>
              </div>
            </div>

            {reservation.special_requests && (
              <div className="border-t border-slate-100 pt-3 text-xs text-slate-600">
                <span className="text-slate-400 block font-semibold">Peticiones Especiales</span>
                <p className="mt-0.5 italic text-slate-700">"{reservation.special_requests}"</p>
              </div>
            )}

            {/* Cancellation Status or Button */}
            {[1, 2].includes(reservation.estado) ? (
              <div className="border-t border-slate-100 pt-5 flex justify-end">
                <button
                  type="button"
                  onClick={() => setCancelModalOpen(true)}
                  className="px-4 py-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-colors cursor-pointer"
                >
                  {textos.reservas.cancelar}
                </button>
              </div>
            ) : (
              <div className="p-3.5 rounded-xl bg-slate-100 text-slate-600 text-xs">
                Esta reserva fue cancelada el {reservation.cancelled_at || 'recientemente'}.
                {reservation.cancellation_reason && ` Motivo: ${reservation.cancellation_reason}`}
              </div>
            )}

          </div>

        </div>
      )}

      {/* Cancel Confirmation Modal */}
      {cancelModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
          <div className="bg-white rounded-3xl max-w-md w-full max-h-[90dvh] overflow-y-auto p-6 space-y-4 shadow-2xl">
            <h3 className="text-lg font-bold text-slate-900">{textos.reservas.tituloCancelar}</h3>
            <p className="text-xs text-slate-500">
              Esta acción liberará la unidad para otros viajeros. Esta operación no se puede revertir.
            </p>

            <div>
              <label className="text-xs font-semibold text-slate-700 block mb-1">
                Motivo de la cancelación (opcional)
              </label>
              <textarea
                rows="2"
                placeholder={textos.reservas.motivoPlaceholder}
                value={cancelReason}
                onChange={(e) => setCancelReason(e.target.value)}
                className="w-full px-3 py-2 text-xs bg-slate-50 rounded-xl border border-slate-200 focus:outline-hidden focus:border-rose-500"
              />
            </div>

            <div className="flex gap-2 justify-end pt-2">
              <button
                type="button"
                onClick={() => setCancelModalOpen(false)}
                className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100"
              >
                Volver
              </button>
              <button
                type="button"
                disabled={cancelling}
                onClick={handleCancelReservation}
                className="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700 shadow-md shadow-rose-600/20 disabled:opacity-50"
              >
                {cancelling ? textos.reservas.cancelando : textos.reservas.confirmarCancelacion}
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
