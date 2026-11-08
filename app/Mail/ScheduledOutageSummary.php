<?php

namespace App\Mail;

use App\Models\MonitoringRun;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ScheduledOutageSummary extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public MonitoringRun $run, public Collection $downDevices) {}
    public function build(): self
    {
        return $this->subject("InfraWatch: {$this->downDevices->count()} device(s) down in scheduled run #{$this->run->id}")->view('mail.scheduled-outage-summary');
    }
}
