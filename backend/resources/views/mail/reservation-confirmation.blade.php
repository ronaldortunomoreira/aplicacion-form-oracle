<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva reserva {{ $reservation->reservation_code }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7;padding:24px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">

                    <tr>
                        <td style="background-color:#0f172a;padding:24px 32px;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;">HospedaYa</h1>
                            <p style="margin:6px 0 0;color:#94a3b8;font-size:13px;">Nueva reserva recibida</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px;">
                            <p style="margin:0 0 16px;font-size:15px;color:#334155;">
                                @if($reservation->esPaquete())
                                    Se ha registrado una nueva reserva del paquete turístico
                                    <strong>{{ $reservation->travelPackage?->nombre }}</strong>.
                                @else
                                    Se ha registrado una nueva reserva en
                                    <strong>{{ $reservation->roomUnit?->accommodation?->name }}</strong>
                                    ({{ $reservation->roomUnit?->name }}).
                                @endif
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:6px;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:12px 16px;background-color:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">Código de reserva</td>
                                    <td style="padding:12px 16px;background-color:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:15px;font-weight:bold;color:#0f172a;">{{ $reservation->reservation_code }}</td>
                                </tr>
                                @if($reservation->check_in_date)
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">Entrada</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{{ $reservation->check_in_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">Salida</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{{ $reservation->check_out_date->format('d/m/Y') }} ({{ $reservation->nights }} noches)</td>
                                </tr>
                                @elseif($reservation->travelPackage?->duracion)
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">Duración</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{{ $reservation->travelPackage->duracion }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">Huéspedes</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{{ $reservation->adults_count }} adulto(s), {{ $reservation->children_count }} niño(s)</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">Estado</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#16a34a;font-weight:bold;">{{ $reservation->reservation_status === 'confirmed' ? 'Confirmada' : $reservation->reservation_status }}</td>
                                </tr>
                            </table>

                            <h2 style="margin:0 0 8px;font-size:15px;color:#0f172a;">Datos del huésped</h2>
                            <p style="margin:0 0 20px;font-size:14px;color:#334155;line-height:1.6;">
                                {{ $reservation->guest_name }}<br>
                                Correo: {{ $reservation->guest_email }}<br>
                                Teléfono: {{ $reservation->guest_country_code }} {{ $reservation->guest_phone }}@if($reservation->guest_document_id)<br>Documento: {{ $reservation->guest_document_id }}@endif
                            </p>

                            <h2 style="margin:0 0 8px;font-size:15px;color:#0f172a;">Desglose de precio</h2>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:6px;margin-bottom:20px;">
                                @if($reservation->esPaquete())
                                <tr>
                                    <td style="padding:10px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">Paquete turístico</td>
                                    <td align="right" style="padding:10px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{{ number_format((float) $reservation->total_price, 2) }} {{ $reservation->currency }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td style="padding:10px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">{{ $reservation->nights }} noche(s) x {{ number_format((float) $reservation->price_per_night, 2) }}</td>
                                    <td align="right" style="padding:10px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{{ number_format((float) ($reservation->price_per_night * $reservation->nights), 2) }} {{ $reservation->currency }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">Tarifa de limpieza</td>
                                    <td align="right" style="padding:10px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{{ number_format((float) $reservation->cleaning_fee, 2) }} {{ $reservation->currency }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">Impuestos (10%)</td>
                                    <td align="right" style="padding:10px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{{ number_format((float) $reservation->taxes, 2) }} {{ $reservation->currency }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:12px 16px;background-color:#f8fafc;font-size:14px;font-weight:bold;color:#0f172a;">Total</td>
                                    <td align="right" style="padding:12px 16px;background-color:#f8fafc;font-size:16px;font-weight:bold;color:#0f172a;">{{ number_format((float) $reservation->total_price, 2) }} {{ $reservation->currency }}</td>
                                </tr>
                            </table>

                            <p style="margin:0 0 6px;font-size:14px;color:#334155;">
                                <strong>Forma de pago:</strong>
                                @switch($reservation->payment_method)
                                    @case('credit_card') Tarjeta de crédito @break
                                    @case('transfer') Transferencia bancaria @break
                                    @default Pago en el alojamiento
                                @endswitch
                            </p>

                            @if($reservation->special_requests)
                                <p style="margin:0 0 6px;font-size:14px;color:#334155;">
                                    <strong>Solicitudes especiales:</strong> {{ $reservation->special_requests }}
                                </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 32px;background-color:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:12px;color:#94a3b8;">
                                @if($reservation->esPaquete())
                                    Gracias por reservar con HospedaYa. Guarda el código
                                    {{ $reservation->reservation_code }} para cualquier consulta.
                                @else
                                    Este aviso lo envía HospedaYa a la empresa propietaria del alojamiento.
                                    Guarda el código {{ $reservation->reservation_code }} para cualquier consulta.
                                @endif
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
