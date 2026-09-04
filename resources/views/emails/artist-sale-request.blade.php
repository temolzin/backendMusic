<!DOCTYPE html>
<html lang="es">
@php use Carbon\Carbon; @endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva solicitud de evento</title>
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
        .link:hover { text-decoration: underline; }
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
                            <h1 class="newsletter-title" style="margin-top: 12px;">Nueva solicitud de evento</h1>
                            <p style="font-size: 16px; margin-bottom: 4px;">Hola {{ $sale->artist->name }},</p>
                            <p class="newsletter-subtitle">Tienes una nueva solicitud de evento pendiente de tu aprobaci&oacute;n.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="info-grid">
                                    <div class="info-row">
                                        <span class="info-label">Cliente</span>
                                        <span class="info-value">{{ $sale->customer_first_name }} {{ $sale->customer_last_name }}</span>
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
                                        <span class="info-label">Ubicaci&oacute;n</span>
                                        <span class="info-value">
                                            <a href="https://www.google.com/maps?q={{ urlencode($sale->customer_address . ($sale->customer_city ? ', ' . $sale->customer_city : '') . ($sale->customer_state ? ', ' . $sale->customer_state : '')) }}" target="_blank" class="link">
                                                {{ $sale->customer_address }}{{ $sale->customer_city ? ', ' . $sale->customer_city : '' }}{{ $sale->customer_state ? ', ' . $sale->customer_state : '' }}
                                            </a>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Monto</span>
                                        <span class="info-value" style="font-weight: 700; font-size: 17px; color: #2e7d32;">$ {{ number_format($sale->amount, 2, '.', ',') }} MXN</span>
                                    </div>
                                </div>

                                <div style="margin-top: 28px; text-align: center;">
                                    <a href="{{ $frontendUrl }}/artist/pending-approvals" target="_blank" class="btn btn-primary" style="color: #fff; background: #094FAB; margin-bottom: 10px;">
                                        Ver solicitudes
                                    </a>
                                </div>

                                <p style="text-align: center; color: #666; font-size: 13px; margin-top: 8px;">
                                    Tienes <strong>24 horas</strong> para aceptar o rechazar esta solicitud.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque tienes una nueva solicitud de evento en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
