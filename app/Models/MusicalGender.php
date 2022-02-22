<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MusicalGender extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'create_at'
    ];

    public function musicalGender()
    {
        return $this->belongsToMany(MusicalGender::class);
    }

}