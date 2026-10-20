import React, { createContext, useCallback, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import BookingModal from '../components/BookingModal';

const ContextoReserva = createContext(null);

/**
 * Comparte las fechas de búsqueda y el modal de reserva entre rutas.
 *
 * Antes de introducir el enrutado, estos datos vivían en el estado de App y se
 * pasaban por props al modal. Con rutas separadas, la home y la página de
 * detalle necesitan leer las mismas fechas, así que viven aquí.
 *
 * El <BookingModal> se monta una sola vez aquí: cualquier página puede abrir una
 * reserva llamando a `abrirReserva(accommodation, unit)`.
 */
export function ProveedorReserva({ children }) {
  const navegar = useNavigate();

  const [checkIn, setCheckIn] = useState('');
  const [checkOut, setCheckOut] = useState('');
  const [guests, setGuests] = useState(1);
  const [reserva, setReserva] = useState(null);

  const abrirReserva = useCallback((accommodation, unit) => {
    setReserva({ accommodation, unit });
  }, []);

  const cerrarReserva = useCallback(() => setReserva(null), []);

  const reiniciarBusqueda = useCallback(() => {
    setCheckIn('');
    setCheckOut('');
    setGuests(1);
  }, []);

  const verMisReservas = useCallback((code, email) => {
    setReserva(null);
    const params = new URLSearchParams();

    if (code) params.set('codigo', code);
    if (email) params.set('correo', email);

    const query = params.toString();
    navegar(query ? `/mis-reservas?${query}` : '/mis-reservas');
  }, [navegar]);

  const valor = useMemo(
    () => ({
      checkIn,
      checkOut,
      guests,
      setCheckIn,
      setCheckOut,
      setGuests,
      abrirReserva,
      reiniciarBusqueda,
    }),
    [checkIn, checkOut, guests, abrirReserva, reiniciarBusqueda]
  );

  return (
    <ContextoReserva.Provider value={valor}>
      {children}

      {reserva && (
        <BookingModal
          accommodation={reserva.accommodation}
          roomUnit={reserva.unit}
          initialCheckIn={checkIn}
          initialCheckOut={checkOut}
          initialGuests={guests}
          onClose={cerrarReserva}
          onViewMyBookings={verMisReservas}
        />
      )}
    </ContextoReserva.Provider>
  );
}

export function useReserva() {
  const contexto = React.useContext(ContextoReserva);

  if (!contexto) {
    throw new Error('useReserva debe usarse dentro de <ProveedorReserva>');
  }

  return contexto;
}

export default ContextoReserva;
