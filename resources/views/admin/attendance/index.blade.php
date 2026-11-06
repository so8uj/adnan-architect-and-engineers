@extends('layouts.admin')

@section('title', 'Attendance sheet')

@php
  $badge = [
    'present' => ['Present', 'bg-green-100 text-green-700'], 'late' => ['Late', 'bg-red-100 text-red-700'], 'absent' => ['Absent', 'bg-red-600 text-white'],
    'off' => ['Day off', 'bg-gray-100 text-gray-600'], 'noshift' => ['No shift', 'bg-amber-100 text-amber-700'],
    'waiting' => ['Not in yet', 'bg-blue-50 text-blue-700'], 'upcoming' => ['Upcoming', 'bg-gray-100 text-gray-500'],
  ];
@endphp

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <form method="GET" class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.attendance.index', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:border-brand-600">‹</a>
    <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-1.5">
    <a href="{{ route('admin.attendance.index', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:border-brand-600">›</a>
    <a href="{{ route('admin.attendance.index') }}" class="px-2 font-semibold text-brand-600 hover:underline">Today</a>
  </form>
  <p class="text-sm text-gray-600">{{ $sum['present'] }} present · {{ $sum['late'] }} late · {{ $sum['absent'] }} absent</p>
</div>

<div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
      <tr><th class="px-4 py-3">Person</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Shift</th><th class="px-4 py-3">In</th><th class="px-4 py-3">Out</th><th class="px-4 py-3">Worked</th><th class="px-4 py-3">Status</th>@if ($canManage)<th class="px-4 py-3 text-right">Fix</th>@endif</tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @forelse ($rows as $r)
        @php [$label, $cls] = $badge[$r['status']]; @endphp
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-2.5 font-semibold">{{ $r['user']->name }}</td>
          <td class="px-4 py-2.5 text-gray-600">{{ $r['user']->department?->name ?? '—' }}</td>
          <td class="px-4 py-2.5 text-gray-600">{{ $r['shift'] ? $r['shift']->name.' '.$r['shift']->start().'-'.$r['shift']->end() : '—' }}</td>
          <td class="px-4 py-2.5">{{ $r['rec']?->clock_in?->format('h:i A') ?? '—' }}</td>
          <td class="px-4 py-2.5">{{ $r['rec']?->clock_out?->format('h:i A') ?? '—' }}</td>
          <td class="px-4 py-2.5">{{ $r['rec']?->worked_minutes ? intdiv($r['rec']->worked_minutes, 60).'h '.($r['rec']->worked_minutes % 60).'m' : '—' }}</td>
          <td class="px-4 py-2.5"><span class="rounded px-2 py-0.5 text-xs font-semibold {{ $cls }}">{{ $label }}@if ($r['status'] === 'late') {{ $r['rec']->late_minutes }}m @endif</span>
            @if ($r['rec']?->note)<span class="ml-1 text-xs text-gray-500">{{ $r['rec']->note }}</span>@endif</td>
          @if ($canManage)<td class="px-4 py-2.5 text-right"><a href="{{ route('admin.attendance.entry', ['user' => $r['user']->id, 'date' => $date->toDateString()]) }}" class="font-semibold text-brand-600 hover:underline">{{ $r['rec'] ? 'Edit' : 'Add' }}</a></td>@endif
        </tr>
      @empty
        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Nobody needs attendance yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
