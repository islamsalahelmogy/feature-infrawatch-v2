<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceType;
use App\Models\Site;
use App\Models\WorkerHeartbeat;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(\Illuminate\Http\Request $request): View
    {
        $columns = ['id'=>'monitoring_runs.id','schedule'=>'monitoring_schedules.name','started'=>'started_at','state'=>'status','responded'=>'responded_count','failed'=>'failed_count','pending'=>'pending_count'];
        $runs = \App\Models\MonitoringRun::query()->select('monitoring_runs.*')->leftJoin('monitoring_schedules','monitoring_schedules.id','=','monitoring_runs.monitoring_schedule_id')->with('schedule')->withCount([
            'devices as responded_count'=>fn($q)=>$q->where('status','success'),
            'devices as failed_count'=>fn($q)=>$q->where('status','failure'),
            'devices as pending_count'=>fn($q)=>$q->where('status','pending'),
        ])->orderBy($columns[$request->query('sort')] ?? 'monitoring_runs.id', $request->query('direction', 'desc') === 'asc' ? 'asc':'desc')->orderByDesc('monitoring_runs.id')->paginate(10)->withQueryString();
        view()->share('runs', $runs);
        return view('dashboard', ['deviceCounts' => Device::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'), 'totalDevices' => Device::count(), 'deviceTypeCounts' => DeviceType::where('is_active', true)->withCount('devices')->orderBy('name')->get(), 'siteCount' => Site::where('is_active', true)->count(), 'recentDevices' => Device::with(['site', 'type'])->orderBy('name')->take(10)->get(), 'workers' => WorkerHeartbeat::orderByDesc('last_seen_at')->get()]);
    }
}
