<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRefund extends Model
{
  use HasFactory;

  protected $fillable = [
    'event_cancellation_id',
    'customer_id',
    'authorized_by',
    'refund_percentage',
    'refund_amount',
    'openpay_refund_id',
    'status',
  ];

  public function cancellation()
  {
    return $this->belongsTo(EventCancellation::class, 'event_cancellation_id');
  }

  public function customer()
  {
    return $this->belongsTo(User::class, 'customer_id');
  }

  public function authorizedBy()
  {
    return $this->belongsTo(User::class, 'authorized_by');
  }
}
