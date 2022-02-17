<?php

namespace App\Models;

use App\Models\Traits\HasPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{

    use HasApiTokens, HasFactory, Notifiable, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    public function clients()
    {
       return $this->hasMany(Client::class);
    }

    public function historyShoppings()
    {
       return $this->belongsToMany(HistoryShopping::class);
    }

    public function images()
    {
        return $this->hasOne(Image::class);
    }

    public function ShoppingsCards()
    {
        return $this->hasOne(User::class);
    }

}
