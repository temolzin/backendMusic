<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GaleryArtist extends Model
{
    use HasFactory;
    protected $fillable = [
        'artist_id',
        'image',
    ];
    public function artists()
    {
        return $this->belongsTo(Artist::class);
    }
}
