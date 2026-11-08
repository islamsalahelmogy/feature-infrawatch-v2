<x-layouts.app>
<h1>{{ $schedule->exists ? 'Edit' : 'Add' }} Monitoring Schedule</h1>
<form class="card" method="POST" action="{{ $schedule->exists ? route('monitoring-schedules.update', $schedule) : route('monitoring-schedules.store') }}">
@csrf @if($schedule->exists) @method('PUT') @endif
<label for="name">Schedule name</label><input id="name" name="name" value="{{ old('name', $schedule->name) }}" required>
<label>Repeat</label>
<div class="frequency-options">
@foreach(['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly'] as $value=>$label)
<label><input type="radio" name="frequency" value="{{ $value }}" @checked(old('frequency', $schedule->frequency ?? 'daily') === $value)> {{ $label }}</label>
@endforeach
</div>
<div id="weekly-fields"><label for="weekday">Day of the week</label><select id="weekday" name="weekday">
@foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day=>$label)<option value="{{ $day }}" @selected(old('weekday',$schedule->weekday) == $day)>{{ $label }}</option>@endforeach
</select></div>
<div id="monthly-fields"><label for="monthday">Day of the month</label><input id="monthday" type="number" min="1" max="31" name="monthday" value="{{ old('monthday',$schedule->monthday ?? 1) }}"><p class="muted">Months without the selected day are skipped.</p></div>
<label>Run at</label><div id="times">
@foreach(old('times',$schedule->times ?? ['09:00']) as $time)
<div class="time-row"><input type="time" name="times[]" value="{{ $time }}" aria-label="Run time" required><button type="button" class="button remove-time">Remove</button></div>
@endforeach
</div>
<button type="button" class="button" id="add-time">+ Add another time</button>
<p class="muted" id="times-count"></p>
<label for="timezone">Timezone</label><input id="timezone" name="timezone" value="{{ old('timezone',$schedule->timezone ?? 'Africa/Cairo') }}" required>
<input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$schedule->is_active))> Active</label>
<button class="button">Save schedule</button>
</form>
<script>
const times = document.getElementById('times');
function refresh() {
 const frequency = document.querySelector('[name=frequency]:checked').value;
 ['weekly','monthly'].forEach(kind => {
  const section = document.getElementById(kind+'-fields');
  section.hidden = frequency !== kind;
  section.querySelectorAll('input,select').forEach(input => input.disabled = section.hidden);
 });
 const count = times.children.length;
 times.querySelectorAll('.remove-time').forEach(button => button.hidden = count === 1);
 document.getElementById('times-count').textContent = count+' time(s) on each scheduled day.';
 document.getElementById('add-time').disabled = count >= 24;
}
document.querySelectorAll('[name=frequency]').forEach(input => input.addEventListener('change',refresh));
document.getElementById('add-time').addEventListener('click',() => {
 if(times.children.length >= 24) return;
 const row = times.firstElementChild.cloneNode(true);
 row.querySelector('input').value = '';
 times.appendChild(row); refresh(); row.querySelector('input').focus();
});
times.addEventListener('click',event => {
 if(event.target.classList.contains('remove-time') && times.children.length > 1) { event.target.closest('.time-row').remove(); refresh(); }
});
refresh();
</script>
</x-layouts.app>
