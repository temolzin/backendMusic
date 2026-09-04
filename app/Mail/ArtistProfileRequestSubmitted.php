<?php

namespace App\Mail;

use App\Models\ArtistProfileRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArtistProfileRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $profileRequest;
    public $frontendUrl;

    public function __construct(ArtistProfileRequest $profileRequest)
    {
        $this->profileRequest = $profileRequest;
        $this->frontendUrl = config('app.frontend_url');
    }

    public function build()
    {
        return $this->view('emails.artist-profile-request-submitted')
                    ->subject('Nueva solicitud de perfil de artista - Vibeer');
    }
}
