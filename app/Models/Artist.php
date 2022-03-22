<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'members',
        'history',
        'zone',
        'price_hour',
        'image',
        'extra_kilometre',
        'points'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->hasOne(Manager::class);
    }

    public function musicalGenders()
    {
        return $this->belongsToMany(MusicalGender::class);
    }

    public function shoppingCardDetail()
    {
        return $this->hasMany(ShoppingCardDetail::class);
    }

    public function galeryArtists()
    {
        return $this->hasMany(GaleryArtist::class);
    }
}
