@extends('layouts.admin')

@section('title', 'My attendance')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
  {{-- clock card --}}
  <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100" x-data="{ t: '' }" x-init="const f = () => t = new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', second: '2-digit'}); f(); setInterval(f, 1000)">
    <p class="text-sm text-gray-500">{{ now()->format('l, d F Y') }}</p>
    <p class="mt-1 text-4xl font-bold tabular-nums" x-text="t">{{ now()->format('h:i:s A') }}</p>

    @if (! $user->attendance_required)
      <p class="mt-4 rounded-lg bg-gray-100 px-4 py-3 text-sm text-gray-700">Attendance is not required for your account - nothing to clock.</p>
    @elseif (! $shift)
      <p class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">You have no shift yet. Ask HR or your manager to give you one.</p>
    @else
      <p class="mt-3 text-sm text-gray-600">Your shift: <strong>{{ $shift->name }}</strong> · {{ $shift->start() }} - {{ $shift->end() }} · {{ $shift->daysLabel() }}</p>

      @if (! $today)
        <form method="POST" action="{{ route('admin.attendance.in') }}" class="mt-5">@csrf
          <button class="w-full rounded-xl bg-brand-600 px-6 py-4 text-lg font-bold text-white hover:bg-brand-700">Clock in</button>
        </form>
      @elseif (! $today->clock_out)
        <p class="mt-4 text-sm">Clocked in at <strong>{{ $today->clock_in->format('h:i A') }}</strong>
          @if ($today->late_minutes)<span class="ml-1 rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">{{ $today->late_minutes }} min late</span>@else<span class="ml-1 rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">on time</span>@endif</p>
        <form method="POST" action="{{ route('admin.attendance.out') }}" class="mt-4">@csrf
          <button class="w-full rounded-xl bg-gray-900 px-6 py-4 text-lg font-bold text-white hover:bg-black">Clock out</button>
        </form>
      @else
        <p class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">Done for today: {{ $today->clock_in->format('h:i A') }} - {{ $today->clock_out->format('h:i A') }}
          ({{ intdiv($today->worked_minutes, 60) }}h {{ $today->worked_minutes % 60 }}m)</p>
      @endif
    @endif
  </div>

  {{-- month --}}
  <div class="lg:col-span-2">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.attendance.me', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:border-brand-600">‹</a>
        <strong class="px-2">{{ $month->format('F Y') }}</strong>
        <a href="{{ route('admin.attendance.me', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:border-brand-600">›</a>
      </div>
      <p class="text-sm text-gray-600">{{ $totals['days'] }} days · {{ $totals['late'] }} late ({{ $totals['lateMinutes'] }} min) · {{ $totals['hours'] }} h worked</p>
    </div>
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">In</th><th class="px-4 py-3">Out</th><th class="px-4 py-3">Worked</th><th class="px-4 py-3">Status</th></tr></thead>
        <tbody class="divide-y divide-gray-100">
          @forelse ($rows as $r)
            <tr>
              <td class="px-4 py-2.5 font-semibold">{{ $r->date->format('D, d M') }}</td>
              <td class="px-4 py-2.5">{{ $r->clock_in?->format('h:i A') ?? '—' }}</td>
              <td class="px-4 py-2.5">{{ $r->clock_out?->format('h:i A') ?? '—' }}</td>
              <td class="px-4 py-2.5">{{ $r->worked_minutes ? intdiv($r->worked_minutes, 60).'h '.($r->worked_minutes % 60).'m' : '—' }}</td>
              <td class="px-4 py-2.5">@if ($r->late_minutes)<span class="rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Late {{ $r->late_minutes }}m</span>@else<span class="rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">On time</span>@endif
                @if ($r->note)<span class="ml-2 text-xs text-gray-500">{{ $r->note }}</span>@endif</td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No attendance recorded this month.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
