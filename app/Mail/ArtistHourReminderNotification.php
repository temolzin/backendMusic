<?php

namespace App\Mail;

use App\Models\ArtistSale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArtistHourReminderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    public $frontendUrl;

    public function __construct(ArtistSale $sale)
    {
        $this->sale = $sale;
        $this->frontendUrl = config('app.frontend_url');
    }

    public function build()
    {
        return $this->view('emails.artist-hour-reminder')
                    ->subject('Tu presentación empieza en 30 minutos - Vibeer');
    }
}
