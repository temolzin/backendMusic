<?php

namespace App\Models;

use App\Models\Traits\HasPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordQueuedNotification;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail, HasMedia
{

    use HasApiTokens, HasFactory, Notifiable, HasPermissions, InteractsWithMedia;

    public const ROLE_ADMIN = 'administrador';
    public const ROLE_ARTIST = 'artista';
    public const ROLE_CLIENT = 'cliente';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dark_mode',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'latitude',
        'longitude',
        'account_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['image_profile'];

    public function getImageProfileAttribute()
    {
        return $this->getFirstMediaUrl('profile_images');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_images')
            ->singleFile();
    }

    // JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    // End JWT

    // Relationships 
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function hasRole(...$roles)
    {
        // $user->hasRole('admin', 'developer');
        return $this->roles()->whereIn('slug', $roles)->count();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions');
    }

    public function artists()
    {
       return $this->hasOne(Artist::class);
    }

    public function cards()
    {
       return $this->hasMany(Card::class);
    }

    public function historyShoppings()
    {
       return $this->belongsToMany(HistoryShopping::class);
    }

    public function ShoppingsCards()
    {
        return $this->hasOne(User::class);
    }

    public function favouriteArtists()
    {
       return $this->hasMany(FavouriteArtists::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordQueuedNotification($token));
    }

    public function sanctions()
    {
        return $this->hasMany(UserSanction::class);
    }

}
