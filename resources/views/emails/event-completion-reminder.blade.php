<!DOCTYPE html>
<html lang="es">
@php use Carbon\Carbon; @endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu evento terminó</title>
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
        .highlight { background: #e3f2fd; border-left: 4px solid #1e88e5; padding: 12px 16px; margin: 16px 0; border-radius: 4px; font-size: 14px; color: #0d47a1; }
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
                            <h1 class="newsletter-title" style="margin-top: 12px;">Tu evento ha terminado</h1>
                            <p style="font-size: 16px; margin-bottom: 4px;">Hola {{ $sale->artist->name ?? 'artista' }},</p>
                            <p class="newsletter-subtitle">La presentación ya finalizó. Recuerda marcarla como completada para que el cliente pueda calificarte.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="highlight">
                                    <strong>Pendiente:</strong> Marca el evento como completado para liberar tu pago y permitir que el cliente deje su calificación.
                                </div>
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
                                        <span class="info-label">Tu horario</span>
                                        <span class="info-value">{{ $sale->event_hour }} &middot; {{ $sale->event_hours }} hora(s)</span>
                                    </div>
                                </div>

                                <div style="margin-top: 28px; text-align: center;">
                                    <a href="{{ $frontendUrl }}/artist/my-calendar" target="_blank" class="btn btn-primary" style="color: #fff; background: #094FAB;">
                                        Marcar evento como completado
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque tienes un evento recién terminado en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
