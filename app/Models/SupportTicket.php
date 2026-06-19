<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'artist_sale_id',
        'reporter_user_id',
        'category',
        'description',
        'status',
        'resolution_type',
    ];

    public function artistSale()
    {
        return $this->belongsTo(ArtistSale::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function evidences()
    {
        return $this->hasMany(TicketEvidence::class);
    }
}
