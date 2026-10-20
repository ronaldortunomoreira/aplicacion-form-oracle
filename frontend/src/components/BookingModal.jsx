import React, { useState, useEffect } from 'react';
import { 
  MdClose, 
  MdCheckCircle, 
  MdCalendarToday, 
  MdPeople, 
  MdCreditCard, 
  MdVerifiedUser, 
  MdAutoAwesome, 
  MdPrint, 
  MdErrorOutline,
  MdContentCopy,
  MdCheck
} from 'react-icons/md';
import { reservationService } from '../services/api';
import { useIdioma } from '../hooks/useIdioma';
import { copy } from '../seo/copys';
import { formatearMoneda } from '../utils/moneda';

export default function BookingModal({
  accommodation,
  roomUnit,
  initialCheckIn,
  initialCheckOut,
  initialGuests,
  onClose,
  onViewMyBookings
}) {
  const idioma = useIdioma();
  const textos = copy(idioma);
  // El desglose previo se formatea con la moneda del alojamiento, la misma que
  // el servidor congelará al confirmar. Si el alojamiento no tiene país, el
  // formateador cae al número pelado en vez de inventar un símbolo.
  const moneda = accommodation?.currency ?? null;
  const [checkIn, setCheckIn] = useState(initialCheckIn || new Date().toISOString().split('T')[0]);
  
  // Default checkout: tomorrow if not provided
  const [checkOut, setCheckOut] = useState(() => {
    if (initialCheckOut) return initialCheckOut;
    const d = new Date();
    d.setDate(d.getDate() + 2);
    return d.toISOString().split('T')[0];
  });

  const [adults, setAdults] = useState(initialGuests || 1);
  const [children, setChildren] = useState(0);

  // Form fields
  const [formData, setFormData] = useState({
    guest_name: '',
    guest_email: '',
    guest_phone: '',
    guest_country_code: '',
    guest_document_id: '',
    payment_method: 'pay_at_property',
    special_requests: '',
  });

  // Country catalogue for the phone field. Same 245 countries as the hotel
  // registration form: the guest's phone can be from anywhere, not only from
  // the countries we have accommodations in.
  const [countries, setCountries] = useState([]);
  const [paisesError, setPaisesError] = useState(false);

  const paisSeleccionado = countries.find((item) => item.iso2 === formData.guest_country_code) || null;

  // State
  const [loadingPreview, setLoadingPreview] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [priceBreakdown, setPriceBreakdown] = useState(null);
  const [confirmedReservation, setConfirmedReservation] = useState(null);
  const [copiedCode, setCopiedCode] = useState(false);

  // Calculate preview whenever dates or unit changes
  useEffect(() => {
    if (checkIn && checkOut && checkOut > checkIn && roomUnit) {
      calculatePreview();
    }
  }, [checkIn, checkOut, roomUnit]);

  // Load the country list once, and again if the visitor switches language.
  useEffect(() => {
    let vigente = true;

    reservationService
      .countries(idioma)
      .then((res) => {
        if (vigente) setCountries(res.data || []);
      })
      .catch(() => {
        // El teléfono es obligatorio, así que sin la lista el formulario no se
        // puede completar. Se avisa en lugar de dejar un selector vacío.
        if (vigente) setPaisesError(true);
      });

    return () => {
      vigente = false;
    };
  }, [idioma]);

  const calculatePreview = async () => {
    try {
      setLoadingPreview(true);
      setErrorMsg('');
      const res = await reservationService.preview({
        room_unit_id: roomUnit.id,
        check_in_date: checkIn,
        check_out_date: checkOut,
        adults_count: adults,
        children_count: children,
      });
      if (res.success) {
        setPriceBreakdown(res.data);
      }
    } catch (err) {
      setErrorMsg(err.response?.data?.message || textos.reserva.errorTarifa);
    } finally {
      setLoadingPreview(false);
    }
  };

  // Un mismo mensaje para el rechazo del navegador y el del backend: el patrón
  // por país solo lo conoce el backend, así que el texto nunca puede ser
  // específico. Se acompaña del ejemplo del país elegido para que el huésped vea
  // qué se espera.
  const errorTelefono = () =>
    textos.reserva.errorTelefono({
      pais: paisSeleccionado?.name || '',
      ejemplo: paisSeleccionado?.phone_example || '',
    });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMsg('');

    if (!formData.guest_name || !formData.guest_email || !formData.guest_phone || !formData.guest_country_code) {
      setErrorMsg(textos.reserva.camposObligatorios);
      return;
    }

    // Comprobación previa laxa: solo descarta lo que no puede ser un teléfono.
    // El patrón exacto por país lo aplica el backend con los metadatos de la UIT,
    // que no tiene sentido replicar aquí.
    const digitos = formData.guest_phone.replace(/\D/g, '');
    if (digitos.length < 4 || digitos.length > 15) {
      setErrorMsg(errorTelefono());
      return;
    }

    try {
      setSubmitting(true);
      const payload = {
        room_unit_id: roomUnit.id,
        guest_name: formData.guest_name,
        guest_email: formData.guest_email,
        guest_phone: formData.guest_phone,
        guest_country_code: formData.guest_country_code,
        guest_document_id: formData.guest_document_id,
        check_in_date: checkIn,
        check_out_date: checkOut,
        adults_count: adults,
        children_count: children,
        payment_method: formData.payment_method,
        special_requests: formData.special_requests,
      };

      const res = await reservationService.create(payload);
      if (res.success) {
        setConfirmedReservation(res.data);
      }
    } catch (err) {
      const errores = err.response?.data?.errors || {};
      if (errores.guest_country_code) {
        setErrorMsg(textos.reserva.errorPais);
      } else if (errores.guest_phone) {
        setErrorMsg(errorTelefono());
      } else {
        setErrorMsg(err.response?.data?.message || textos.reserva.errorReserva);
      }
    } finally {
      setSubmitting(false);
    }
  };

  const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    setCopiedCode(true);
    setTimeout(() => setCopiedCode(false), 2000);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/75 backdrop-blur-sm animate-in fade-in duration-200 overflow-y-auto">
      <div 
        className="bg-white rounded-3xl max-w-2xl w-full max-h-[94dvh] overflow-y-auto shadow-2xl border border-slate-100 relative my-auto scrollbar-thin"
        onClick={(e) => e.stopPropagation()}
      >
        
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-all cursor-pointer"
        >
          <MdClose className="w-4 h-4" />
        </button>

        {/* ------------------- CONFIRMATION SUCCESS VIEW ------------------- */}
        {confirmedReservation ? (
          <div className="p-6 sm:p-10 text-center space-y-6">
            <div className="w-16 h-16 rounded-3xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
              <MdCheckCircle className="w-9 h-9" />
            </div>

            <div>
              <span className="text-xs font-bold tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full uppercase">
                {textos.reserva.confirmada}
              </span>
              <h2 className="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2">
                {textos.reserva.gracias({ nombre: confirmedReservation.guest_name })}
              </h2>
              <p className="text-xs sm:text-sm text-slate-500 mt-1">
                {textos.reserva.enviadaA({ correo: confirmedReservation.guest_email })}
              </p>
            </div>

            {/* Voucher Card */}
            <div className="bg-slate-50 rounded-2xl p-5 border border-slate-200 text-left space-y-4">
              
              <div className="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-3">
                <div>
                  <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                    {textos.reserva.codigoReserva}
                  </span>
                  <span className="text-xl font-black text-rose-600 tracking-wider">
                    {confirmedReservation.reservation_code}
                  </span>
                </div>
                <button
                  type="button"
                  onClick={() => copyToClipboard(confirmedReservation.reservation_code)}
                  className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 cursor-pointer transition-colors"
                >
                  {copiedCode ? <MdCheck className="w-3.5 h-3.5 text-emerald-600" /> : <MdContentCopy className="w-3.5 h-3.5" />}
                  <span>{copiedCode ? textos.reserva.copiado : textos.reserva.copiarCodigo}</span>
                </button>
              </div>

              <div className="grid grid-cols-2 gap-3 text-xs">
                <div>
                  <span className="text-slate-400 block">{textos.reserva.alojamiento}</span>
                  <span className="font-bold text-slate-800">{accommodation.name}</span>
                </div>
                <div>
                  <span className="text-slate-400 block">{textos.reserva.unidad}</span>
                  <span className="font-bold text-slate-800">{roomUnit.name}</span>
                </div>
                <div>
                  <span className="text-slate-400 block">{textos.reserva.fechas}</span>
                  <span className="font-bold text-slate-800">
                    {confirmedReservation.check_in_date} - {confirmedReservation.check_out_date}{' '}
                    {textos.reserva.noches({ total: confirmedReservation.nights })}
                  </span>
                </div>
                <div>
                  <span className="text-slate-400 block">{textos.reserva.totalAPagar}</span>
                  <span className="font-black text-slate-900 text-sm">
                    {/* La moneda congelada por el servidor, no la del catálogo:
                        una reserva ya confirmada no cambia aunque el país la
                        cambie mañana. */}
                    {formatearMoneda(
                      confirmedReservation.total_price,
                      confirmedReservation.currency,
                      idioma
                    )}
                  </span>
                </div>
              </div>

              <div className="pt-2 text-[11px] text-slate-500 border-t border-slate-200">
                Modalidad: <strong className="text-slate-700">Pago en el establecimiento a la llegada</strong>
              </div>
            </div>

            {/* Action Buttons */}
            <div className="flex flex-col sm:flex-row items-center gap-3 pt-2">
              <button
                type="button"
                onClick={() => window.print()}
                className="w-full sm:flex-1 py-3 px-4 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50 flex items-center justify-center gap-2 cursor-pointer transition-colors"
              >
                <MdPrint className="w-4 h-4" />
                <span>{textos.reserva.imprimirVoucher}</span>
              </button>

              <button
                type="button"
                onClick={() => {
                  onClose();
                  onViewMyBookings(confirmedReservation.reservation_code, confirmedReservation.guest_email);
                }}
                className="w-full sm:flex-1 py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center gap-2 cursor-pointer shadow-md shadow-slate-900/10 transition-colors"
              >
                <span>{textos.reserva.verEnMisReservas}</span>
              </button>
            </div>

          </div>
        ) : (

        /* ------------------- BOOKING FORM VIEW ------------------- */
          <form onSubmit={handleSubmit} className="p-6 sm:p-8 space-y-6">
            
            {/* Header */}
            <div className="pr-10">
              <span className="text-xs font-bold text-rose-600 uppercase tracking-wider block">
                Finalizar Reserva
              </span>
              <h2 className="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1">
                {roomUnit.name}
              </h2>
              <p className="text-xs text-slate-500 mt-0.5">
                {accommodation.name} • {accommodation.city}, {accommodation.country}
              </p>
            </div>

            {errorMsg && (
              <div className="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2">
                <MdErrorOutline className="w-4 h-4 shrink-0 text-rose-600" />
                <span>{errorMsg}</span>
              </div>
            )}

            {/* Date Pickers */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
              <div>
                <label className="text-xs font-bold text-slate-600 block mb-1">Llegada</label>
                <input
                  type="date"
                  min={new Date().toISOString().split('T')[0]}
                  value={checkIn}
                  onChange={(e) => setCheckIn(e.target.value)}
                  className="w-full px-3 py-2 bg-white text-slate-800 text-xs font-semibold rounded-xl border border-slate-200 focus:outline-hidden focus:border-rose-500"
                  required
                />
              </div>

              <div>
                <label className="text-xs font-bold text-slate-600 block mb-1">Salida</label>
                <input
                  type="date"
                  min={checkIn}
                  value={checkOut}
                  onChange={(e) => setCheckOut(e.target.value)}
                  className="w-full px-3 py-2 bg-white text-slate-800 text-xs font-semibold rounded-xl border border-slate-200 focus:outline-hidden focus:border-rose-500"
                  required
                />
              </div>
            </div>

            {/* Guest Form Fields */}
            <div className="space-y-3.5">
              <h3 className="text-sm font-bold text-slate-900">{textos.reserva.datosHuesped}</h3>

              <div>
                <label className="text-xs font-semibold text-slate-600 block mb-1">Nombre Completo *</label>
                <input
                  type="text"
                  placeholder={textos.reserva.nombrePlaceholder}
                  value={formData.guest_name}
                  onChange={(e) => setFormData({ ...formData, guest_name: e.target.value })}
                  className="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white text-slate-800 text-xs font-medium rounded-xl border border-slate-200 focus:border-rose-500 focus:outline-hidden"
                  required
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">
                    {textos.reserva.pais} *
                  </label>
                  <select
                    value={formData.guest_country_code}
                    onChange={(e) =>
                      setFormData({ ...formData, guest_country_code: e.target.value, guest_phone: '' })
                    }
                    required
                    disabled={paisesError}
                    className="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white text-slate-800 text-xs font-medium rounded-xl border border-slate-200 focus:border-rose-500 focus:outline-hidden disabled:opacity-60"
                  >
                    <option value="">
                      {paisesError ? textos.reserva.paisesNoDisponibles : textos.reserva.paisSelecciona}
                    </option>
                    {countries.map((item) => (
                      <option key={item.iso2} value={item.iso2}>
                        {item.name} ({item.dial_code})
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">
                    {textos.reserva.telefono} *
                  </label>
                  <input
                    type="tel"
                    inputMode="tel"
                    placeholder={paisSeleccionado?.phone_example || textos.reserva.telefonoPlaceholder}
                    value={formData.guest_phone}
                    onChange={(e) => setFormData({ ...formData, guest_phone: e.target.value })}
                    className="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white text-slate-800 text-xs font-medium rounded-xl border border-slate-200 focus:border-rose-500 focus:outline-hidden"
                    required
                  />
                </div>
              </div>

              {paisSeleccionado && (
                <p className="text-[11px] text-slate-500 -mt-1.5">
                  {textos.reserva.paisAyuda({ prefijo: paisSeleccionado.dial_code })}
                </p>
              )}

              <div>
                <label className="text-xs font-semibold text-slate-600 block mb-1">
                  {textos.reserva.correo} *
                </label>
                <input
                  type="email"
                  placeholder={textos.reserva.correoPlaceholder}
                  value={formData.guest_email}
                  onChange={(e) => setFormData({ ...formData, guest_email: e.target.value })}
                  className="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white text-slate-800 text-xs font-medium rounded-xl border border-slate-200 focus:border-rose-500 focus:outline-hidden"
                  required
                />
              </div>

              <div>
                <label className="text-xs font-semibold text-slate-600 block mb-1">Peticiones Especiales (Opcional)</label>
                <textarea
                  rows="2"
                  placeholder={textos.reserva.notasPlaceholder}
                  value={formData.special_requests}
                  onChange={(e) => setFormData({ ...formData, special_requests: e.target.value })}
                  className="w-full px-3.5 py-2 bg-slate-50 focus:bg-white text-slate-800 text-xs font-medium rounded-xl border border-slate-200 focus:border-rose-500 focus:outline-hidden resize-none"
                />
              </div>
            </div>

            {/* Price Breakdown */}
            {priceBreakdown && (
              <div className="p-4 rounded-2xl bg-rose-50/60 border border-rose-100 text-xs space-y-2">
                <div className="flex justify-between text-slate-600">
                  <span>
                    {formatearMoneda(priceBreakdown.price_per_night, moneda, idioma)} ×{' '}
                    {priceBreakdown.nights} noches
                  </span>
                  <span className="font-semibold">
                    {formatearMoneda(priceBreakdown.stay_subtotal, moneda, idioma)}
                  </span>
                </div>

                {priceBreakdown.cleaning_fee > 0 && (
                  <div className="flex justify-between text-slate-600">
                    <span>{textos.reserva.tarifaLimpieza}</span>
                    <span className="font-semibold">
                      {formatearMoneda(priceBreakdown.cleaning_fee, moneda, idioma)}
                    </span>
                  </div>
                )}

                <div className="flex justify-between text-slate-600">
                  <span>{textos.reserva.impuestos}</span>
                  <span className="font-semibold">
                    {formatearMoneda(priceBreakdown.taxes, moneda, idioma)}
                  </span>
                </div>

                <div className="flex justify-between text-slate-900 font-extrabold text-sm pt-2 border-t border-rose-200">
                  <span>{textos.reserva.totalFinal}</span>
                  <span className="text-rose-600">
                    {formatearMoneda(priceBreakdown.total_price, moneda, idioma)}
                  </span>
                </div>
              </div>
            )}

            {/* Submit Button */}
            <button
              type="submit"
              disabled={submitting || loadingPreview}
              className="w-full py-3.5 px-6 rounded-2xl bg-[#154495] hover:bg-[#0f3a7a] text-white font-extrabold text-sm shadow-lg shadow-[#154495]/30 flex items-center justify-center gap-2 active:scale-98 transition-all cursor-pointer disabled:opacity-50"
            >
              <MdVerifiedUser className="w-4 h-4" />
              <span>{submitting ? textos.reserva.confirmando : textos.reserva.confirmarAhora}</span>
            </button>

            <p className="text-[11px] text-center text-slate-400">
              🔒 {textos.reserva.notaPago}
            </p>

          </form>
        )}

      </div>
    </div>
  );
}
