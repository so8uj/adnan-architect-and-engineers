@extends('layouts.admin')

@section('title', 'Page SEO & custom code')

@section('content')
<p class="mb-4 text-sm text-gray-600">Each page has its own title, description, social image, robots rules, structured data and custom scripts / CSS. Projects and insights have the same options inside their own edit forms.</p>
<div class="divide-y divide-gray-100 rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
  @foreach ($pages as $page)
    <a href="{{ route('admin.pages.edit', $page) }}" class="flex items-center justify-between px-5 py-4 hover:bg-gray-50">
      <div>
        <p class="font-semibold">{{ $page->name }}</p>
        <p class="text-xs text-gray-500">{{ $page->seo?->tr('meta_title', 'en') ?: ($page->seo?->tr('meta_title', 'bn') ?: 'No custom title') }}</p>
      </div>
      <span class="text-sm font-semibold text-brand-600">Edit →</span>
    </a>
  @endforeach
</div>
@endsection
