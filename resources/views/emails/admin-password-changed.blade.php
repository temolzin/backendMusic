<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu contraseña ha sido cambiada</title>
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
                            <h1 class="newsletter-title" style="margin-top: 12px;">Tu contrase&ntilde;a ha sido cambiada</h1>
                            <p class="newsletter-subtitle">Hola {{ $user->name ?? 'usuario' }}, el administrador ha cambiado la contrase&ntilde;a de tu cuenta.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="info-grid">
                                    <div class="info-row">
                                        <span class="info-label">Para establecer tu nueva contrase&ntilde;a, haz clic en el siguiente bot&oacute;n:</span>
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
                                    Si no solicitaste este cambio, por favor comun&iacute;cate con nosotros de inmediato.
                                </p>

                                <p style="text-align: center; color: #666; font-size: 12px; margin-top: 8px;">
                                    Correo: <a href="mailto:info@root.com" style="color: #094FAB;">info@root.com</a> &middot; Tel&eacute;fono: +52 55 43 22 32
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque un administrador cambi&oacute; la contrase&ntilde;a de tu cuenta en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
