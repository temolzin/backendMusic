<?php

namespace App\Mail;

use App\Models\ArtistSale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventCancelledNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    public $reason;
    public $cancelledBy;
    public $refundAmount;
    public $penaltyAmount;
    public $penaltyPercentage;
    public $recipientType;
    public $isBeforeApproval;
    public $frontendUrl;

    public function __construct(ArtistSale $sale, string $reason, string $cancelledBy, float $refundAmount, float $penaltyAmount, int $penaltyPercentage, string $recipientType, bool $isBeforeApproval = false)
    {
        $this->sale = $sale;
        $this->reason = $reason;
        $this->cancelledBy = $cancelledBy;
        $this->refundAmount = $refundAmount;
        $this->penaltyAmount = $penaltyAmount;
        $this->penaltyPercentage = $penaltyPercentage;
        $this->recipientType = $recipientType;
        $this->isBeforeApproval = $isBeforeApproval;
        $this->frontendUrl = config('app.frontend_url');
    }

    public function build()
    {
        $subject = $this->isBeforeApproval ? 'Solicitud cancelada - Vibeer' : 'Evento cancelado - Vibeer';

        return $this->view('emails.event-cancelled')
                    ->subject($subject);
    }
}
