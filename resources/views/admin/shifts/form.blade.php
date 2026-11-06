@extends('layouts.admin')

@section('title', $shift->exists ? 'Edit shift' : 'New shift')

@php
  $input = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20';
  $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
  $chosen = array_map('intval', (array) old('work_days', $shift->work_days ?? []));
@endphp

@section('content')
<a href="{{ route('admin.shifts.index') }}" class="mb-4 inline-block text-sm text-gray-500 hover:text-brand-600">← Back to shifts</a>
<form method="POST" action="{{ $shift->exists ? route('admin.shifts.update', $shift) : route('admin.shifts.store') }}" class="max-w-xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
  @csrf
  @if ($shift->exists) @method('PUT') @endif
  <label class="block text-sm font-semibold text-gray-800">Shift name
    <input type="text" name="name" value="{{ old('name', $shift->name) }}" required placeholder="Day shift" class="{{ $input }}"></label>
  <div class="mt-4 grid grid-cols-3 gap-4">
    <label class="block text-sm font-semibold text-gray-800">Starts
      <input type="time" name="starts_at" value="{{ old('starts_at', $shift->start()) }}" required class="{{ $input }}"></label>
    <label class="block text-sm font-semibold text-gray-800">Ends
      <input type="time" name="ends_at" value="{{ old('ends_at', $shift->end()) }}" required class="{{ $input }}"></label>
    <label class="block text-sm font-semibold text-gray-800">Grace (min)
      <input type="number" name="grace_minutes" min="0" max="240" value="{{ old('grace_minutes', $shift->grace_minutes) }}" required class="{{ $input }}"></label>
  </div>
  <fieldset class="mt-4">
    <legend class="text-sm font-semibold text-gray-800">Work days</legend>
    <div class="mt-2 flex flex-wrap gap-2">
      @foreach ($days as $n => $d)
        <label class="flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-1.5 text-sm"><input type="checkbox" name="work_days[]" value="{{ $n }}" @checked(in_array($n, $chosen, true))> {{ $d }}</label>
      @endforeach
    </div>
  </fieldset>
  <button class="mt-6 rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save</button>
</form>
@endsection
