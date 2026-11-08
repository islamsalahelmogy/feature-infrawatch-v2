<?php

namespace App\Http\Controllers;

use App\Models\MonitoringRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RunReportController extends Controller
{
    public function show(Request $request, MonitoringRun $run)
    {
        $columns = ['device' => 'devices.name', 'ip' => 'devices.management_ip', 'type' => 'device_types.name', 'site' => 'sites.name', 'status' => 'monitoring_run_devices.status', 'latency' => 'check_results.response_time_ms', 'time' => 'check_results.checked_at'];
        $sort = array_key_exists($request->query('sort', 'device'), $columns) ? $request->query('sort', 'device') : 'device';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
        $counts = $run->devices()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $results = DB::table('monitoring_run_devices')->where('monitoring_run_id', $run->id)
            ->leftJoin('devices', 'devices.id', '=', 'monitoring_run_devices.device_id')
            ->leftJoin('device_types', 'device_types.id', '=', 'devices.device_type_id')
            ->leftJoin('sites', 'sites.id', '=', 'devices.site_id')
            ->leftJoin('check_results', 'check_results.id', '=', 'monitoring_run_devices.check_result_id')
            ->select('monitoring_run_devices.status', 'devices.name', 'devices.management_ip', 'device_types.name as type', 'sites.name as site', 'check_results.response_time_ms', 'check_results.checked_at', 'check_results.error_message')
            ->orderBy($columns[$sort], $direction)->orderBy('monitoring_run_devices.id')->paginate(25)->withQueryString();

        return view('reports.run', ['run' => $run->load('schedule'), 'counts' => $counts, 'results' => $results]);
    }
}
