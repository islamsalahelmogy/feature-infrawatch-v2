<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'device_type_id', 'monitoring_profile_id', 'name', 'hostname', 'management_ip', 'mac_address', 'default_gateway', 'subnet_mask', 'ip_assignment', 'status', 'criticality', 'is_active', 'notes'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    public function monitoringProfile(): BelongsTo
    {
        return $this->belongsTo(MonitoringProfile::class);
    }
}
