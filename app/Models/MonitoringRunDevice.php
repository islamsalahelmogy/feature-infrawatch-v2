<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringRunDevice extends Model
{
    protected $fillable = ['monitoring_run_id', 'device_id', 'status', 'check_result_id', 'reported_at'];
    protected function casts(): array { return ['reported_at' => 'datetime']; }
    public function run(): BelongsTo { return $this->belongsTo(MonitoringRun::class, 'monitoring_run_id'); }
    public function device(): BelongsTo { return $this->belongsTo(Device::class); }
}
