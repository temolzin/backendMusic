<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemComment extends Model
{
    protected $fillable = [
        'user_id',
        'body',
        'rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
