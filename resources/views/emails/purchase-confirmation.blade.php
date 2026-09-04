<!DOCTYPE html>
<html lang="es">
@php use Carbon\Carbon; @endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por tu compra</title>
    <style>
        {{ file_get_contents(resource_path('css/app.css')) }}
        .info-grid { width: 100%; }
        .info-row { padding: 10px 0; border-bottom: 1px solid #eaeaea; display: flex; align-items: center; }
        .info-row:last-child { border-bottom: none; }
        .info-label { width: 120px; font-weight: 700; color: var(--gsm-dark); font-size: 13px; flex-shrink: 0; }
        .info-value { color: var(--gsm-text); font-size: 14px; }
        .btn { display: inline-block; padding: 14px 36px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; text-align: center; }
        .btn-primary { background: #094FAB; color: #fff !important; }
        .btn-outline { background: transparent; border: 2px solid #094FAB; color: #094FAB !important; }
        .amount-display { font-size: 24px; font-weight: 800; color: #094FAB; text-align: center; padding: 16px; background: #f0f5ff; border-radius: 10px; margin: 16px 0; }
        .check-icon { display: inline-block; width: 56px; height: 56px; border-radius: 50%; background: #4caf50; color: #fff; line-height: 56px; font-size: 30px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <table class="newsletter-shell" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" class="newsletter-shell__outer">
                <table class="newsletter-card" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td class="newsletter-hero">
                            <div class="check-icon">&#10003;</div>
                            <img src="{{ $message->embed(public_path('logovibeer.png')) }}" alt="Vibeer" class="newsletter-logo">
                            <h1 class="newsletter-title" style="margin-top: 12px;">
                                Gracias por tu compra
                            </h1>
                            <p class="newsletter-subtitle">
                                Hola {{ $sale->customer_first_name }}, tu reservacion con <strong>{{ $sale->artist->name }}</strong> ha sido confirmada.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <p style="text-align:center;color:#555;font-size:15px;line-height:1.6;">
                                    Adjunto a este correo encontraras tu ticket de compra en PDF con todos los detalles de tu evento.
                                </p>

                                <div class="amount-display">
                                    $ {{ number_format($sale->amount, 2, '.', ',') }} MXN
                                </div>

                                <div class="info-grid" style="margin-top:16px;">
                                    <div class="info-row">
                                        <span class="info-label">Artista</span>
                                        <span class="info-value">{{ $sale->artist->name }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Fecha</span>
                                        <span class="info-value">{{ Carbon::parse($sale->event_date)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Horario</span>
                                        <span class="info-value">{{ $sale->event_hour }} &middot; {{ $sale->event_hours }} hora(s)</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Pago</span>
                                        <span class="info-value">{{ $sale->payment_method === 'cash' ? 'Efectivo' : 'Tarjeta' }}</span>
                                    </div>
                                    @if($sale->payment_method === 'cash' && $sale->cashReference)
                                        <div class="info-row">
                                            <span class="info-label">Referencia</span>
                                            <span class="info-value" style="font-weight:700;letter-spacing:2px;">{{ $sale->cashReference->cash_reference }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div style="margin-top:28px;text-align:center;">
                                    <a href="{{ $frontendUrl }}/client/shopping-cart/view-my-order-details" target="_blank" class="btn btn-primary" style="margin-bottom:10px;color:#fff;">
                                        Ver mis compras
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque tu reservacion en Vibeer fue confirmada exitosamente.</p>
                            <p>Si tienes dudas, contacta a soporte@vibeer.com</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
