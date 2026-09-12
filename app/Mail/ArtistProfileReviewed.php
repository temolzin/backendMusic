<?php

namespace App\Mail;

use App\Models\ArtistProfileRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArtistProfileReviewed extends Mailable
{
    use Queueable, SerializesModels;

    public $profileRequest;
    public $status;
    public $frontendUrl;

    public function __construct(ArtistProfileRequest $profileRequest, string $status)
    {
        $this->profileRequest = $profileRequest;
        $this->status = $status;
        $this->frontendUrl = config('app.frontend_url');
    }

    public function build()
    {
        $subject = $this->status === ArtistProfileRequest::APPROVAL_STATUS_ACCEPTED
            ? '¡Tu perfil ya está disponible en Vibeer!'
            : 'Tu solicitud de perfil no fue aprobada - Vibeer';

        return $this->view('emails.artist-profile-reviewed')
                    ->subject($subject);
    }
}
