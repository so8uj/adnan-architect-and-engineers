@extends('layouts.admin')

@section('title', 'Shifts')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <p class="text-sm text-gray-500">A shift says when the day starts and ends. Late = clocking in after the start time plus the grace minutes.</p>
  @if ($canManage)<a href="{{ route('admin.shifts.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">+ Add shift</a>@endif
</div>

<div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-4 py-3">Shift</th><th class="px-4 py-3">Hours</th><th class="px-4 py-3">Grace</th><th class="px-4 py-3">Work days</th><th class="px-4 py-3">Assigned</th>@if ($canManage)<th class="px-4 py-3 text-right">Actions</th>@endif</tr></thead>
    <tbody class="divide-y divide-gray-100">
      @forelse ($shifts as $s)
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 font-semibold">{{ $s->name }}</td>
          <td class="px-4 py-3">{{ $s->start() }} - {{ $s->end() }}</td>
          <td class="px-4 py-3">{{ $s->grace_minutes }} min</td>
          <td class="px-4 py-3 text-gray-600">{{ $s->daysLabel() }}</td>
          <td class="px-4 py-3">{{ $s->assignments_count }}</td>
          @if ($canManage)
            <td class="whitespace-nowrap px-4 py-3 text-right">
              <a href="{{ route('admin.shifts.edit', $s) }}" class="mr-3 font-semibold text-brand-600 hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.shifts.destroy', $s) }}" class="inline" onsubmit="return confirm('Delete {{ $s->name }}?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
            </td>
          @endif
        </tr>
      @empty
        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No shifts yet. Add one, then give people that shift below.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<h2 class="mb-3 mt-8 text-lg font-bold">Who works which shift</h2>
<form method="POST" action="{{ route('admin.shifts.assign') }}" x-data="{ all: false }">
  @csrf
  <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
        <tr>@if ($canManage)<th class="w-10 px-4 py-3"><input type="checkbox" x-model="all" @change="$root.querySelectorAll('[name=&quot;users[]&quot;]').forEach(c => c.checked = all)"></th>@endif
          <th class="px-4 py-3">Person</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Current shift</th></tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach ($people as $p)
          <tr class="hover:bg-gray-50">
            @if ($canManage)<td class="px-4 py-2.5"><input type="checkbox" name="users[]" value="{{ $p['user']->id }}"></td>@endif
            <td class="px-4 py-2.5 font-semibold">{{ $p['user']->name }}</td>
            <td class="px-4 py-2.5 text-gray-600">{{ $p['user']->department?->name ?? '—' }}</td>
            <td class="px-4 py-2.5">{{ $p['shift'] ? $p['shift']->name.' ('.$p['shift']->start().'-'.$p['shift']->end().')' : 'No shift' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if ($canManage && $shifts->count())
    <div class="mt-4 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
      <label class="text-sm font-semibold text-gray-800">Give the ticked people shift
        <select name="shift_id" class="mt-1 block rounded-lg border border-gray-300 px-3 py-2 text-sm">@foreach ($shifts as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->start() }}-{{ $s->end() }})</option>@endforeach</select></label>
      <label class="text-sm font-semibold text-gray-800">from
        <input type="date" name="starts_on" value="{{ today()->toDateString() }}" class="mt-1 block rounded-lg border border-gray-300 px-3 py-2 text-sm"></label>
      <button class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Assign</button>
    </div>
  @endif
</form>
@endsection
