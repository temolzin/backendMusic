<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketEvidence extends Model
{
    protected $table = 'ticket_evidences'; 
    
    protected $fillable = ['support_ticket_id', 'file_path'];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class);
    }
}
