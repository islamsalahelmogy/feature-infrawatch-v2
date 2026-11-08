<?php
namespace Tests\Feature;

use App\Models\MonitoringRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RunReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_links_to_paginated_report_and_requires_login(): void
    {
        $run = MonitoringRun::create(['check_type'=>'icmp','status'=>'completed','expected_devices'=>0,'started_at'=>now(),'completed_at'=>now()]);
        $this->get('/reports/runs/'.$run->id)->assertRedirect('/login');
        $this->actingAs(User::factory()->create());
        $this->get('/dashboard?sort=id&direction=asc')->assertOk()->assertSee('Scheduled test log')->assertSee('/reports/runs/'.$run->id);
        $this->get('/reports/runs/'.$run->id.'?sort=status&direction=desc')->assertOk()->assertSee('Ping report #'.$run->id)->assertSee('This test has no devices.');
        $this->get('/reports/runs/'.$run->id.'?sort=invalid')->assertOk();
    }
}
