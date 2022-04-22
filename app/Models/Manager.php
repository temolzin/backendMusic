<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'name',
        'phone',
        'email',
        'image',
    ];

    public function manager()
    {
        return $this->belongsTo(Artist::class);
    }
}
