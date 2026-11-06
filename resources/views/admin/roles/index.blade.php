@extends('layouts.admin')

@section('title', 'Roles & permissions')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <p class="text-sm text-gray-500">A role says which screens a person can see (View) and change (Manage). Give each user one role.</p>
  @if (auth()->user()->hasPermission('roles.manage'))
    <a href="{{ route('admin.roles.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">+ Add role</a>
  @endif
</div>

<div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
      <tr><th class="px-4 py-3">Role</th><th class="px-4 py-3">Description</th><th class="px-4 py-3">Access</th><th class="px-4 py-3">People</th><th class="px-4 py-3 text-right">Actions</th></tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @foreach ($roles as $r)
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 font-semibold">{{ $r->name }} @if ($r->is_system)<span class="ml-1 rounded bg-gray-200 px-1.5 text-[10px] font-semibold text-gray-600">SYSTEM</span>@endif</td>
          <td class="px-4 py-3 text-gray-600">{{ $r->description }}</td>
          <td class="px-4 py-3">{{ $r->isSuper() ? 'Everything' : count((array) $r->permissions).' permissions' }}</td>
          <td class="px-4 py-3">{{ $r->users_count }}</td>
          <td class="whitespace-nowrap px-4 py-3 text-right">
            <a href="{{ route('admin.roles.edit', $r) }}" class="mr-3 font-semibold text-brand-600 hover:underline">{{ $r->is_system || ! auth()->user()->hasPermission('roles.manage') ? 'View' : 'Edit' }}</a>
            @if (! $r->is_system && auth()->user()->hasPermission('roles.manage'))
              <form method="POST" action="{{ route('admin.roles.destroy', $r) }}" class="inline" onsubmit="return confirm('Delete the role {{ $r->name }}?')">@csrf @method('DELETE')
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
