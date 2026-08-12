<!DOCTYPE html>
<html lang="es">
@php use Carbon\Carbon; @endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu cuenta ha sido bloqueada temporalmente</title>
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
        .highlight { background: #ffebee; border-left: 4px solid #e53935; padding: 12px 16px; margin: 16px 0; border-radius: 4px; font-size: 14px; color: #b71c1c; }
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
                            <h1 class="newsletter-title" style="margin-top: 12px;">Tu cuenta ha sido bloqueada</h1>
                            <p style="font-size: 16px; margin-bottom: 4px;">Hola {{ $user->name }},</p>
                            <p class="newsletter-subtitle">Detectamos un comportamiento que incumple nuestras políticas.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="highlight">
                                    <strong>Importante:</strong> Tu cuenta ha sido bloqueada temporalmente mientras revisamos tu actividad.
                                </div>

                                <div class="info-grid" style="margin-top: 16px;">
                                    <div class="info-row">
                                        <span class="info-label">Inicio del bloqueo:</span>
                                        <span class="info-value">{{ $blockedAt ? Carbon::parse($blockedAt)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY, [a las] HH:mm') : Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY, [a las] HH:mm') }}</span>
                                    </div>
                                    @if ($endsAt)
                                        <div class="info-row">
                                            <span class="info-label">Fin del bloqueo:</span>
                                            <span class="info-value">{{ Carbon::parse($endsAt)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY, [a las] HH:mm') }}</span>
                                        </div>
                                    @endif
                                    @if ($isArtist)
                                        <div class="info-row">
                                            <span class="info-label">Perfil público:</span>
                                            <span class="info-value">Mientras dure el bloqueo, tu cuenta de artista no estará a la vista del público.</span>
                                        </div>
                                    @endif
                                </div>

                                <p style="font-size: 14px; color: var(--gsm-text); line-height: 1.6; margin-top: 20px;">
                                    A partir de este momento, tu cuenta ya no podrá utilizarse para acceder a los servicios de Vibeer.
                                </p>

                                <div class="info-row" style="background-color: #fdecea; padding: 14px; border-radius: 8px; border-left: 4px solid #d32f2f; border-bottom: none; margin-top: 16px;">
                                    <span class="info-value" style="color: #d32f2f; line-height: 1.5;">
                                        <strong style="display: block; margin-bottom: 4px;">Motivo del bloqueo:</strong>
                                        {{ $reason ?: 'No se especificó un motivo. Contacta a soporte de Vibeer para más información.' }}
                                    </span>
                                </div>

                                <p style="font-size: 14px; color: var(--gsm-text); line-height: 1.6; margin-top: 20px;">
                                    Si consideras que esto es un error, nuestro equipo de soporte está disponible para revisar tu caso.
                                </p>

                                <div style="margin-top: 28px; text-align: center;">
                                    <a href="{{ $frontendUrl }}" target="_blank" class="btn btn-primary" style="color: #fff; background: #094FAB;">
                                        Ir a Vibeer
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque tu cuenta fue bloqueada en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>