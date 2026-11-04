<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceType;
use App\Models\Site;
use App\Models\WorkerHeartbeat;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', ['deviceCounts' => Device::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'), 'deviceTypeCounts' => DeviceType::where('is_active', true)->withCount('devices')->orderBy('name')->get(), 'siteCount' => Site::where('is_active', true)->count(), 'recentDevices' => Device::with('site')->latest()->take(6)->get(), 'workers' => WorkerHeartbeat::orderByDesc('last_seen_at')->get()]);
    }
}
