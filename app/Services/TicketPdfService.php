<?php

namespace App\Services;

use App\Models\ArtistSale;
use Barryvdh\DomPDF\PDF;

class TicketPdfService
{
    protected $pdf;

    public function __construct(PDF $pdf)
    {
        $this->pdf = $pdf;
    }

    public function generate(ArtistSale $sale): PDF
    {
        $sale->loadMissing(['artist.musicalGenders', 'cashReference']);

        $this->pdf->loadView('pdf.ticket', [
            'sale' => $sale,
        ]);

        $this->pdf->setPaper([0, 0, 340, 800], 'portrait');

        return $this->pdf;
    }

    public function download(ArtistSale $sale)
    {
        $pdf = $this->generate($sale);
        $ticketNumber = str_pad($sale->id, 8, '0', STR_PAD_LEFT);
        return $pdf->download("ticket-vibeer-{$ticketNumber}.pdf");
    }

    public function output(ArtistSale $sale)
    {
        $pdf = $this->generate($sale);
        return $pdf->output();
    }
}
