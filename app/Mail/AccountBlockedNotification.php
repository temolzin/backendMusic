<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountBlockedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $reason;
    public $blockedAt;
    public $endsAt;
    public $isArtist;
    public $frontendUrl;

    public function __construct(User $user, string $reason, $blockedAt = null, $endsAt = null)
    {
        $this->user = $user;
        $this->reason = $reason;
        $this->blockedAt = $blockedAt;
        $this->endsAt = $endsAt;
        $this->isArtist = (bool) $user->hasRole(User::ROLE_ARTIST);
        $this->frontendUrl = config('app.frontend_url');
    }

    public function build()
    {
        return $this->view('emails.account-blocked')
                    ->subject('Tu cuenta ha sido bloqueada temporalmente - Vibeer');
    }
}