<?php

namespace App\Services;

use App\Jobs\SendScheduledOutageSummary;
use App\Models\Device;
use App\Models\MonitoringRun;
use App\Models\MonitoringRunDevice;
use App\Models\MonitoringSchedule;
use Illuminate\Support\Facades\DB;

class MonitoringRunService
{
    public function start(MonitoringSchedule $schedule): MonitoringRun
    {
        return DB::transaction(function () use ($schedule): MonitoringRun {
            $run = MonitoringRun::create(['monitoring_schedule_id' => $schedule->id, 'check_type' => $schedule->check_type, 'status' => 'running', 'started_at' => now()]);
            $deviceIds = Device::where('is_active', true)->whereHas('monitoringProfile', fn ($query) => $query->where('is_active', true)->where('check_type', $schedule->check_type))->pluck('id');
            foreach ($deviceIds as $deviceId) { MonitoringRunDevice::create(['monitoring_run_id' => $run->id, 'device_id' => $deviceId]); }
            $run->update(['expected_devices' => $deviceIds->count()]);
            if ($deviceIds->isEmpty()) { $run->update(['status' => 'completed', 'completed_at' => now()]); }

            return $run;
        });
    }

    public function recordResult(MonitoringRun $run, int $deviceId, int $checkResultId, string $status): void
    {
        DB::transaction(function () use ($run, $deviceId, $checkResultId, $status): void {
            $entry = MonitoringRunDevice::where('monitoring_run_id', $run->id)->where('device_id', $deviceId)->lockForUpdate()->firstOrFail();
            if ($entry->status !== 'pending') { return; }
            $entry->update(['status' => $status, 'check_result_id' => $checkResultId, 'reported_at' => now()]);
            $run->refresh();
            $received = $run->devices()->whereIn('status', ['success', 'failure'])->count();
            $run->update(['received_results' => $received]);
            if ($received < $run->expected_devices) { return; }
            $down = $run->devices()->whereHas('device', fn ($devices) => $devices->where('status', 'down'))->count();
            $run->update(['status' => 'completed', 'down_devices' => $down, 'completed_at' => now()]);
            if ($down > 0) { SendScheduledOutageSummary::dispatch($run->id)->afterCommit(); }
        });
    }
}
