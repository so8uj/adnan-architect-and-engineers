@extends('layouts.admin')

@section('title', $user->exists ? 'Edit user' : 'New user')

@php $input = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20'; @endphp

@section('content')
<a href="{{ route('admin.users.index') }}" class="mb-4 inline-block text-sm text-gray-500 hover:text-brand-600">← Back to users</a>
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user->id) : route('admin.users.store') }}" class="max-w-xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
  @csrf
  @if ($user->exists) @method('PUT') @endif

  <label class="block text-sm font-semibold text-gray-800">Name
    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $input }}"></label>
  <label class="mt-4 block text-sm font-semibold text-gray-800">Email
    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $input }}"></label>
  <label class="mt-4 block text-sm font-semibold text-gray-800">Department
    <select name="department_id" class="{{ $input }}"><option value="">— none —</option>
      @foreach ($departments as $d)<option value="{{ $d->id }}" @selected((string) old('department_id', $user->department_id) === (string) $d->id)>{{ $d->name }}</option>@endforeach
    </select></label>
  @if ($user->id !== auth()->id())
  <label class="mt-4 block text-sm font-semibold text-gray-800">Role <span class="font-normal text-gray-500">(decides what this person can see and change)</span>
    <select name="role_id" class="{{ $input }}"><option value="">— no access (dashboard and profile only) —</option>
      @foreach ($roles as $r)<option value="{{ $r->id }}" @selected((string) old('role_id', $user->role_id) === (string) $r->id)>{{ $r->name }}</option>@endforeach
    </select></label>
  @else
  <p class="mt-4 text-sm text-gray-500">Your role: <strong>{{ $user->role?->name ?? 'none' }}</strong> (you can not change your own role).</p>
  @endif
  @if (\App\Support\Features::adminCan('attendance'))
  <label class="mt-4 flex items-center gap-3 text-sm font-semibold text-gray-800">
    <input type="hidden" name="attendance_required" value="0">
    <input type="checkbox" name="attendance_required" value="1" class="h-4 w-4" @checked(old('attendance_required', $user->exists ? $user->attendance_required : true))>
    Needs attendance <span class="font-normal text-gray-500">(untick for people who do not clock in, e.g. the owner)</span></label>
  @endif
  <label class="mt-4 block text-sm font-semibold text-gray-800">Password @if ($user->exists)<span class="font-normal text-gray-500">(leave empty to keep the current one)</span>@endif
    <input type="password" name="password" @if (! $user->exists) required @endif minlength="8" autocomplete="new-password" class="{{ $input }}"></label>
  <label class="mt-4 block text-sm font-semibold text-gray-800">Repeat password
    <input type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}"></label>

  <button class="mt-6 rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save</button>
</form>
@endsection
