<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    protected $fillable = ['device_id', 'check_type', 'severity', 'status', 'title', 'summary', 'failure_count', 'opened_at', 'acknowledged_at', 'resolved_at'];

    protected function casts(): array
    {
        return ['opened_at' => 'datetime', 'acknowledged_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(IncidentEvent::class);
    }
}
