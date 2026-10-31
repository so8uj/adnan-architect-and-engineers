@extends('layouts.admin')

@section('title', $def['title'])

@section('content')
<form method="POST" action="{{ route('admin.settings.update', $def['slug']) }}" enctype="multipart/form-data">
  @csrf @method('PUT')

  @if (! empty($def['intro']))
    <p class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $def['intro'] }}</p>
  @endif

  @include('admin.partials.fields', ['fields' => $def['fields'], 'get' => fn ($k) => $values[$k] ?? null, 'prefix' => null, 'mode' => 'grid'])

  <div class="sticky bottom-0 -mx-4 mt-6 border-t border-gray-200 bg-white/95 px-4 py-3 sm:-mx-6 sm:px-6">
    <button class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save changes</button>
  </div>
</form>
@endsection
