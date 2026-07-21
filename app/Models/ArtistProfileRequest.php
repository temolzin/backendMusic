<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ArtistProfileRequest extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    const TYPE_CREATION = 'creation';
    const TYPE_UPDATE = 'update';

    const APPROVAL_STATUS_PENDING = 'pending_approval';
    const APPROVAL_STATUS_ACCEPTED = 'accepted';
    const APPROVAL_STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'artist_id',
        'request_type',
        'proposed_data',
        'approval_status',
        'rejection_reason',
        'reviewed_at',
        'authorized_by',
    ];

    protected $casts = [
        'proposed_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = ['image_artist_url', 'image_manager_url'];

    public function getImageArtistUrlAttribute()
    {
        return $this->getFirstMediaUrl('pending_artist_image');
    }

    public function getImageManagerUrlAttribute()
    {
        return $this->getFirstMediaUrl('pending_manager_image');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pending_artist_image')->singleFile();
        $this->addMediaCollection('pending_manager_image')->singleFile();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
