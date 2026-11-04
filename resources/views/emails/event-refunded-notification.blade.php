<!DOCTYPE html>
<html lang="es">
@php use Carbon\Carbon; @endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reembolso Procesado</title>
    <style>
        {{ file_get_contents(resource_path('css/app.css')) }}
        .info-grid { width: 100%; }
        .info-row { padding: 8px 0; border-bottom: 1px solid #eaeaea; display: flex; align-items: center; }
        .info-row:last-child { border-bottom: none; }
        .info-label { width: 120px; font-weight: 700; color: var(--gsm-dark); font-size: 13px; flex-shrink: 0; }
        .info-value { color: var(--gsm-text); font-size: 14px; }
        .btn { display: inline-block; padding: 12px 32px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 15px; text-align: center; }
        .btn-primary { background: #094FAB; color: #fff !important; }
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
                            <h1 class="newsletter-title" style="margin-top: 12px; color: #fffdfd;">
                                Reembolso Procesado
                            </h1>
                            <p class="newsletter-subtitle">
                                Se ha completado el proceso de reembolso correspondiente a tu contratación.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="info-grid">
                                    <div class="info-row">
                                        <span class="info-label">Folio Venta</span>
                                        <span class="info-value">#{{ $sale->id }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Artista</span>
                                        <span class="info-value">{{ $sale->artist->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Fecha Evento</span>
                                        <span class="info-value">{{ Carbon::parse($sale->event_date)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Monto Devuelto</span>
                                        <span class="info-value" style="font-weight: 700; font-size: 17px; color: #094FAB;">$ {{ number_format($refundAmount, 2, '.', ',') }} MXN</span>
                                    </div>
                                    @if ($reason)
                                        <div class="info-row">
                                            <span class="info-label">Motivo</span>
                                            <span class="info-value">{{ $reason }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="info-row" style="background-color: #e3f2fd; padding: 12px; border-radius: 8px; border-left: 4px solid #094FAB; margin-top: 16px;">
                                    <span class="info-value" style="color: #0d47a1; font-size: 13px;">
                                        Dependiendo de tu institución bancaria, el reembolso puede tardar entre 3 y 10 días hábiles en verse reflejado en tu cuenta o tarjeta.
                                    </span>
                                </div>

                                <div style="margin-top: 28px; text-align: center;">
                                    <a href="{{ $frontendUrl }}/client/shopping-cart/view-my-order-details" target="_blank" class="btn btn-primary">
                                        Ver mis compras
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque se notificó un reembolso sobre tu evento contratado en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
