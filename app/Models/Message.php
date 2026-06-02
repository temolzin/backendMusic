<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_sale_id',
        'created_by',
        'message',
        'is_read',
    ];

    public function artistSale()
    {
        return $this->belongsTo(ArtistSale::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
