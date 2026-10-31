@extends('layouts.admin')

@section('title', 'User management')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <p class="text-sm text-gray-500">{{ $users->count() }} {{ \Illuminate\Support\Str::plural('user', $users->count()) }} can sign in to this admin panel.</p>
  <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">+ Add user</a>
</div>

<div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
      <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Created</th><th class="px-4 py-3 text-right">Actions</th></tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @foreach ($users as $u)
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 font-semibold">{{ $u->name }} @if ($u->id === auth()->id())<span class="ml-1 rounded bg-gray-200 px-1.5 text-[10px] font-semibold text-gray-600">YOU</span>@endif</td>
          <td class="px-4 py-3">{{ $u->email }}</td>
          <td class="px-4 py-3 text-gray-500">{{ $u->created_at?->format('d M Y') }}</td>
          <td class="whitespace-nowrap px-4 py-3 text-right">
            <a href="{{ route('admin.users.edit', $u->id) }}" class="mr-3 font-semibold text-brand-600 hover:underline">Edit</a>
            @if ($u->id !== auth()->id() && ! $u->is_developer)
              <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" class="inline" onsubmit="return confirm('Delete {{ $u->name }}?')">@csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
