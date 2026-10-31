@extends('layouts.admin')

@section('title', ($item->exists ? 'Edit ' : 'New ').$def['single'])

@section('content')
<form method="POST" enctype="multipart/form-data"
  action="{{ $item->exists ? route('admin.content.update', [$def['slug'], $item->id]) : route('admin.content.store', $def['slug']) }}">
  @csrf
  @if ($item->exists) @method('PUT') @endif

  <a href="{{ route('admin.content.index', $def['slug']) }}" class="mb-4 inline-block text-sm text-gray-500 hover:text-brand-600">← Back to {{ strtolower($def['title']) }}</a>

  @include('admin.partials.fields', ['fields' => $def['fields'], 'get' => fn ($k) => $item->getAttribute($k), 'prefix' => null, 'mode' => 'split'])

  @if ($seo)
    <h2 class="mt-8 text-lg font-bold">SEO &amp; custom code</h2>
    @include('admin.partials.seo', ['seo' => $seo])
  @endif

  <div class="sticky bottom-0 -mx-4 mt-6 flex items-center gap-4 border-t border-gray-200 bg-white/95 px-4 py-3 sm:-mx-6 sm:px-6">
    <button class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save</button>
    @if ($item->exists && ! empty($def['view_url']))
      <a href="{{ str_replace('{slug}', $item->slug, $def['view_url']) }}" target="_blank" rel="noopener" class="text-sm font-semibold text-gray-600 hover:text-brand-600">View on website ↗</a>
    @endif
  </div>
</form>
@endsection
