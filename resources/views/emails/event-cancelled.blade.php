<!DOCTYPE html>
<html lang="es">
@php use Carbon\Carbon; @endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isBeforeApproval ? 'Solicitud cancelada' : 'Evento cancelado' }}</title>
    <style>
        {{ file_get_contents(resource_path('css/app.css')) }}
        .info-grid { width: 100%; }
        .info-row { padding: 8px 0; border-bottom: 1px solid #eaeaea; display: flex; align-items: center; }
        .info-row:last-child { border-bottom: none; }
        .info-label { width: 100px; font-weight: 700; color: var(--gsm-dark); font-size: 13px; flex-shrink: 0; }
        .info-value { color: var(--gsm-text); font-size: 14px; }
        .center-msg { text-align: center; font-style: italic; color: #666; width: 100%; }
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
                            <h1 class="newsletter-title" style="margin-top: 12px;">{{ $isBeforeApproval ? 'Solicitud cancelada' : 'Evento cancelado' }}</h1>
                            @if ($cancelledBy === 'client' && $isBeforeApproval && $recipientType === 'client')
                                <p class="newsletter-subtitle" style="text-align: center;">Has cancelado esta solicitud.</p>
                            @endif
                            @if ($cancelledBy === 'client' && $isBeforeApproval && $recipientType !== 'client')
                                <p class="newsletter-subtitle" style="text-align: center;">{{ $sale->customer_first_name }} {{ $sale->customer_last_name }} ha cancelado esta solicitud.</p>
                            @endif
                            @if ($cancelledBy === 'client' && !$isBeforeApproval && $recipientType === 'client')
                                <p class="newsletter-subtitle" style="text-align: center;">Has cancelado el evento.</p>
                            @endif
                            @if ($cancelledBy === 'client' && !$isBeforeApproval && $recipientType !== 'client')
                                <p class="newsletter-subtitle" style="text-align: center;">{{ $sale->customer_first_name }} {{ $sale->customer_last_name }} ha cancelado el evento.</p>
                            @endif
                            @if ($cancelledBy !== 'client' && $recipientType === 'client')
                                <p class="newsletter-subtitle" style="text-align: center;">{{ $sale->artist->name }} ha cancelado su evento.</p>
                            @endif
                            @if ($cancelledBy !== 'client' && $recipientType !== 'client')
                                <p class="newsletter-subtitle" style="text-align: center;">Has cancelado el evento.</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="info-grid">
                                    <div class="info-row" style="border-bottom: none; justify-content: center;">
                                        <span class="info-value center-msg">{{ $reason }}</span>
                                    </div>
                                </div>

                                <div class="info-grid" style="margin-top: 16px;">
                                    <div class="info-row">
                                        <span class="info-label">{{ $isBeforeApproval ? 'Solicitante' : 'Artista' }}</span>
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
                                    @if ($recipientType === 'client' && $cancelledBy !== 'client')
                                        <div class="info-row">
                                            <span class="info-label">Reembolso</span>
                                            <span class="info-value" style="font-weight: 700; font-size: 17px; color: #2e7d32;">$ {{ number_format($refundAmount, 2, '.', ',') }} MXN</span>
                                        </div>
                                    @endif
                                    @if ($recipientType === 'client' && $cancelledBy === 'client' && $penaltyAmount > 0)
                                        <div class="info-row">
                                            <span class="info-label">Penalizaci&oacute;n</span>
                                            <span class="info-value" style="font-weight: 700; color: #c62828;">$ {{ number_format($penaltyAmount, 2, '.', ',') }} MXN</span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">Total a devolver</span>
                                            <span class="info-value" style="font-weight: 700; font-size: 17px; color: #2e7d32;">$ {{ number_format($refundAmount, 2, '.', ',') }} MXN</span>
                                        </div>
                                    @endif
                                    @if ($recipientType === 'client' && $cancelledBy === 'client' && $penaltyAmount == 0)
                                        <div class="info-row">
                                            <span class="info-label">Reembolso</span>
                                            <span class="info-value" style="font-weight: 700; font-size: 17px; color: #2e7d32;">$ {{ number_format($refundAmount, 2, '.', ',') }} MXN</span>
                                        </div>
                                    @endif
                                    @if ($recipientType !== 'client' && $cancelledBy !== 'client' && $penaltyAmount > 0)
                                        <div class="info-row">
                                            <span class="info-label">Penalizaci&oacute;n</span>
                                            <span class="info-value" style="font-weight: 700; color: #c62828;">{{ $penaltyPercentage }}% &middot; $ {{ number_format($penaltyAmount, 2, '.', ',') }} MXN</span>
                                        </div>
                                    @endif
                                    @if ($recipientType !== 'client' && $cancelledBy === 'client')
                                        <div class="info-row" style="border-bottom: none; justify-content: center;">
                                            <span class="info-value center-msg">Esta fecha ya está libre para recibir nuevas solicitudes.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque un evento fue cancelado en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
