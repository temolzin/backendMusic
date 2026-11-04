<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistPayoutMethod extends Model
{
    use HasFactory;
    protected $fillable = [
        'artist_id',
        'bank_name',
        'account_holder',
        'clabe',
        'rfc',
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
   
}
