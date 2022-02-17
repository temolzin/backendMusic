<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCardDetail extends Model
{
    use HasFactory;

    public function shoppingCard()
    {
        return $this->belongsTo(ShoppingCard::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}
