@extends('layouts.admin')

@section('title', 'SEO · '.$page->name)

@section('content')
<form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
  @csrf @method('PUT')
  <a href="{{ route('admin.pages.index') }}" class="mb-2 inline-block text-sm text-gray-500 hover:text-brand-600">← All pages</a>
  @include('admin.partials.seo', ['seo' => $seo])
  <div class="sticky bottom-0 -mx-4 mt-6 border-t border-gray-200 bg-white/95 px-4 py-3 sm:-mx-6 sm:px-6">
    <button class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save</button>
  </div>
</form>
@endsection
