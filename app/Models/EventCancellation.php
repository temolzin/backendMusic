<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCancellation extends Model
{
    protected $fillable = [
        'artist_sale_id',
        'user_id',
        'cancellation_reason',
        'penalty_percentage',
        'penalty_amount',
        'refunded_at',
        'penalty_paid',
    ];

    protected $casts = [
        'penalty_paid' => 'boolean',
        'refunded_at' => 'datetime',
        'penalty_percentage' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
    ];

    public function artistSale()
    {
        return $this->belongsTo(ArtistSale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
