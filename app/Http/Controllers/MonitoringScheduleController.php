<?php

namespace App\Http\Controllers;

use App\Models\MonitoringSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MonitoringScheduleController extends Controller
{
    public function index(): View
    {
        return view('schedules.index', ['schedules' => MonitoringSchedule::orderBy('name')->paginate(25)]);
    }

    public function create(): View
    {
        return view('schedules.form', ['schedule' => new MonitoringSchedule(['check_type' => 'icmp', 'interval_minutes' => 5, 'timezone' => 'Africa/Cairo', 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        MonitoringSchedule::create($this->validated($request));

        return redirect()->route('monitoring-schedules.index')->with('status', 'Monitoring schedule added.');
    }

    public function edit(MonitoringSchedule $monitoringSchedule): View
    {
        return view('schedules.form', ['schedule' => $monitoringSchedule]);
    }

    public function update(Request $request, MonitoringSchedule $monitoringSchedule): RedirectResponse
    {
        $monitoringSchedule->update($this->validated($request));

        return redirect()->route('monitoring-schedules.index')->with('status', 'Monitoring schedule updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'times' => ['required', 'array', 'min:1', 'max:24'],
            'times.*' => ['required', 'date_format:H:i', 'distinct'],
            'weekday' => ['exclude_unless:frequency,weekly', 'required', 'integer', 'between:0,6'],
            'monthday' => ['exclude_unless:frequency,monthly', 'required', 'integer', 'between:1,31'],
            'timezone' => ['required', 'timezone'], 'is_active' => ['required', 'boolean'],
        ]);
        $data['check_type'] = 'icmp';
        $data['weekday'] = $data['weekday'] ?? null;
        $data['monthday'] = $data['monthday'] ?? null;
        $data['next_run_at'] = (new MonitoringSchedule($data))->nextOccurrence();
        return $data;
    }
}
