<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $subject }}</title>
        <style>
            {{ file_get_contents(resource_path('css/app.css')) }}
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
                                <p class="newsletter-kicker">Vibeer</p>
                                <h1 class="newsletter-title">{{ $subject }}</h1>
                                <p class="newsletter-subtitle">Novedades, avisos y contenido especial para tu comunidad.</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="newsletter-content">
                                <div class="newsletter-body">
                                    {!! nl2br(e($content)) !!}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="newsletter-footer">
                                <p>Recibiste este correo porque formas parte de la comunidad de Vibeer.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
