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
        'openpay_sandbox_mode',
    ];

    protected $casts = [
        'openpay_id' => 'encrypted',
        'openpay_secret' => 'encrypted',
        'openpay_public_key' => 'encrypted',
        'openpay_sandbox_mode' => 'boolean',
    ];
}
