@extends('layouts.admin')

@section('title', 'Messages')

@section('content')
<div class="grid gap-3 xl:grid-cols-2 items-start">
  @forelse ($messages as $m)
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 {{ $m->is_read ? 'ring-gray-100' : 'ring-brand-600/40' }}">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="font-semibold">{{ $m->name }} @unless ($m->is_read)<span class="ml-1 rounded bg-brand-600 px-1.5 text-[10px] text-white">NEW</span>@endunless</p>
          <p class="text-sm text-gray-600"><a href="tel:{{ $m->phone }}" class="hover:text-brand-600">{{ $m->phone }}</a> @if ($m->service) · {{ $m->service }} @endif</p>
        </div>
        <p class="text-xs text-gray-400">{{ $m->created_at->format('d M Y, h:i A') }}</p>
      </div>
      @if ($m->message)<p class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $m->message }}</p>@endif
      <div class="mt-3 flex gap-4 text-sm">
        <form method="POST" action="{{ route('admin.messages.read', $m) }}">@csrf<button class="font-semibold text-brand-600 hover:underline">{{ $m->is_read ? 'Mark unread' : 'Mark read' }}</button></form>
        <form method="POST" action="{{ route('admin.messages.destroy', $m) }}" onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
      </div>
    </div>
  @empty
    <p class="rounded-xl bg-white p-10 text-center text-gray-500 shadow-sm">No messages yet.</p>
  @endforelse
  {{ $messages->links() }}
</div>
@endsection
