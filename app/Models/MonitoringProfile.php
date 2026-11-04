<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringProfile extends Model
{
    protected $fillable = ['name', 'check_type', 'interval_seconds', 'timeout_seconds', 'retry_count', 'failure_threshold', 'settings', 'is_active'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'is_active' => 'boolean'];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
