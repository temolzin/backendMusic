<?php

namespace App\Mail;

use App\Models\ArtistSale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventRefundedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    public $refundAmount;
    public $reason;
    public $frontendUrl;

    public function __construct(ArtistSale $sale, $refundAmount = null, string $reason = null)
    {
        $this->sale = $sale;
        $this->refundAmount = $refundAmount ?? $sale->amount;
        $this->reason = $reason;
        $this->frontendUrl = config('app.frontend_url');
    }

    public function build()
    {
        return $this->view('emails.event-refunded-notification')
                    ->subject('Reembolso procesado para tu evento - Vibeer');
    }
}
