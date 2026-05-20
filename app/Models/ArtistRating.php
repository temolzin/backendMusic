<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistRating extends Model
{
    protected $fillable = ['user_id', 'artist_id', 'rating'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}
