@extends('layouts.admin')

@section('title', 'Attendance entry')

@php $input = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20'; @endphp

@section('content')
<a href="{{ route('admin.attendance.index', ['date' => $date->toDateString()]) }}" class="mb-4 inline-block text-sm text-gray-500 hover:text-brand-600">← Back to the sheet</a>
<form method="POST" action="{{ route('admin.attendance.save') }}" class="max-w-xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
  @csrf @method('PUT')
  <input type="hidden" name="user_id" value="{{ $target->id }}"><input type="hidden" name="date" value="{{ $date->toDateString() }}">
  <p class="text-lg font-bold">{{ $target->name }}</p>
  <p class="text-sm text-gray-500">{{ $date->format('l, d F Y') }} · {{ $shift ? $shift->name.' '.$shift->start().' - '.$shift->end() : 'no shift' }}</p>

  <div class="mt-4 grid grid-cols-2 gap-4">
    <label class="block text-sm font-semibold text-gray-800">Clock in
      <input type="time" name="clock_in" value="{{ old('clock_in', $rec?->clock_in?->format('H:i')) }}" class="{{ $input }}"></label>
    <label class="block text-sm font-semibold text-gray-800">Clock out
      <input type="time" name="clock_out" value="{{ old('clock_out', $rec?->clock_out?->format('H:i')) }}" class="{{ $input }}"></label>
  </div>
  <label class="mt-4 block text-sm font-semibold text-gray-800">Note <span class="font-normal text-gray-500">(why it was corrected)</span>
    <input type="text" name="note" value="{{ old('note', $rec?->note) }}" maxlength="255" class="{{ $input }}"></label>
  <p class="mt-2 text-xs text-gray-500">Late minutes are worked out again from the shift when you save.</p>

  <div class="mt-6 flex items-center gap-4">
    <button class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save</button>
    @if ($rec)
      <label class="flex items-center gap-2 text-sm text-red-600"><input type="checkbox" name="remove" value="1"> Remove this entry</label>
    @endif
  </div>
</form>
@endsection
