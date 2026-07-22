<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutLog extends Model
{
    use HasFactory;

    protected $table = 'payouts_logs';

    protected $fillable = [
        'sale_id',
        'artist_id',
        'user_id',
        'amount',
    ];

    public function sale()
    {
        return $this->belongsTo(ArtistSale::class, 'sale_id');
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    public function administrator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
