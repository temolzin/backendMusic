<!DOCTYPE html>
<html lang="en">
@php use Carbon\Carbon; @endphp

<head>
    <meta charset="utf-8">
    <title>Cotizacion</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" media="all" />
</head>

<body>
    <header class="clearfix">
        <div id="logo">
            <img src="/logovibeer.png">
        </div>
        <div id="company">
            <h2 class="name">Vibeer</h2>
            <div>455 Foggy Heights, AZ 85004, US</div>
            <div>(602) 519-0450</div>
            <div><a href="mailto:company@example.com">Vibeer@gmail.com</a></div>
        </div>
        </div>
    </header>
    <main>
        <div id="details" class="clearfix">
            <div id="client">
                <div class="to">
                    <h2>Datos:</h2>
                </div>
                <div class="name">Cliente: {{ $quotation->full_name }}</div>
                <div class="address">Ciudad: {{ $quotation->city }}</div>
                <div class="address">Domicilio: {{ $quotation->address }}</div>
                <div class="email"><a href="mailto:john@example.com">Email: {{ $quotation->email }}</a></div>
            </div>
            <div id="invoice">
                <h1>Cotizacion</h1>
                <div class="date">{{ Carbon::parse($quotation->event_date)->format('d/m/Y') }}</div>
                <div class="date">{{ $quotation->created_at ? $quotation->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        <div class="table">
            <table class="items table ">
                <thead>
                    <tr>
                        <th class="text-center">Artista</th>
                        <th class="text-center">Fecha del Evento</th>
                        <th class="text-center">Duración</th>
                        <th class="text-center">Precio por Hora</th>
                        <th class="text-center">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">{{ $quotation->artist->name }}</td>
                        <td class="text-center">{{ Carbon::parse($quotation->event_date)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $quotation->event_hours }} hora(s)</td>
                        <td class="text-center">$ {{ number_format($quotation->artist->price_hour, 2, '.', ',') }}</td>
                        <td class="text-center">$ {{ number_format($quotation->base_price ?? $quotation->price, 2, '.', ',') }}</td>
                    </tr>
                </tbody>
                @php
                    $showDiscount = $quotation->discount_percentage && $quotation->discount_percentage > 0 && $quotation->discount_amount && $quotation->discount_amount > 0;
                    $showExtraKm = $quotation->extra_km_distance && $quotation->extra_km_cost && $quotation->extra_km_cost > 0;
                @endphp
                @if($showDiscount)
                    <tr>
                        <td colspan="4" class="text-right" style="padding: 8px 10px;">Descuento ({{ number_format($quotation->discount_percentage, 0) }}%)</td>
                        <td class="text-center" style="color: #28a745;">- $ {{ number_format($quotation->discount_amount, 2, '.', ',') }}</td>
                    </tr>
                @endif
                @if($showExtraKm)
                    <tr>
                        <td colspan="4" class="text-right" style="padding: 8px 10px;">Km extra ({{ number_format($quotation->extra_km_distance, 2) }} km)</td>
                        <td class="text-center">+ $ {{ number_format($quotation->extra_km_cost, 2, '.', ',') }}</td>
                    </tr>
                @endif
                <tfoot>
                    <tr>
                        <td colspan="4"></td>
                        <td class="text-center"><strong>$ {{ number_format($quotation->price, 2, '.', ',') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <br>
        <div id="thanks">Gracias por solicitar tu Cotizacion.</div>
        <div id="notices">
            <div>Vibeer</div>
            <div class="notice">Saludos.</div>
        </div>
    </main>
    <footer>
        Invoice was created on a computer and is valid without the signature and seal.
    </footer>
</body>
</html>

<style>
    .clearfix:after {
        content: "";
        display: table;
        clear: both;
    }

    a {
        color: #0087C3;
        text-decoration: none;
    }

    body {
        position: relative;
        width: 21cm;
        height: 29.7cm;
        margin: 0 auto;
        color: #555555;
        background: #FFFFFF;
        font-family: sans-serif;
        font-size: 16px;

    }

    header {
        padding: 10px 0;
        margin-bottom: 20px;
        border-bottom: 1px solid #AAAAAA;
    }

    #logo {
        float: left;
        margin-top: 8px;
    }

    #logo img {
        height: 70px;
    }

    #company {
        float: right;
        text-align: right;
    }

    #details {
        margin-bottom: 50px;
    }

    #client {
        padding-left: 6px;
        border-left: 6px solid #0087C3;
        float: left;
    }

    #client .to {
        color: #777777;
    }

    h2.name {
        font-size: 1.4em;
        font-weight: normal;
        margin: 0;
    }

    #invoice {
        float: right;
        text-align: right;
    }

    #invoice h1 {
        color: #0087C3;
        font-size: 2.4em;
        line-height: 1em;
        font-weight: normal;
        margin: 0 0 10px 0;
    }

    #invoice .date {
        font-size: 1.1em;
        color: #777777;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #ccc;
    }

    td {
        padding: 10px;

    }

    th {
        background-color: #ddd;
        font-weight: bold;
    }

    .items {
        width: 800px;
        margin: 0 auto;
    }

    .items th {
        font-weight: bold;
        background-color: #007bff;
        color: white;
    }

    .items td {
        text-align: center;

    }

    .items td.text-right {
        text-align: right;
    }

    .items thead th {
        background-color: #007bff;
        color: white;
    }

    .bg-primary {
        background-color: #007bff;
    }

    table tfoot td {

        background: #FFFFFF;
        border-bottom: none;
        font-size: 1.2em;
        white-space: nowrap;
        border-top: 1px solid #AAAAAA;
    }

    table tfoot tr:first-child td {
        border-top: none;
    }

    table tfoot tr:last-child td {
        color: #007bff;
        font-size: 1.4em;
        border-top: 3px solid black;

    }

    table tfoot tr td:first-child {
        border: none;
    }

    #thanks {
        font-size: 2em;
        margin-bottom: 50px;
    }

    #notices {
        padding-left: 6px;
        border-left: 6px solid #0087C3;
    }

    #notices .notice {
        font-size: 1.2em;
    }

    footer {
        color: #777777;
        width: 100%;
        height: 30px;
        position: absolute;
        bottom: 0;
        border-top: 1px solid #AAAAAA;
        padding: 8px 0;
        text-align: center;
    }
</style>
