<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringSchedule extends Model
{
    protected $fillable = ['name', 'check_type', 'interval_minutes', 'timezone', 'is_active', 'next_run_at', 'frequency', 'times', 'weekday', 'monthday'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'next_run_at' => 'datetime', 'times' => 'array'];
    }

    public function nextOccurrence(): \Carbon\Carbon
    {
        if (! $this->frequency) { return now()->addMinutes($this->interval_minutes); }
        $now = now()->timezone($this->timezone);
        $times = $this->times;
        sort($times);
        for ($offset = 0; $offset <= 370; $offset++) {
            $day = $now->copy()->startOfDay()->addDays($offset);
            if ($this->frequency === 'weekly' && $day->dayOfWeek != $this->weekday) { continue; }
            if ($this->frequency === 'monthly' && $day->day != $this->monthday) { continue; }
            foreach ($times as $time) {
                $candidate = $day->copy()->setTimeFromTimeString($time);
                if ($candidate->gt($now)) { return $candidate->utc(); }
            }
        }
        throw new \LogicException('No scheduled time found.');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(MonitoringRun::class);
    }
}
