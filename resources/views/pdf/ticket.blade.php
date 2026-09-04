|   <!DOCTYPE html>
<html lang="es">
@php
    use Carbon\Carbon;
    $ticketNumber = $sale->id;
    $isCash = $sale->payment_method === 'cash';
    $isCard = $sale->payment_method === 'card';
    $cardBrand = $sale->_card_brand ?? '';
    $cardLast = $sale->_card_last_digits ?? '';
    $cardLabel = '';
    if ($cardBrand === 'visa') { $cardLabel = 'Visa'; }
    elseif ($cardBrand === 'mastercard') { $cardLabel = 'Mastercard'; }
    elseif ($cardBrand === 'amex') { $cardLabel = 'American Express'; }
    else { $cardLabel = ucfirst($cardBrand); }
@endphp
<head>
    <meta charset="UTF-8">
    <title>Ticket de compra - Vibeer</title>
    <style>
        @page { margin: 0px; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            background: #f0f2f5;
            font-size: 12px;
            color: #222;
            line-height: 1.5;
        }
        .ticket {
            width: 380px;
            margin: 20px auto;
            background: #ffffff;
        }
        .header {
            background-color: #094FAB;
            padding: 22px 24px 14px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header .icon-space {
            height: 8px;
        }
        .header img {
            height: 40px;
        }
        .header h1 {
            font-size: 17px;
            margin: 8px 0 3px 0;
            font-weight: bold;
        }
        .header p {
            font-size: 11px;
            margin: 0;
            color: #c8d6f0;
        }
        .body {
            padding: 16px 24px 12px 24px;
        }
        .receipt-number {
            text-align: center;
            margin-bottom: 10px;
        }
        .receipt-number .label {
            font-size: 9px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .receipt-number .number {
            font-size: 18px;
            font-weight: bold;
            color: #094FAB;
            letter-spacing: 2px;
            margin-top: 2px;
        }
        .divider {
            height: 1px;
            background-color: #ddd;
            margin: 10px 0;
        }
        .artist-section {
            text-align: center;
            margin-bottom: 10px;
        }
        .artist-section .name {
            font-size: 18px;
            font-weight: bold;
            color: #111;
        }
        .artist-section .meta {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table tr td {
            padding: 4px 0;
            border-bottom: 1px dotted #e8e8e8;
            vertical-align: middle;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .info-table td.label {
            font-size: 9px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            width: 100px;
            padding-right: 8px;
        }
        .info-table td.value {
            font-size: 13px;
            color: #222;
            font-weight: bold;
        }
        .info-table td.value .card-detail {
            font-size: 11px;
            color: #555;
            font-weight: normal;
            display: block;
            margin-top: 1px;
        }
        .payment-tag {
            display: inline-block;
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 1px 8px;
            font-size: 10px;
            font-weight: bold;
        }
        .payment-tag.cash {
            background-color: #fff3e0;
            color: #e65100;
        }
        .total-box {
            background-color: #f4f7fc;
            padding: 12px 14px;
            margin-top: 12px;
        }
        .total-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .total-box table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .total-box .total-label {
            font-size: 11px;
            color: #555;
        }
        .total-box .total-pending {
            font-size: 10px;
            color: #e65100;
            margin-top: 1px;
        }
        .total-box .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #094FAB;
            text-align: right;
        }
        .total-box .total-currency {
            font-size: 11px;
            color: #888;
            font-weight: normal;
        }
        .footer {
            text-align: center;
            padding: 12px 24px 14px 24px;
            background-color: #fafafa;
            border-top: 1px solid #eee;
        }
        .footer .brand {
            font-size: 13px;
            font-weight: bold;
            color: #094FAB;
            margin-bottom: 3px;
        }
        .footer p {
            font-size: 10px;
            color: #aaa;
            margin: 1px 0;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <div class="icon-space"></div>
            <img src="{{ public_path('logovibeer.png') }}" alt="Vibeer" style="height: 80px;">
            <h1>Compra confirmada</h1>
            <p>Tu evento ha sido registrado exitosamente</p>
        </div>

        <div class="body">
            <div class="receipt-number">
                <div class="label">Folio</div>
                <div class="number">{{ $ticketNumber }}</div>
            </div>

            <div class="divider"></div>

            <div class="artist-section">
                <div class="name">{{ $sale->artist->name }}</div>
                @if($sale->artist->zone)
                    <div class="meta">{{ $sale->artist->zone }}</div>
                @endif
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">Evento</td>
                    <td class="value">{{ Carbon::parse($sale->event_date)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</td>
                </tr>
                <tr>
                    <td class="label">Horario</td>
                    <td class="value">{{ $sale->event_hour }}</td>
                </tr>
                <tr>
                    <td class="label">Duraci&oacute;n</td>
                    <td class="value">{{ $sale->event_hours }} hora(s)</td>
                </tr>
                <tr>
                    <td class="label">Pago</td>
                    <td class="value">
                        <span class="payment-tag {{ $isCash ? 'cash' : '' }}">{{ $isCash ? 'Efectivo' : 'Tarjeta' }}</span>
                        @if($isCard && $cardLabel)
                            <span class="card-detail">{{ $cardLabel }} ****{{ $cardLast }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Cliente</td>
                    <td class="value">{{ $sale->customer_first_name }} {{ $sale->customer_last_name }}</td>
                </tr>
                @if($sale->customer_email)
                <tr>
                    <td class="label">Correo</td>
                    <td class="value" style="font-size:11px;">{{ $sale->customer_email }}</td>
                </tr>
                @endif
            </table>

            <div class="total-box">
                <table>
                    <tr>
                        <td>
                            <div class="total-label">Total pagado</div>
                        </td>
                        <td>
                            <div class="total-amount">
                                ${{ number_format($sale->amount, 2, '.', ',') }}
                                <span class="total-currency">MXN</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="footer">
            <div class="brand">Vibeer</div>
            <p>Gracias por confiar en nosotros</p>
            <p>Si tienes dudas, contacta a soporte@vibeer.com</p>
        </div>
    </div>
</body>
</html>
