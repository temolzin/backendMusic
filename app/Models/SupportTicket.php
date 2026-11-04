<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupportTicket extends Model implements HasMedia
{
    use InteractsWithMedia;

    const STATUS_OPEN = 'open';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_REJECTED = 'rejected';

    const CATEGORY_NO_SHOW = 'no_show';
    const CATEGORY_DELAY = 'delay';
    const CATEGORY_BAD_SERVICE = 'bad_service';
    const CATEGORY_CANCELLATION = 'cancellation';
    const CATEGORY_OTHER = 'other';

    const CATEGORIES_CUSTOMER = [
        self::CATEGORY_NO_SHOW,
        self::CATEGORY_DELAY,
        self::CATEGORY_BAD_SERVICE,
        self::CATEGORY_CANCELLATION,
        self::CATEGORY_OTHER,
    ];

    const CATEGORIES_ARTIST = [
        self::CATEGORY_NO_SHOW,
        self::CATEGORY_BAD_SERVICE,
        self::CATEGORY_CANCELLATION,
        self::CATEGORY_OTHER,
    ];

    const RESOLUTION_TYPES = ['full_refund', 'partial_refund', 'no_action'];

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
        return $this->hasMany(TicketLog::class)->with('changedBy')->orderBy('created_at', 'asc');
    }

    public function sanction()
    {
        return $this->morphOne(UserSanction::class, 'sanctionable');
    }
}
