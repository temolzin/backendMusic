<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSanction extends Model
{
    use HasFactory;

    const TYPE_RESTRICTED = 'restricted';
    const CREATOR_SYSTEM = 'system';
    const CREATOR_ADMIN = 'admin';

    protected $fillable = [
        'user_id',
        'sanctionable_type',
        'sanctionable_id',
        'type',
        'reason',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getSourceTextAttribute()
    {
        if (!$this->sanctionable_type) {
            return 'Sanción Manual Directa';
        }

        return match($this->sanctionable_type) {
            SupportTicket::class => 'Ticket de Soporte',
            EventCancellation::class => 'Cancelación de Evento',
            ArtistSale::class => 'Solicitud Expirada',
            default => 'Sanción Manual Directa',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sanctionable()
    {
        return $this->morphTo();
    }
}
