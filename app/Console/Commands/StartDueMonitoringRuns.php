<?php

namespace App\Console\Commands;

use App\Models\MonitoringSchedule;
use App\Services\MonitoringRunService;
use Illuminate\Console\Command;

class StartDueMonitoringRuns extends Command
{
    protected $signature = 'infrawatch:start-due-runs';
    protected $description = 'Create monitoring runs that are due.';

    public function handle(MonitoringRunService $runs): int
    {
        MonitoringSchedule::where('is_active', true)->where(fn ($query) => $query->whereNull('next_run_at')->orWhere('next_run_at', '<=', now()))->each(function (MonitoringSchedule $schedule) use ($runs): void {
            if ($schedule->runs()->where('status', 'running')->exists()) { return; }
            $runs->start($schedule);
            $schedule->update(['next_run_at' => $schedule->nextOccurrence()]);
        });

        return self::SUCCESS;
    }
}
