<?php

namespace App\Services;

use App\Models\CheckResult;
use App\Models\Device;
use App\Models\Incident;
use App\Models\IncidentEvent;
use App\Models\MonitoringRun;
use Illuminate\Support\Carbon;

class MonitoringResultService
{
    public function recordIcmp(Device $device, array $data): CheckResult
    {
        $checkedAt = Carbon::parse($data['checked_at'] ?? now());
        $result = CheckResult::create([
            'device_id' => $device->id,
            'check_type' => 'icmp',
            'status' => $data['status'],
            'response_time_ms' => $data['response_time_ms'] ?? null,
            'packet_loss_percent' => $data['packet_loss_percent'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'payload' => $data['payload'] ?? null,
            'checked_at' => $checkedAt,
        ]);

        $this->updateDeviceAndIncident($device, $result);

        if (isset($data['monitoring_run_id'])) {
            app(MonitoringRunService::class)->recordResult(MonitoringRun::findOrFail($data['monitoring_run_id']), $device->id, $result->id, $result->status);
        }

        return $result;
    }

    private function updateDeviceAndIncident(Device $device, CheckResult $result): void
    {
        $threshold = max(1, $device->monitoringProfile?->failure_threshold ?? 3);
        $openIncident = Incident::where('device_id', $device->id)->where('check_type', 'icmp')->whereIn('status', ['open', 'acknowledged'])->latest('id')->first();

        if ($result->status === 'success') {
            $device->update(['status' => 'up']);
            if ($openIncident) {
                $openIncident->update(['status' => 'resolved', 'resolved_at' => $result->checked_at]);
                IncidentEvent::create(['incident_id' => $openIncident->id, 'event_type' => 'recovered', 'message' => 'ICMP reachability recovered.', 'occurred_at' => $result->checked_at]);
            }

            return;
        }

        $failures = CheckResult::where('device_id', $device->id)->where('check_type', 'icmp')->latest('checked_at')->take($threshold)->get();
        $consecutiveFailures = $failures->count() === $threshold && $failures->every(fn (CheckResult $check) => $check->status === 'failure');
        $device->update(['status' => $consecutiveFailures ? 'down' : 'degraded']);

        if (! $consecutiveFailures) {
            return;
        }

        if ($openIncident) {
            $openIncident->increment('failure_count');

            return;
        }

        $incident = Incident::create(['device_id' => $device->id, 'check_type' => 'icmp', 'severity' => $this->severity($device->criticality), 'title' => "ICMP unavailable: {$device->name}", 'summary' => $result->error_message, 'failure_count' => $threshold, 'opened_at' => $result->checked_at]);
        IncidentEvent::create(['incident_id' => $incident->id, 'event_type' => 'opened', 'message' => "{$threshold} consecutive ICMP failures detected.", 'context' => ['result_id' => $result->id], 'occurred_at' => $result->checked_at]);
    }

    private function severity(string $criticality): string
    {
        return match ($criticality) {
            'critical' => 'critical',
            'high' => 'high',
            'medium' => 'medium',
            default => 'low',
        };
    }
}
