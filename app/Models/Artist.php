<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Offer;

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
        'coverage_radius',
        'points',
        'social_media'
    ];

    protected $casts = [
        'social_media' => 'array',
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

    public function favouriteArtists()
    {
        return $this->hasMany(FavouriteArtists::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotations::class);
    }

    public function ratings()
    {
        return $this->hasMany(ArtistRating::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function payoutMethod()
    {
        return $this->hasOne(ArtistPayoutMethod::class);
    }
}
