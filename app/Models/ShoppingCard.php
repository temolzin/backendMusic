<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCard extends Model
{
    use HasFactory;
    protected $table = "shoppings_cards";
    protected $fillable = [
        'user_id',
        'status',
        'order_date_start',
        'order_date_finish',
        'total'
    ];

    public function shoppingCardDetail()
    {
        return $this->hasMany(shoppingCardDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
