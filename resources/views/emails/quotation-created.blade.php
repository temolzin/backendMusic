<!DOCTYPE html>
<html lang="es">
@php use Carbon\Carbon; @endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Cotización</title>
    <style>
        {{ file_get_contents(resource_path('css/app.css')) }}
        .newsletter-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .newsletter-table th { text-align: left; padding: 10px 12px; border-bottom: 2px solid var(--gsm-border); color: var(--gsm-dark); font-size: 14px; }
        .newsletter-table td { padding: 10px 12px; border-bottom: 1px solid var(--gsm-border); color: var(--gsm-text); font-size: 15px; }
        .newsletter-table .total { font-weight: 800; color: var(--gsm-dark); border-top: 2px solid var(--gsm-dark); font-size: 17px; }
        .newsletter-table .discount { color: #28a745; }
        .newsletter-table .extra-km { color: var(--gsm-primary); }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; padding: 3px 0; font-weight: 700; color: var(--gsm-dark); white-space: nowrap; width: 120px; }
        .info-value { display: table-cell; padding: 3px 0; color: var(--gsm-text); }
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
                            <h1 class="newsletter-title">Cotización</h1>
                            <p class="newsletter-subtitle">Gracias por solicitar tu cotización en Vibeer.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="info-grid">
                                    <div class="info-row">
                                        <span class="info-label">Cliente</span>
                                        <span class="info-value">{{ $quotation->full_name }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Email</span>
                                        <span class="info-value">{{ $quotation->email }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Ciudad</span>
                                        <span class="info-value">{{ $quotation->city }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Dirección</span>
                                        <span class="info-value">{{ $quotation->address }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Evento</span>
                                        <span class="info-value">{{ Carbon::parse($quotation->event_date)->format('d/m/Y') }} &middot; {{ $quotation->event_hours }} hora(s)</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Artista</span>
                                        <span class="info-value">{{ $quotation->artist->name }}</span>
                                    </div>
                                </div>

                                @php
                                    $showDiscount = $quotation->discount_percentage && $quotation->discount_percentage > 0 && $quotation->discount_amount && $quotation->discount_amount > 0;
                                    $showExtraKm = $quotation->extra_km_distance && $quotation->extra_km_cost && $quotation->extra_km_cost > 0;
                                @endphp

                                <table class="newsletter-table" cellspacing="0" cellpadding="0" border="0">
                                    <thead>
                                        <tr>
                                            <th>Descripción</th>
                                            <th style="text-align: right;">Importe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Precio base ({{ $quotation->event_hours }} h &times; ${{ number_format($quotation->artist->price_hour, 2, '.', ',') }}/h)</td>
                                            <td style="text-align: right;">$ {{ number_format($quotation->base_price ?? $quotation->price, 2, '.', ',') }}</td>
                                        </tr>
                                        @if($showDiscount)
                                        <tr>
                                            <td>Descuento ({{ number_format($quotation->discount_percentage, 0) }}%)</td>
                                            <td class="discount" style="text-align: right;">- $ {{ number_format($quotation->discount_amount, 2, '.', ',') }}</td>
                                        </tr>
                                        @endif
                                        @if($showExtraKm)
                                        <tr>
                                            <td>Km extra ({{ number_format($quotation->extra_km_distance, 2) }} km)</td>
                                            <td class="extra-km" style="text-align: right;">+ $ {{ number_format($quotation->extra_km_cost, 2, '.', ',') }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>

                                <table class="newsletter-table" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td class="total" style="text-align: left;">Total</td>
                                        <td class="total" style="text-align: right;">$ {{ number_format($quotation->price, 2, '.', ',') }}</td>
                                    </tr>
                                </table>

                                <p style="margin-top: 24px;">Saludos.</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque solicitaste una cotización en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>