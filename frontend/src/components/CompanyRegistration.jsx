import React, { useEffect, useState } from 'react';
import {
  MdBusiness,
  MdPerson,
  MdEmail,
  MdChat,
  MdForum,
  MdSend,
  MdCheckCircle,
  MdErrorOutline,
} from 'react-icons/md';
import { companyService } from '../services/api';
import { copy } from '../seo/copys';
import { useIdioma } from '../hooks/useIdioma';

/**
 * Formulario para registrar una empresa.
 *
 * Pide el nombre de la empresa y el de la persona de contacto, que se guardan en
 * columnas distintas (`nombre_empresa` y `nombre`). Al enviar solo se muestra la
 * confirmación de que la solicitud llegó: el listado de solicitudes es interno y
 * no se expone en la página.
 *
 * El selector ofrece todos los países con los que se puede validar un número de
 * WhatsApp, no solo los que tienen alojamientos publicados, y los carga de
 * `GET /api/company-countries`. Cada uno llega con su prefijo y un número de
 * ejemplo.
 *
 * La validación del número la hace entera el backend con los datos de la UIT
 * (libphonenumber). Aquí solo se comprueba que el campo parezca un teléfono: un
 * número mal escrito no se detecta hasta enviarlo, y esa es la única forma de no
 * reimplementar en el navegador unos patrones que el servidor ya conoce.
 */
