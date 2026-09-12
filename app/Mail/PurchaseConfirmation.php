<?php

namespace App\Mail;

use App\Models\ArtistSale;
use App\Services\TicketPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseConfirmation extends Mailable
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
        $ticketNumber = str_pad($this->sale->id, 8, '0', STR_PAD_LEFT);

        $pdf = app(TicketPdfService::class)->output($this->sale);

        return $this->view('emails.purchase-confirmation')
                    ->subject('Gracias por tu compra - Vibeer')
                    ->attachData($pdf, "ticket-vibeer-{$ticketNumber}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
    }
}
