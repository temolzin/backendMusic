<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutLog extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public const TABLE_PAYOUTS_LOGS = 'payouts_logs';
    protected $table = self::TABLE_PAYOUTS_LOGS;

    protected $fillable = [
        'sale_id',
        'artist_id',
        'user_id',
        'amount',
        'openpay_fee_applied',
    ];

    public function sale()
    {
        return $this->belongsTo(ArtistSale::class, 'sale_id');
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    public function administrator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
