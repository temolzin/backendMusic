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
        'price'
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
