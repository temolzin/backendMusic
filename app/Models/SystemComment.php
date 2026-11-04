<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemComment extends Model
{
    public const FILTER_ALL = 'todos';
    public const FILTER_GOOD = 'buenos';
    public const FILTER_BAD = 'bajos';

    protected $fillable = [
        'user_id',
        'body',
        'rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
