<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCancellation extends Model
{
    const CANCEL_PENALTY_DAYS_SHORT = 7;
    const CANCEL_PENALTY_DAYS_MEDIUM = 30;

    const PENALTY_SHORT_TERM = 100;
    const PENALTY_MEDIUM_TERM = 50;
    const PENALTY_LONG_TERM = 0;

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

    public function sanction()
    {
        return $this->morphOne(UserSanction::class, 'sanctionable');
    }
}
