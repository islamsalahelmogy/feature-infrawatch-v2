<x-layouts.app>
<a href="{{ route('dashboard') }}" class="button">Back to dashboard</a>
<h1>Ping report #{{ $run->id }}</h1>
<p class="muted">{{ $run->schedule?->name ?? 'Deleted schedule' }} · {{ ucfirst($run->status) }} · {{ $run->started_at?->timezone('Africa/Cairo')->format('d M Y H:i:s') }} (Cairo)</p>
<p class="muted">Results below belong to this test, not the current device status.</p>
<div class="grid">
@foreach(['Expected'=>$run->expected_devices,'Responded'=>$counts['success'] ?? 0,'No response'=>$counts['failure'] ?? 0,'Pending'=>$counts['pending'] ?? 0,'Not tested'=>$counts['missed'] ?? 0] as $label=>$count)
<div class="card stat"><span class="muted">{{ $label }}</span><div class="metric">{{ $count }}</div></div>
@endforeach
</div>
<div class="card" style="overflow:auto"><table><thead><tr>
<x-sort-heading column="device" label="Device" default="device"/><x-sort-heading column="type" label="Type"/><x-sort-heading column="site" label="Site"/><x-sort-heading column="ip" label="IP address"/><x-sort-heading column="status" label="Ping result"/><x-sort-heading column="latency" label="Response (ms)"/><x-sort-heading column="time" label="Checked at (Cairo)"/><th>Details</th>
</tr></thead><tbody>
@forelse($results as $result)
<tr><td>{{ $result->name ?? 'Removed device' }}</td><td>{{ $result->type }}</td><td>{{ $result->site }}</td><td>{{ $result->management_ip }}</td><td><span class="badge {{ ['success'=>'up','failure'=>'down','pending'=>'unknown','missed'=>'degraded'][$result->status] ?? 'unknown' }}">{{ ['success'=>'Responded','failure'=>'No response','pending'=>'Pending','missed'=>'Not tested'][$result->status] ?? $result->status }}</span></td><td>{{ $result->status === 'success' ? $result->response_time_ms : '—' }}</td><td>{{ $result->checked_at ? \Carbon\Carbon::parse($result->checked_at)->timezone('Africa/Cairo')->format('d M H:i:s') : '—' }}</td><td style="max-width:280px;overflow-wrap:anywhere">{{ $result->error_message ?? '—' }}</td></tr>
@empty<tr><td colspan="8">This test has no devices.</td></tr>@endforelse
</tbody></table></div>{{ $results->links() }}
</x-layouts.app>
