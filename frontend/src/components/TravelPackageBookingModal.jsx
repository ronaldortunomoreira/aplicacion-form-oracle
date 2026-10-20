import React, { useState, useEffect } from 'react';
import { MdClose, MdCheckCircle, MdContentCopy, MdCheck, MdVerifiedUser, MdErrorOutline } from 'react-icons/md';
import { reservationService } from '../services/api';
import { useIdioma } from '../hooks/useIdioma';
import { copy } from '../seo/copys';
import { formatearMoneda } from '../utils/moneda';

export default function TravelPackageBookingModal({ travelPackage, onClose, onViewMyBookings }) {
  const idioma = useIdioma();
  const textos = copy(idioma);
  const moneda = travelPackage?.currency ?? null;

  const [formData, setFormData] = useState({
    guest_name: '',
    guest_email: '',
    guest_phone: '',
    guest_country_code: '',
    guest_document_id: '',
    special_requests: '',
    payment_method: 'pay_at_property',
  });

  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [confirmedReservation, setConfirmedReservation] = useState(null);
  const [copiedCode, setCopiedCode] = useState(false);
  const [countries, setCountries] = useState([]);
  const [paisesError, setPaisesError] = useState(false);

  const paisSeleccionado = countries.find((item) => item.iso2 === formData.guest_country_code) || null;

  useEffect(() => {
    let vigente = true;
    reservationService
      .countries()
      .then((res) => {
        if (vigente) setCountries(res.data || []);
      })
      .catch(() => {
        if (vigente) setPaisesError(true);
      });
    return () => {
      vigente = false;
    };
  }, []);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setErrorMsg('');

    try {
      const res = await reservationService.reservePackage({
        travel_package_id: travelPackage.id,
        guest_name: formData.guest_name,
        guest_email: formData.guest_email,
        guest_phone: formData.guest_phone,
        guest_country_code: formData.guest_country_code,
        guest_document_id: formData.guest_document_id,
        payment_method: formData.payment_method,
        special_requests: formData.special_requests,
      });

      if (res.success) {
        setConfirmedReservation(res.data);
      } else {
        setErrorMsg(res.message || textos.reserva.errorReserva);
      }
    } catch (err) {
      setErrorMsg(err.response?.data?.message || textos.reserva.errorReserva);
    } finally {
      setSubmitting(false);
    }
  };

  const copyReservationCode = async () => {
    if (!confirmedReservation?.reservation_code) return;
    try {
      await navigator.clipboard.writeText(confirmedReservation.reservation_code);
      setCopiedCode(true);
      setTimeout(() => setCopiedCode(false), 2000);
    } catch (err) {
      console.error('Error al copiar el código', err);
    }
  };

  if (confirmedReservation) {
    return (
      <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div className="bg-white rounded-2xl max-w-lg w-full max-h-[90dvh] overflow-y-auto">
          <div className="p-6 space-y-6">
            <div className="text-center space-y-3">
              <div className="mx-auto w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <MdCheckCircle className="w-6 h-6 text-green-600" />
              </div>
              <h2 className="text-xl font-bold text-slate-900">
                {textos.reserva.confirmada}
              </h2>
              <p className="text-slate-600">
                {textos.reserva.enviadaA({ correo: confirmedReservation.guest_email })}
              </p>
            </div>

            <div className="bg-slate-50 rounded-xl p-4 space-y-3">
              <div className="flex justify-between">
                <span className="text-slate-600">{textos.reserva.codigoReserva}</span>
                <div className="flex items-center gap-2">
                  <span className="font-mono font-semibold">{confirmedReservation.reservation_code}</span>
                  <button
                    onClick={copyReservationCode}
                    className="p-1 hover:bg-white rounded"
                    aria-label={textos.reserva.copiarCodigo}
                  >
                    {copiedCode ? (
                      <MdCheck className="w-4 h-4 text-green-600" />
                    ) : (
                      <MdContentCopy className="w-4 h-4 text-slate-400" />
                    )}
                  </button>
                </div>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-600">{textos.reserva.paquete}</span>
                <span className="font-medium">{travelPackage.name}</span>
              </div>
            </div>

            <div className="flex flex-col gap-3">
              <button
                onClick={() =>
                  onViewMyBookings(
                    confirmedReservation.reservation_code,
                    confirmedReservation.guest_email
                  )
                }
                className="w-full px-4 py-2.5 bg-teal-600 text-white rounded-xl font-semibold hover:bg-teal-700 transition-colors"
              >
                {textos.reserva.verEnMisReservas}
              </button>
              <button
                onClick={onClose}
                className="w-full px-4 py-2.5 border border-slate-200 text-slate-700 rounded-xl font-semibold hover:bg-slate-50 transition-colors"
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-2xl w-full max-h-[90dvh] overflow-y-auto">
        <div className="sticky top-0 bg-white border-b border-slate-100 p-4 flex justify-between items-start">
          <div>
            <h2 className="text-lg font-bold text-slate-900">Reservar Paquete Turístico</h2>
            <p className="text-sm text-slate-600 mt-1">{travelPackage.name}</p>
          </div>
          <button
            onClick={onClose}
            className="p-2 hover:bg-slate-100 rounded-full"
          >
            <MdClose className="w-5 h-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-4 space-y-6">
          {errorMsg && (
            <div className="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-start gap-2">
              <MdErrorOutline className="w-5 h-5 flex-shrink-0 mt-0.5" />
              <span>{errorMsg}</span>
            </div>
          )}

          <div className="space-y-4">
            <h3 className="font-semibold text-slate-900">{textos.reserva.datosHuesped}</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">
                  Nombre Completo *
                </label>
                <input
                  type="text"
                  name="guest_name"
                  value={formData.guest_name}
                  onChange={handleChange}
                  required
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">
                  {textos.reserva.correo} *
                </label>
                <input
                  type="email"
                  name="guest_email"
                  value={formData.guest_email}
                  onChange={handleChange}
                  required
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">
                  {textos.reserva.pais} *
                </label>
                <select
                  name="guest_country_code"
                  value={formData.guest_country_code}
                  onChange={handleChange}
                  required
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                >
                  <option value="">{textos.reserva.paisSelecciona}</option>
                  {countries.map((c) => (
                    <option key={c.iso2} value={c.iso2}>
                      {c.name} ({c.dial_code})
                    </option>
                  ))}
                </select>
                {paisesError && (
                  <p className="mt-1 text-xs text-teal-600">
                    {textos.reserva.paisesNoDisponibles}
                  </p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">
                  {textos.reserva.telefono} *
                </label>
                <div className="flex gap-2">
                  <div className="w-24 px-2 py-2 border border-slate-200 rounded-lg bg-slate-50 text-sm text-slate-600">
                    {paisSeleccionado ? paisSeleccionado.dial_code : '—'}
                  </div>
                  <input
                    type="tel"
                    name="guest_phone"
                    value={formData.guest_phone}
                    onChange={handleChange}
                    placeholder={paisSeleccionado?.phone_example || textos.reserva.telefonoPlaceholder}
                    required
                    className="flex-1 px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                  />
                </div>
              </div>
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-slate-700 mb-1">
                  Documento de Identidad (Opcional)
                </label>
                <input
                  type="text"
                  name="guest_document_id"
                  value={formData.guest_document_id}
                  onChange={handleChange}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                />
              </div>
            </div>
          </div>

          <div className="space-y-4">
            <h3 className="font-semibold text-slate-900">Información del Paquete</h3>
            <div className="bg-slate-50 rounded-xl p-4 space-y-2">
              <div className="flex justify-between">
                <span className="text-slate-600">Precio desde</span>
                <span className="font-medium">
                  {formatearMoneda(travelPackage.starting_price, moneda)}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-600">{textos.reserva.duracion}</span>
                <span className="font-medium">{travelPackage.duration}</span>
              </div>
            </div>
          </div>

          <div className="space-y-4">
            <h3 className="font-semibold text-slate-900">Políticas de Pago</h3>
            <div className="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
              <MdVerifiedUser className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
              <p className="text-sm text-amber-800">
                Recibirás la confirmación de inmediato sin pago por adelantado. Coordina el
                pago del paquete directamente con la agencia.
              </p>
            </div>
          </div>

          <div className="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t border-slate-100">
            <button
              type="button"
              onClick={onClose}
              className="w-full sm:w-auto px-4 py-2.5 border border-slate-200 text-slate-700 rounded-xl font-semibold hover:bg-slate-50 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="submit"
              disabled={submitting}
              className="w-full sm:w-auto px-6 py-2.5 bg-[#154495] text-white rounded-xl font-semibold hover:bg-[#0f3a7a] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {submitting ? textos.reserva.confirmando : textos.reserva.confirmarAhora}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
