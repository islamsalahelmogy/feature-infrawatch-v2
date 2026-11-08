<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    protected $fillable = ['monitoring_run_id', 'recipient_id', 'notification_type', 'status', 'attempts', 'sent_at', 'error_message'];
    protected function casts(): array { return ['sent_at' => 'datetime']; }
}
