<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceType;
use App\Models\MonitoringProfile;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $devices = Device::with(['site', 'type', 'monitoringProfile'])
            ->when($type !== '', fn ($query) => $query->whereHas('type', fn ($typeQuery) => $typeQuery->where('slug', $type)))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('devices.index', ['devices' => $devices, 'types' => DeviceType::where('is_active', true)->orderBy('name')->get(), 'selectedType' => $type]);
    }

    public function create(): View
    {
        return view('devices.form', $this->formData(new Device));
    }

    public function store(Request $request): RedirectResponse
    {
        $device = Device::create($this->validated($request));

        return redirect()->route('devices.show', $device)->with('status', 'Device added to inventory.');
    }

    public function show(Device $device): View
    {
        return view('devices.show', ['device' => $device->load(['site', 'type', 'monitoringProfile'])]);
    }

    public function edit(Device $device): View
    {
        return view('devices.form', $this->formData($device));
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $device->update($this->validated($request, $device));

        return redirect()->route('devices.show', $device)->with('status', 'Device updated.');
    }

    public function destroy(Device $device): RedirectResponse
    {
        $device->delete();

        return redirect()->route('devices.index')->with('status', 'Device removed from inventory.');
    }

    private function formData(Device $device): array
    {
        return ['device' => $device, 'sites' => Site::where('is_active', true)->orderBy('name')->get(), 'types' => DeviceType::where('is_active', true)->orderBy('name')->get(), 'profiles' => MonitoringProfile::where('is_active', true)->orderBy('name')->get()];
    }

    private function validated(Request $request, ?Device $device = null): array
    {
        $macAddress = preg_replace('/[^0-9a-f]/i', '', (string) $request->input('mac_address'));
        $request->merge(['mac_address' => strlen($macAddress) === 12 ? strtoupper(implode(':', str_split($macAddress, 2))) : $request->input('mac_address')]);

        return $request->validate(['site_id' => ['required', 'exists:sites,id'], 'device_type_id' => ['required', 'exists:device_types,id'], 'monitoring_profile_id' => ['nullable', 'exists:monitoring_profiles,id'], 'name' => ['required', 'string', 'max:150'], 'hostname' => ['nullable', 'string', 'max:255'], 'management_ip' => ['required', 'ipv4', Rule::unique('devices', 'management_ip')->where(fn ($q) => $q->where('site_id', $request->site_id))->ignore($device)], 'mac_address' => ['required', 'regex:/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i', Rule::unique('devices', 'mac_address')->where(fn ($q) => $q->where('site_id', $request->site_id))->ignore($device)], 'default_gateway' => ['required', 'ipv4'], 'subnet_mask' => ['required', 'ipv4'], 'ip_assignment' => ['required', Rule::in(['static'])], 'criticality' => ['required', Rule::in(['critical', 'high', 'medium', 'low'])], 'is_active' => ['required', 'boolean'], 'notes' => ['nullable', 'string', 'max:4000']]);
    }
}
