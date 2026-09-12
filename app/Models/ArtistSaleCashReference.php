<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistSaleCashReference extends Model
{
    protected $fillable = [
        'artist_sale_id',
        'cash_reference',
        'cash_barcode_url',
        'cash_due_date',
    ];

    protected $casts = [
        'cash_due_date' => 'datetime',
    ];

    public function artistSale()
    {
        return $this->belongsTo(ArtistSale::class);
    }
}
