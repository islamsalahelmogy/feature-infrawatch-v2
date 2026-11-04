<?php

namespace App\Http\Controllers;

use App\Models\MonitoringProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MonitoringProfileController extends Controller
{
    public function index(): View
    {
        return view('profiles.index', ['profiles' => MonitoringProfile::orderBy('name')->paginate(20)]);
    }

    public function create(): View
    {
        return view('profiles.form', ['profile' => new MonitoringProfile(['interval_seconds' => 60, 'timeout_seconds' => 5, 'retry_count' => 2, 'failure_threshold' => 3, 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        MonitoringProfile::create($this->validated($request));

        return redirect()->route('monitoring-profiles.index')->with('status', 'Monitoring profile created.');
    }

    public function edit(MonitoringProfile $monitoringProfile): View
    {
        return view('profiles.form', ['profile' => $monitoringProfile]);
    }

    public function update(Request $request, MonitoringProfile $monitoringProfile): RedirectResponse
    {
        $monitoringProfile->update($this->validated($request));

        return redirect()->route('monitoring-profiles.index')->with('status', 'Monitoring profile updated.');
    }

    public function destroy(MonitoringProfile $monitoringProfile): RedirectResponse
    {
        abort_if($monitoringProfile->devices()->exists(), 422, 'A profile assigned to devices cannot be deleted.');
        $monitoringProfile->delete();

        return redirect()->route('monitoring-profiles.index')->with('status', 'Monitoring profile deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:100'], 'check_type' => ['required', Rule::in(['icmp', 'snmp', 'windows', 'fortigate'])], 'interval_seconds' => ['required', 'integer', 'min:30', 'max:86400'], 'timeout_seconds' => ['required', 'integer', 'min:1', 'max:120'], 'retry_count' => ['required', 'integer', 'min:0', 'max:10'], 'failure_threshold' => ['required', 'integer', 'min:1', 'max:20'], 'is_active' => ['required', 'boolean']]);
    }
}
