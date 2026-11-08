<?php

namespace App\Jobs;

use App\Mail\ScheduledOutageSummary;
use App\Models\MonitoringRun;
use App\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendScheduledOutageSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $runId) {}
    public function handle(): void
    {
        $run = MonitoringRun::with(['schedule', 'devices.device.site'])->findOrFail($this->runId);
        $downDevices = $run->devices->filter(fn ($entry) => $entry->device?->status === 'down');
        if ($downDevices->isEmpty()) { return; }
        $recipients = User::where('is_active', true)->whereHas('roles', fn ($query) => $query->where('slug', 'administrator'))->get();
        foreach ($recipients as $recipient) {
            $delivery = NotificationDelivery::firstOrCreate(['monitoring_run_id' => $run->id, 'recipient_id' => $recipient->id, 'notification_type' => 'scheduled_outage_summary'], ['status' => 'pending']);
            if ($delivery->status === 'sent') { continue; }
            try { Mail::to($recipient)->send(new ScheduledOutageSummary($run, $downDevices)); $delivery->update(['status' => 'sent', 'attempts' => $delivery->attempts + 1, 'sent_at' => now(), 'error_message' => null]); }
            catch (\Throwable $exception) { $delivery->update(['status' => 'failed', 'attempts' => $delivery->attempts + 1, 'error_message' => str($exception->getMessage())->limit(2000)]); throw $exception; }
        }
    }
}
