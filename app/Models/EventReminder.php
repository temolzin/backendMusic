<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReminder extends Model
{
    const LAPSE_24H = '24h';
    const LAPSE_30MIN = '30min';

    protected $fillable = [
        'artist_sale_id',
        'lapse',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function artistSale(): BelongsTo
    {
        return $this->belongsTo(ArtistSale::class);
    }
}
