<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso de nueva reserva para la empresa propietaria del alojamiento.
 *
 * El destinatario principal es el correo de `empresas` vinculado al
 * alojamiento (alojamientos.empresa_id) y HospedaYa recibe copia como
 * respaldo. Si el alojamiento no tiene empresa, el correo sale igualmente
 * solo para el respaldo: la reserva no debe perderse por falta de vínculo.
 */
class ReservationConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Reservation $reservation)
    {
    }

    public function envelope(): Envelope
    {
        // Una reserva de paquete turístico no tiene unidad de habitación: su
        // asunto nombra el paquete, no el alojamiento.
        $asunto = $this->reservation->esPaquete()
            ? "Nueva reserva de paquete {$this->reservation->reservation_code}"
                . " - {$this->reservation->travelPackage?->nombre}"
            : "Nueva reserva {$this->reservation->reservation_code}"
                . " - {$this->reservation->roomUnit?->accommodation?->name}";

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.reservation-confirmation');
    }
}
