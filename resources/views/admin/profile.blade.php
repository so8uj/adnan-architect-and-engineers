@extends('layouts.admin')

@section('title', 'My profile')

@php $input = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20'; @endphp

@section('content')
<form method="POST" action="{{ route('admin.profile.update') }}" class="max-w-xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
  @csrf @method('PUT')
  <label class="block text-sm font-semibold text-gray-800">Name
    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $input }}"></label>
  <label class="mt-4 block text-sm font-semibold text-gray-800">Email
    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $input }}"></label>

  <h2 class="mt-6 text-sm font-bold uppercase tracking-wide text-gray-500">Change password</h2>
  <label class="mt-2 block text-sm font-semibold text-gray-800">Current password
    <input type="password" name="current_password" autocomplete="current-password" class="{{ $input }}"></label>
  <label class="mt-4 block text-sm font-semibold text-gray-800">New password <span class="font-normal text-gray-500">(min. 8 characters, leave empty to keep)</span>
    <input type="password" name="password" minlength="8" autocomplete="new-password" class="{{ $input }}"></label>
  <label class="mt-4 block text-sm font-semibold text-gray-800">Repeat new password
    <input type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}"></label>

  <button class="mt-6 rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save profile</button>
</form>
@endsection
