<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistRating extends Model
{
    protected $fillable = ['artist_sale_id', 'artist_id', 'rating', 'comment'];

    public function artistSale()
    {
        return $this->belongsTo(ArtistSale::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}
