<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupportTicket extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'artist_sale_id',
        'reporter_user_id',
        'category',
        'description',
        'status',
        'resolution_type',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('ticket_evidences');
    }

    public function artistSale()
    {
        return $this->belongsTo(ArtistSale::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class)->with('changedBy')->latest();
    }
}
