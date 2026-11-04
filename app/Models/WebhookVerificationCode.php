<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookVerificationCode extends Model
{
    protected $fillable = [
        'verification_code',
        'event_id',
        'event_date',
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];
}
