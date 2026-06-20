<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotations extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'event_date',
        'event_hours',
        'city',
        'address',
        'phone',
        'email',
        'full_name',
        'price',
        'base_price',
        'discount_percentage',
        'discount_amount',
        'latitude',
        'longitude',
        'google_place_id',
        'extra_km_distance',
        'extra_km_cost'
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
    public function musical_gender()
    {
        return $this->belongsTo(MusicalGender::class);
    }

}
