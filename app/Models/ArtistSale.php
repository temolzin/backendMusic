<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistSale extends Model
{
    const PAYMENT_STATUS_AUTHORIZED = 'authorized';
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_COMPLETED = 'completed';
    const PAYMENT_STATUS_CANCELLED = 'cancelled';
    const PAYMENT_STATUS_LIQUIDATED = 'liquidated';

    const EVENT_STATUS_PENDING = 'pending';
    const EVENT_STATUS_COMPLETED = 'completed';
    const EVENT_STATUS_CANCELLED = 'cancelled';
    const EVENT_STATUS_REJECTED = 'rejected';
    const EVENT_STATUS_EXPIRED = 'expired';

    const APPROVAL_STATUS_PENDING = 'pending_approval';
    const APPROVAL_STATUS_ACCEPTED = 'accepted';
    const APPROVAL_STATUS_REJECTED = 'rejected';
    const APPROVAL_STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'artist_id',
        'offer_id',
        'customer_id',
        'amount',
        'openpay_fee',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_city',
        'customer_state',
        'customer_zip_code',
        'event_date',
        'event_hour',
        'event_hours',
        'event_status',
        'openpay_transaction_id',
        'payment_method',
        'store',
        'latitude',
        'longitude',
        'google_place_id',
        'extra_km_distance',
        'extra_km_cost',
        'approval_status',
        'approval_deadline',
        'approval_responded_at',
        'openpay_customer_id',
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function rating()
    {
        return $this->hasOne(ArtistRating::class);
    }

    public function eventCancellation()
    {
        return $this->hasOne(EventCancellation::class);
    }
}
