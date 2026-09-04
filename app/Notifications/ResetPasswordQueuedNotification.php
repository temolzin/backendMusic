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

        $appUrl = env('FRONTEND_APP');

        return (new MailMessage)
            ->subject('Recuperación de contraseña')
            ->view('emails.password-reset', [
                'resetUrl' => $resetUrl,
                'user' => $notifiable,
                'frontendUrl' => $appUrl,
            ]);
    }
}
