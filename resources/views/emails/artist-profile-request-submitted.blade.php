<!DOCTYPE html>
<html lang="es">
@php use Carbon\Carbon; @endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva solicitud de perfil de artista</title>
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
                            <h1 class="newsletter-title" style="margin-top: 12px;">Nueva solicitud de perfil</h1>
                            <p class="newsletter-subtitle">
                                {{ $profileRequest->request_type === 'creation' ? 'Un artista nuevo quiere unirse a Vibeer.' : 'Un artista editó su perfil.' }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-content">
                            <div class="newsletter-body">
                                <div class="info-grid">
                                    <div class="info-row">
                                        <span class="info-label">Artista</span>
                                        <span class="info-value">{{ $profileRequest->proposed_data['name'] }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Cuenta</span>
                                        <span class="info-value">{{ $profileRequest->user->email }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Tipo</span>
                                        <span class="info-value">{{ $profileRequest->request_type === 'creation' ? 'Alta nueva' : 'Edición de perfil' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Zona</span>
                                        <span class="info-value">{{ $profileRequest->proposed_data['zone'] }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Enviado</span>
                                        <span class="info-value">{{ Carbon::parse($profileRequest->created_at)->locale('es')->isoFormat('D [de] MMMM, YYYY [a las] HH:mm') }}</span>
                                    </div>
                                </div>

                                <div style="margin-top: 28px; text-align: center;">
                                    <a href="{{ $frontendUrl }}/admin/artist-approvals" target="_blank" class="btn btn-primary" style="color: #fff; background: #094FAB;">
                                        Revisar solicitud
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="newsletter-footer">
                            <p>Recibiste este correo porque hay una solicitud de perfil de artista esperando revisión en Vibeer.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
