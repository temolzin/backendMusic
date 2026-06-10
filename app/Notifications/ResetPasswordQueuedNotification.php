<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordQueuedNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ], true);

        return (new MailMessage)
            ->subject('Recuperación de contraseña')
            ->greeting('¡Hola!')
            ->line('Recibimos una solicitud para restablecer tu contraseña de Vibeer.')
            ->action('Cambiar contraseña', $resetUrl)
            ->line('Este enlace expirará en 60 minutos.')
            ->line('Si no realizaste esta solicitud, puedes ignorar este correo.')
            ->salutation('Saludos, Vibeer');
    }
}