export default function CompanyRegistration() {
  const idioma = useIdioma();
  const textos = copy(idioma).empresas;

  const [name, setName] = useState('');
  const [companyName, setCompanyName] = useState('');
  const [email, setEmail] = useState('');
  const [countries, setCountries] = useState([]);
  const [countryIso, setCountryIso] = useState('');
  const [whatsapp, setWhatsapp] = useState('');
  const [detail, setDetail] = useState('');
  const [sending, setSending] = useState(false);
  const [successMsg, setSuccessMsg] = useState('');
  const [errorMsg, setErrorMsg] = useState('');
  const [paisesError, setPaisesError] = useState(false);

  useEffect(() => {
    let vigente = true;

    const cargar = async () => {
      try {
        const res = await companyService.countries(idioma);
        if (!vigente) return;

        if (res.success) {
          setCountries(res.data);
          setPaisesError(false);
        } else {
          setPaisesError(true);
        }
      } catch {
        if (vigente) setPaisesError(true);
      }
    };

    cargar();

    // Cambiar de idioma vuelve a pedir los nombres ya traducidos.
    return () => {
      vigente = false;
    };
  }, [idioma]);

  const pais = countries.find((item) => item.iso2 === countryIso) || null;

  /**
   * Comprobación mínima antes de enviar: que haya dígitos y que no excedan los
   * 15 del formato E.164. Si no se cumple, el servidor lo rechaza igual, pero sin
   * gastar un viaje.
   */
  const whatsappValido = () => {
    const digitos = whatsapp.replace(/\D/g, '');
    return digitos.length >= 4 && digitos.length <= 15;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMsg('');
    setSuccessMsg('');

    if (!name.trim()) return setErrorMsg(textos.errorNombre);
    if (!companyName.trim()) return setErrorMsg(textos.errorNombreEmpresa);
    if (!/^\S+@\S+\.\S+$/.test(email)) return setErrorMsg(textos.errorCorreo);
    if (!pais) return setErrorMsg(textos.errorPais);
    if (detail.length > 2000) return setErrorMsg(textos.errorDetalle);
    if (!whatsappValido()) {
      return setErrorMsg(
        textos.errorWhatsapp({ pais: pais.name, ejemplo: pais.phone_example })
      );
    }

    try {
      setSending(true);
      const res = await companyService.create({
        name: name.trim(),
        company_name: companyName.trim(),
        email: email.trim(),
        country_code: pais.iso2,
        whatsapp: whatsapp.trim(),
        detail: detail.trim(),
      });

      if (res.success) {
        setName('');
        setCompanyName('');
        setEmail('');
        setWhatsapp('');
        setDetail('');
        setSuccessMsg(textos.enviada);
      }
    } catch (err) {
      const errores = err.response?.data?.errors;
      if (errores?.email) {
        setErrorMsg(textos.errorCorreoDuplicado);
      } else if (errores?.name) {
        setErrorMsg(textos.errorNombre);
      } else if (errores?.company_name) {
        setErrorMsg(textos.errorNombreEmpresa);
      } else if (errores?.country_code) {
        setErrorMsg(textos.errorPais);
      } else if (errores?.whatsapp) {
        setErrorMsg(
          textos.errorWhatsapp({
            pais: pais?.name || '',
            ejemplo: pais?.phone_example || '',
          })
        );
      } else if (errores?.detail) {
        setErrorMsg(textos.errorDetalle);
      } else {
        setErrorMsg(textos.errorGenerico);
      }
    } finally {
      setSending(false);
    }
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 py-10 space-y-8 animate-in fade-in duration-200">
      <div className="text-center space-y-2">
        <h1 className="text-3xl sm:text-4xl font-black text-slate-900">{textos.h1}</h1>
        <p className="text-sm text-slate-500 max-w-lg mx-auto">{textos.resumen}</p>
      </div>

      <form
        onSubmit={handleSubmit}
        className="bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 border border-slate-200/80 space-y-4"
      >
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label
              htmlFor="empresa-nombre-empresa"
              className="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5 ml-1"
            >
              {textos.nombreEmpresa}
            </label>
            <div className="relative">
              <MdBusiness className="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                id="empresa-nombre-empresa"
                type="text"
                value={companyName}
                onChange={(e) => setCompanyName(e.target.value)}
                placeholder={textos.nombreEmpresaPlaceholder}
                maxLength={120}
                required
                className="w-full pl-11 px-4 py-3 bg-slate-50 focus:bg-white text-slate-800 text-sm rounded-2xl border border-slate-200 focus:border-rose-500 focus:outline-hidden"
              />
            </div>
          </div>

          <div>
            <label
              htmlFor="empresa-nombre"
              className="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5 ml-1"
            >
              {textos.nombre}
            </label>
            <div className="relative">
              <MdPerson className="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                id="empresa-nombre"
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                placeholder={textos.nombrePlaceholder}
                maxLength={120}
                required
                className="w-full pl-11 px-4 py-3 bg-slate-50 focus:bg-white text-slate-800 text-sm rounded-2xl border border-slate-200 focus:border-rose-500 focus:outline-hidden"
              />
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label
              htmlFor="empresa-correo"
              className="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5 ml-1"
            >
              {textos.correo}
            </label>
            <div className="relative">
              <MdEmail className="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                id="empresa-correo"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder={textos.correoPlaceholder}
                maxLength={150}
                required
                className="w-full pl-11 px-4 py-3 bg-slate-50 focus:bg-white text-slate-800 text-sm rounded-2xl border border-slate-200 focus:border-rose-500 focus:outline-hidden"
              />
            </div>
          </div>

          <div>
            <label
              htmlFor="empresa-pais"
              className="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5 ml-1"
            >
              {textos.pais}
            </label>
            <select
              id="empresa-pais"
              value={countryIso}
              onChange={(e) => {
                setCountryIso(e.target.value);
                setWhatsapp('');
              }}
              required
              disabled={paisesError}
              className="w-full px-4 py-3 bg-slate-50 focus:bg-white text-slate-800 text-sm rounded-2xl border border-slate-200 focus:border-rose-500 focus:outline-hidden disabled:opacity-60"
            >
              <option value="">
                {paisesError ? textos.paisesNoDisponibles : textos.paisSelecciona}
              </option>
              {countries.map((item) => (
                <option key={item.iso2} value={item.iso2}>
                  {item.name} ({item.dial_code})
                </option>
              ))}
            </select>
          </div>
        </div>

        <div>
          <label
            htmlFor="empresa-whatsapp"
            className="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5 ml-1"
          >
            {textos.whatsapp}
          </label>
          <div className="relative">
            <MdForum className="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              id="empresa-whatsapp"
              type="tel"
              inputMode="tel"
              value={whatsapp}
              onChange={(e) => setWhatsapp(e.target.value)}
              placeholder={pais?.phone_example || textos.whatsappPlaceholder}
              maxLength={30}
              required
              className="w-full pl-11 px-4 py-3 bg-slate-50 focus:bg-white text-slate-800 text-sm rounded-2xl border border-slate-200 focus:border-rose-500 focus:outline-hidden"
            />
          </div>
        </div>

        {pais && (
          <p className="text-xs text-slate-500 -mt-1">
            {textos.whatsappAyuda({ prefijo: pais.dial_code })}
          </p>
        )}

        <div>
          <label
            htmlFor="empresa-detalle"
            className="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5 ml-1"
          >
            {textos.detalle}
          </label>
          <div className="relative">
            <MdChat className="w-4 h-4 absolute left-4 top-4 text-slate-400" />
            <textarea
              id="empresa-detalle"
              value={detail}
              onChange={(e) => setDetail(e.target.value)}
              placeholder={textos.detallePlaceholder}
              maxLength={2000}
              rows={4}
              className="w-full pl-11 px-4 py-3 bg-slate-50 focus:bg-white text-slate-800 text-sm rounded-2xl border border-slate-200 focus:border-rose-500 focus:outline-hidden resize-y"
            />
          </div>
        </div>

        <button
          type="submit"
          disabled={sending}
          className="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-[#154495] hover:bg-[#0f3a7a] disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-sm rounded-2xl shadow-lg shadow-[#154495]/25 transition-colors"
        >
          <MdSend className="w-4 h-4" />
          {sending ? textos.enviando : textos.enviar}
        </button>

        {successMsg && (
          <p className="flex items-start gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3">
            <MdCheckCircle className="w-4 h-4 shrink-0 mt-0.5" />
            {successMsg}
          </p>
        )}

        {errorMsg && (
          <p className="flex items-start gap-2 text-sm text-rose-700 bg-rose-50 border border-rose-200 rounded-2xl px-4 py-3">
            <MdErrorOutline className="w-4 h-4 shrink-0 mt-0.5" />
            {errorMsg}
          </p>
        )}
      </form>
    </div>
  );
}