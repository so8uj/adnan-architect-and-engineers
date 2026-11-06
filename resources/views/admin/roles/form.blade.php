@extends('layouts.admin')

@section('title', $role->exists ? ($role->is_system ? 'Role: '.$role->name : 'Edit role') : 'New role')

@php
  $input = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20';
  $locked = $role->is_system || ! auth()->user()->hasPermission('roles.manage');
  $have = (array) old('permissions', $role->permissions ?? []);
  $me = auth()->user();
@endphp

@section('content')
<a href="{{ route('admin.roles.index') }}" class="mb-4 inline-block text-sm text-gray-500 hover:text-brand-600">← Back to roles</a>

@if ($role->is_system)
  <p class="mb-4 max-w-3xl rounded-lg bg-gray-100 px-4 py-3 text-sm text-gray-700">Super Admin has access to every screen, including ones added later. It is protected and can not be changed.</p>
@endif

<form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="max-w-3xl"
  x-data="{ perms: @js($have),
    has(k) { return this.perms.includes(k) },
    toggle(m, a) { const k = m + '.' + a, v = m + '.view', g = m + '.manage';
      if (this.has(k)) { this.perms = this.perms.filter(p => p !== k && !(a === 'view' && p === g)); }
      else { this.perms.push(k); if (a === 'manage' && !this.has(v)) this.perms.push(v); } } }">
  @csrf
  @if ($role->exists) @method('PUT') @endif

  <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
    <label class="block text-sm font-semibold text-gray-800">Role name
      <input type="text" name="name" value="{{ old('name', $role->name) }}" required @disabled($locked) class="{{ $input }}"></label>
    <label class="mt-4 block text-sm font-semibold text-gray-800">Description <span class="font-normal text-gray-500">(optional)</span>
      <input type="text" name="description" value="{{ old('description', $role->description) }}" @disabled($locked) class="{{ $input }}"></label>
  </div>

  @unless ($role->isSuper())
    @foreach ($catalogue as $group => $modules)
      <div class="mt-5 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <div class="bg-gray-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $group }}</div>
        <table class="min-w-full text-sm">
          <thead><tr class="text-left text-xs text-gray-500"><th class="px-4 py-2 font-semibold">Module</th><th class="w-24 px-4 py-2 text-center font-semibold">View</th><th class="w-24 px-4 py-2 text-center font-semibold">Manage</th></tr></thead>
          <tbody class="divide-y divide-gray-100">
            @foreach ($modules as $module => $label)
              <tr>
                <td class="px-4 py-2.5 font-semibold">{{ $label }}</td>
                @foreach (['view', 'manage'] as $action)
                  @php $perm = $module.'.'.$action; $can = $me->hasPermission($perm); @endphp
                  <td class="px-4 py-2.5 text-center">
                    <input type="checkbox" class="h-4 w-4 accent-[rgb(var(--brand-600))]" @disabled($locked || ! $can)
                      :checked="has('{{ $perm }}')" @change="toggle('{{ $module }}', '{{ $action }}')" @if (! $can) title="You do not have this permission yourself" @endif>
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endforeach
    <template x-for="p in perms" :key="p"><input type="hidden" name="permissions[]" :value="p"></template>
  @endunless

  @unless ($locked)
    <button class="mt-6 rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save role</button>
  @endunless
</form>
@endsection
