<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class AdminPasswordChangedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetUrl;
    public $frontendUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        $token = Password::broker()->createToken($user);
        $this->resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], true);
        $this->frontendUrl = config('app.frontend_url');
    }

    public function build()
    {
        return $this->view('emails.admin-password-changed')
                    ->subject('Tu contraseña ha sido cambiada - Vibeer');
    }
}
