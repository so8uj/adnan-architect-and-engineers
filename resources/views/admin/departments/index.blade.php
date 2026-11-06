@extends('layouts.admin')

@section('title', 'Departments')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <p class="text-sm text-gray-500">Group your team into departments (Design, Site engineering, Accounts ...). A person belongs to one department and has one role.</p>
  @if (auth()->user()->hasPermission('departments.manage'))
    <a href="{{ route('admin.departments.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">+ Add department</a>
  @endif
</div>

<div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
      <tr><th class="px-4 py-3">Department</th><th class="px-4 py-3">Description</th><th class="px-4 py-3">People</th><th class="px-4 py-3 text-right">Actions</th></tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @forelse ($departments as $d)
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 font-semibold">{{ $d->name }}</td>
          <td class="px-4 py-3 text-gray-600">{{ $d->description }}</td>
          <td class="px-4 py-3">{{ $d->users_count }}</td>
          <td class="whitespace-nowrap px-4 py-3 text-right">
            @if (auth()->user()->hasPermission('departments.manage'))
              <a href="{{ route('admin.departments.edit', $d) }}" class="mr-3 font-semibold text-brand-600 hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.departments.destroy', $d) }}" class="inline" onsubmit="return confirm('Delete {{ $d->name }}? Its people stay, without a department.')">@csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No departments yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
