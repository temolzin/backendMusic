<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de contraseña</title>
    <style>
        {{ file_get_contents(resource_path('css/app.css')) }}
        .btn { display: inline-block; padding: 12px 32px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 15px; text-align: center; }
        .btn-primary { background: #094FAB; color: #fff !important; }
        .info-grid { width: 100%; }
        .info-row { padding: 8px 0; display: flex; align-items: center; justify-content: center; }
        .info-label { font-weight: 700; color: var(--gsm-dark); font-size: 13px; }
        .warning-text { color: #dc3545; font-size: 13px; text-align: center; margin-top: 16px; }
        .expiry-text { color: #666; font-size: 13px; text-align: center; margin-top: 8px; }
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
                            <h1 class="newsletter-title" style="margin-top: 12px;">Recuperaci&oacute;n de contrase&ntilde;a</h1>
                            <p class="newsletter-subtitle">Hola {{ $user->name ?? 'usuario' }}, recibimos una solicitud para restablecer tu contrase&ntilde;a.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="info-grid">
                                    <div class="info-row">
                                        <span class="info-label">Para continuar, haz clic en el siguiente bot&oacute;n:</span>
                                    </div>
                                </div>

                                <div style="margin-top: 28px; text-align: center;">
                                    <a href="{{ $resetUrl }}" target="_blank" class="btn btn-primary" style="color: #fff; background: #094FAB;">
                                        Cambiar contrase&ntilde;a
                                    </a>
                                </div>

                                <p style="text-align: center; color: #666; font-size: 13px; margin-top: 8px;">
                                    Este enlace expirar&aacute; en <strong>60 minutos</strong>.
                                </p>

                                <p class="warning-text">
                                    Si no realizaste esta solicitud, puedes ignorar este correo.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque solicitaste restablecer tu contrase&ntilde;a en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
