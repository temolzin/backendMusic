<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryShopping extends Model
{
    use HasFactory;

    public function historyShopping()
    {
        return $this->belongsToMany(User::class);
    }
}
