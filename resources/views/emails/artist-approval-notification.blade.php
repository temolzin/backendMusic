<!DOCTYPE html>
<html lang="es">
@php use Carbon\Carbon; @endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $status === 'accepted' ? 'Solicitud aceptada' : 'Solicitud rechazada' }}</title>
    <style>
        {{ file_get_contents(resource_path('css/app.css')) }}
        .info-grid { width: 100%; }
        .info-row { padding: 8px 0; border-bottom: 1px solid #eaeaea; display: flex; align-items: center; }
        .info-row:last-child { border-bottom: none; }
        .info-label { width: 100px; font-weight: 700; color: var(--gsm-dark); font-size: 13px; flex-shrink: 0; }
        .info-value { color: var(--gsm-text); font-size: 14px; }
        .btn { display: inline-block; padding: 12px 32px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 15px; text-align: center; }
        .btn-primary { background: #094FAB; color: #fff !important; }
        .link { color: #094FAB; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <table class="newsletter-shell" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" class="newsletter-shell__outer">
                <table class="newsletter-card" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td class="newsletter-hero">
                            <img src="{{ $message->embed(public_path('logovibeer.png')) }}" alt="Vibeer" class="newsletter-logo">
                            <h1 class="newsletter-title" style="margin-top: 12px;">
                                {{ $status === 'accepted' ? 'Solicitud aceptada' : 'Solicitud rechazada' }}
                            </h1>
                            <p class="newsletter-subtitle">
                                {{ $sale->artist->name }} ha {{ $status === 'accepted' ? 'aceptado' : 'rechazado' }} tu solicitud de evento.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="info-grid">
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
                                @if ($status === 'accepted')
                                    <div class="info-row">
                                        <span class="info-label">Monto</span>
                                        <span class="info-value" style="font-weight: 700; font-size: 17px; color: #2e7d32;">$ {{ number_format($sale->amount, 2, '.', ',') }} MXN</span>
                                    </div>
                                @endif
                                @if ($status !== 'accepted')
                                    <div class="info-row" style="border-bottom: none; text-align: center; justify-content: center;">
                                        <span class="info-value" style="font-style: italic; color: #666; text-align: center;">
                                            Este artista no pudo, pero hay m&aacute;s opciones para ti. Explora nuestro extenso cat&aacute;logo de artistas y encuentra el ideal.
                                        </span>
                                    </div>
                                @endif
                                </div>

                                <div style="margin-top: 28px; text-align: center;">
                                    <a href="{{ $frontendUrl }}/client/shopping-cart/view-my-order-details" target="_blank" class="btn btn-primary" style="color: #fff; background: #094FAB; margin-bottom: 10px;">
                                        Ver mis compras
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque el artista respondi&oacute; a tu solicitud de evento en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
