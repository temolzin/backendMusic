<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $status === 'accepted' ? 'Solicitud aceptada' : 'Solicitud rechazada' }}</title>
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
                            <h1 class="newsletter-title" style="margin-top: 12px;">
                                {{ $status === 'accepted' ? '¡Felicidades!' : 'Tu solicitud no fue aprobada' }}
                            </h1>
                            <p class="newsletter-subtitle">
                                @if ($status === 'accepted')
                                    Tu perfil como {{ $profileRequest->proposed_data['name'] }} ya está disponible en la tienda de Vibeer.
                                @else
                                    Revisamos los datos de {{ $profileRequest->proposed_data['name'] }} y encontramos algo que corregir.
                                @endif
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                @if ($status === 'accepted')
                                    <p style="text-align: center; color: var(--gsm-text); font-size: 15px;">
                                        Ya puedes ser contratado por los clientes que visiten la tienda. ¡Éxito con tus eventos!
                                    </p>
                                    <div style="margin-top: 24px; text-align: center;">
                                        <a href="{{ $frontendUrl }}/artist-list" target="_blank" class="btn btn-primary" style="color: #fff; background: #094FAB;">
                                            Ver tienda
                                        </a>
                                    </div>
                                @else
                                    <div class="info-row" style="background-color: #fdecea; padding: 12px; border-radius: 8px; border-left: 4px solid #d32f2f; border-bottom: none;">
                                        <span class="info-value" style="color: #d32f2f;">
                                            {{ $profileRequest->rejection_reason ?: 'No se especificó un motivo. Contacta a soporte de Vibeer para más información.' }}
                                        </span>
                                    </div>
                                    <p style="text-align: center; color: var(--gsm-text); font-size: 14px; margin-top: 16px;">
                                        Corrige tu información y vuelve a enviar tu solicitud, con gusto la revisamos de nuevo.
                                    </p>
                                    <div style="margin-top: 24px; text-align: center;">
                                        <a href="{{ $frontendUrl }}/artist/index" target="_blank" class="btn btn-primary" style="color: #fff; background: #094FAB;">
                                            Corregir mi perfil
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque enviaste una solicitud de perfil de artista en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
