<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringRun extends Model
{
    protected $fillable = ['monitoring_schedule_id', 'check_type', 'status', 'expected_devices', 'received_results', 'down_devices', 'started_at', 'completed_at'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function schedule(): BelongsTo { return $this->belongsTo(MonitoringSchedule::class, 'monitoring_schedule_id'); }
    public function devices(): HasMany { return $this->hasMany(MonitoringRunDevice::class); }
}
