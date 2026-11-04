<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MusicalGender extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
    ];

    protected $appends = ['image'];

    public function getImageAttribute()
    {
        return $this->getFirstMediaUrl('musical_gender_image');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('musical_gender_image')->singleFile();
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class);
    }
}
