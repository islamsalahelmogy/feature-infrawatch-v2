<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckResult extends Model
{
    protected $fillable = ['device_id', 'check_type', 'status', 'response_time_ms', 'packet_loss_percent', 'error_message', 'payload', 'checked_at'];

    protected function casts(): array
    {
        return ['response_time_ms' => 'decimal:2', 'packet_loss_percent' => 'decimal:2', 'payload' => 'array', 'checked_at' => 'datetime'];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
