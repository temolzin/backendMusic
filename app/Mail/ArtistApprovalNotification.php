<?php

namespace App\Mail;

use App\Models\ArtistSale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArtistApprovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    public $status;
    public $frontendUrl;

    public function __construct(ArtistSale $sale, string $status)
    {
        $this->sale = $sale;
        $this->status = $status;
        $this->frontendUrl = config('app.frontend_url');
    }

    public function build()
    {
        $subject = $this->status === 'accepted'
            ? 'Solicitud de evento aceptada - Vibeer'
            : 'Solicitud de evento rechazada - Vibeer';

        return $this->view('emails.artist-approval-notification')
                    ->subject($subject);
    }
}
