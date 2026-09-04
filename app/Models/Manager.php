<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Manager extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'artist_id',
        'name',
        'phone',
        'email',
    ];

    protected $appends = ['image'];

    public function getImageAttribute()
    {
        return $this->getFirstMediaUrl('manager_image');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('manager_image')->singleFile();
    }

    public function manager()
    {
        return $this->belongsTo(Artist::class);
    }
}
