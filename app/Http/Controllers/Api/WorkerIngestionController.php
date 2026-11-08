<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\WorkerHeartbeat;
use App\Models\MonitoringRun;
use App\Services\MonitoringResultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerIngestionController extends Controller
{
    public function devices(): JsonResponse
    {
        $devices = Device::query()
            ->with('monitoringProfile:id,check_type,interval_seconds,timeout_seconds,retry_count,is_active')
            ->where('is_active', true)
            ->whereHas('monitoringProfile', fn ($query) => $query->where('is_active', true)->where('check_type', 'icmp'))
            ->get(['id', 'name', 'management_ip', 'monitoring_profile_id'])
            ->map(fn (Device $device) => ['id' => $device->id, 'name' => $device->name, 'ip_address' => $device->management_ip, 'interval_seconds' => $device->monitoringProfile->interval_seconds, 'timeout_seconds' => $device->monitoringProfile->timeout_seconds, 'retry_count' => $device->monitoringProfile->retry_count]);

        return response()->json(['devices' => $devices]);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $data = $request->validate(['worker_name' => ['required', 'string', 'max:100'], 'status' => ['required', 'in:healthy,degraded,offline'], 'metadata' => ['nullable', 'array'], 'seen_at' => ['nullable', 'date']]);
        $heartbeat = WorkerHeartbeat::updateOrCreate(['worker_name' => $data['worker_name']], ['status' => $data['status'], 'metadata' => $data['metadata'] ?? null, 'last_seen_at' => $data['seen_at'] ?? now()]);

        return response()->json(['id' => $heartbeat->id, 'status' => 'accepted'], 202);
    }

    public function icmpResult(Request $request, MonitoringResultService $service): JsonResponse
    {
        $data = $request->validate(['device_id' => ['required', 'integer', 'exists:devices,id'], 'monitoring_run_id' => ['nullable', 'integer', 'exists:monitoring_runs,id'], 'status' => ['required', 'in:success,failure'], 'response_time_ms' => ['nullable', 'numeric', 'min:0', 'max:120000'], 'packet_loss_percent' => ['nullable', 'numeric', 'min:0', 'max:100'], 'error_message' => ['nullable', 'string', 'max:1000'], 'payload' => ['nullable', 'array'], 'checked_at' => ['nullable', 'date']]);
        $device = Device::with('monitoringProfile')->findOrFail($data['device_id']);
        $result = $service->recordIcmp($device, $data);

        return response()->json(['id' => $result->id, 'status' => 'accepted'], 202);
    }

    public function nextRun(): JsonResponse
    {
        $run = MonitoringRun::query()->where('status', 'running')->with(['devices' => fn ($query) => $query->where('status', 'pending')->with('device.monitoringProfile')])->oldest('started_at')->first();
        if (! $run) { return response()->json(['run' => null]); }
        $devices = $run->devices->filter(fn ($entry) => $entry->device?->monitoringProfile?->check_type === 'icmp')->map(fn ($entry) => ['id' => $entry->device->id, 'name' => $entry->device->name, 'ip_address' => $entry->device->management_ip, 'timeout_seconds' => $entry->device->monitoringProfile->timeout_seconds, 'retry_count' => $entry->device->monitoringProfile->retry_count]);

        return response()->json(['run' => ['id' => $run->id, 'devices' => $devices->values()]]);
    }
}
