<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Quotations;

class QuotationCreated extends Mailable
{
    use Queueable, SerializesModels;
    public $quotation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Quotations $quotation)
    {
        $this->quotation = $quotation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.quotation-created')
                    ->subject('Detalles de la Cotización');
    }
}
