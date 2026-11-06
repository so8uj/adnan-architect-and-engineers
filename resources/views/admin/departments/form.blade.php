@extends('layouts.admin')

@section('title', $department->exists ? 'Edit department' : 'New department')

@php $input = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20'; @endphp

@section('content')
<a href="{{ route('admin.departments.index') }}" class="mb-4 inline-block text-sm text-gray-500 hover:text-brand-600">← Back to departments</a>
<form method="POST" action="{{ $department->exists ? route('admin.departments.update', $department) : route('admin.departments.store') }}" class="max-w-xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
  @csrf
  @if ($department->exists) @method('PUT') @endif
  <label class="block text-sm font-semibold text-gray-800">Name
    <input type="text" name="name" value="{{ old('name', $department->name) }}" required class="{{ $input }}"></label>
  <label class="mt-4 block text-sm font-semibold text-gray-800">Description <span class="font-normal text-gray-500">(optional)</span>
    <input type="text" name="description" value="{{ old('description', $department->description) }}" class="{{ $input }}"></label>
  <button class="mt-6 rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save</button>
</form>
@endsection
