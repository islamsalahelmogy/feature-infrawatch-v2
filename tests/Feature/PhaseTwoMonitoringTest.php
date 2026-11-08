<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceType;
use App\Models\MonitoringProfile;
use App\Models\MonitoringSchedule;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Services\MonitoringRunService;
use App\Jobs\SendScheduledOutageSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PhaseTwoMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function icmpDevice(): Device
    {
        $site = Site::create(['name' => 'Main Factory', 'code' => 'MAIN', 'timezone' => 'Africa/Cairo']);
        $type = DeviceType::create(['name' => 'Switch', 'slug' => 'switch', 'category' => 'network']);
        $profile = MonitoringProfile::create(['name' => 'Critical ICMP', 'check_type' => 'icmp', 'interval_seconds' => 30, 'timeout_seconds' => 3, 'retry_count' => 1, 'failure_threshold' => 3, 'is_active' => true]);

        return Device::create(['site_id' => $site->id, 'device_type_id' => $type->id, 'monitoring_profile_id' => $profile->id, 'name' => 'Core Switch', 'management_ip' => '192.168.1.2', 'mac_address' => '00:11:22:33:44:55', 'default_gateway' => '192.168.1.4', 'subnet_mask' => '255.255.248.0', 'ip_assignment' => 'static', 'criticality' => 'critical', 'is_active' => true]);
    }

    public function test_worker_endpoints_require_a_token(): void
    {
        $this->postJson('/api/worker/heartbeats', [])->assertUnauthorized();
    }

    public function test_icmp_failures_create_and_success_resolves_an_incident(): void
    {
        config(['infrawatch.worker_token' => 'worker-test-token']);
        $device = $this->icmpDevice();

        foreach (range(1, 3) as $attempt) {
            $this->withHeader('X-Worker-Token', 'worker-test-token')->postJson('/api/worker/icmp-results', ['device_id' => $device->id, 'status' => 'failure', 'packet_loss_percent' => 100, 'error_message' => 'Request timed out'])->assertAccepted();
        }

        $this->assertDatabaseHas('devices', ['id' => $device->id, 'status' => 'down']);
        $this->assertDatabaseHas('incidents', ['device_id' => $device->id, 'check_type' => 'icmp', 'status' => 'open', 'failure_count' => 3]);

        $this->withHeader('X-Worker-Token', 'worker-test-token')->postJson('/api/worker/icmp-results', ['device_id' => $device->id, 'status' => 'success', 'response_time_ms' => 4.5, 'packet_loss_percent' => 0])->assertAccepted();

        $this->assertDatabaseHas('devices', ['id' => $device->id, 'status' => 'up']);
        $this->assertDatabaseHas('incidents', ['device_id' => $device->id, 'status' => 'resolved']);
        $this->assertDatabaseHas('incident_events', ['event_type' => 'recovered']);
    }

    public function test_worker_can_retrieve_only_active_icmp_devices(): void
    {
        config(['infrawatch.worker_token' => 'worker-test-token']);
        $device = $this->icmpDevice();

        $this->withHeader('X-Worker-Token', 'worker-test-token')->getJson('/api/worker/devices')->assertOk()->assertJsonPath('devices.0.id', $device->id)->assertJsonPath('devices.0.ip_address', '192.168.1.2');
    }

    public function test_completed_scheduled_run_sends_one_summary_to_each_active_administrator(): void
    {
        config(['infrawatch.worker_token' => 'worker-test-token']);
        Queue::fake();
        Mail::fake();
        $device = $this->icmpDevice();
        $device->monitoringProfile->update(['failure_threshold' => 1]);
        $role = Role::create(['name' => 'Administrator', 'slug' => 'administrator']);
        $first = User::factory()->create(['email' => 'first@example.test']);
        $second = User::factory()->create(['email' => 'second@example.test']);
        $inactive = User::factory()->create(['email' => 'inactive@example.test', 'is_active' => false]);
        $first->roles()->attach($role);
        $second->roles()->attach($role);
        $inactive->roles()->attach($role);
        $schedule = MonitoringSchedule::create(['name' => 'ICMP every five minutes', 'check_type' => 'icmp', 'interval_minutes' => 5, 'timezone' => 'Africa/Cairo']);
        $run = app(MonitoringRunService::class)->start($schedule);

        $this->withHeader('X-Worker-Token', 'worker-test-token')->postJson('/api/worker/icmp-results', ['monitoring_run_id' => $run->id, 'device_id' => $device->id, 'status' => 'failure', 'packet_loss_percent' => 100, 'error_message' => 'Request timed out'])->assertAccepted();

        $this->assertDatabaseHas('monitoring_runs', ['id' => $run->id, 'status' => 'completed', 'down_devices' => 1]);
        Queue::assertPushed(SendScheduledOutageSummary::class, fn ($job) => $job->runId === $run->id);
        (new SendScheduledOutageSummary($run->id))->handle();
        Mail::assertSent(\App\Mail\ScheduledOutageSummary::class, 2);
        $this->assertDatabaseCount('notification_deliveries', 2);
    }
}
