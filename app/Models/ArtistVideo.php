<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistVideo extends Model
{
    protected $fillable = ['artist_id', 'youtube_url', 'title', 'thumbnail'];
}
