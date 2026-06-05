<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenpayKey extends Model
{
    protected $table = 'openpay_keys';

    protected $fillable = [
        'openpay_id',
        'openpay_secret',
        'openpay_public_key',
    ];

    protected $casts = [
        'openpay_sandbox_mode' => 'boolean',
    ];
}
