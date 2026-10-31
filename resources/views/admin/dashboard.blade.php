@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  @foreach ($counts as $label => $n)
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
      <p class="text-sm text-gray-500">{{ $label }}</p>
      <p class="mt-1 text-3xl font-bold text-gray-900">{{ $n }}</p>
    </div>
  @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
  <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100 lg:col-span-2">
    <div class="flex items-center justify-between">
      <h2 class="font-bold">Latest messages</h2>
      <a href="{{ route('admin.messages') }}" class="text-sm font-semibold text-brand-600 hover:underline">All messages →</a>
    </div>
    <div class="mt-3 divide-y divide-gray-100">
      @forelse ($latest as $m)
        <div class="py-3 text-sm">
          <p class="font-semibold">{{ $m->name }} <span class="font-normal text-gray-500">· {{ $m->phone }}</span> @unless ($m->is_read)<span class="ml-1 rounded bg-brand-600 px-1.5 text-[10px] text-white">NEW</span>@endunless</p>
          <p class="text-gray-600">{{ \Illuminate\Support\Str::limit($m->message ?: ($m->service ?: '—'), 120) }}</p>
          <p class="text-xs text-gray-400">{{ $m->created_at->diffForHumans() }}</p>
        </div>
      @empty
        <p class="py-6 text-sm text-gray-500">No messages yet. Messages sent from the website contact form appear here.</p>
      @endforelse
    </div>
  </div>

  <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
    <h2 class="font-bold">Quick links</h2>
    <div class="mt-3 grid gap-2 text-sm">
      <a href="{{ url('/') }}" target="_blank" rel="noopener" class="rounded-lg border border-gray-200 px-3 py-2 font-semibold hover:border-brand-600 hover:text-brand-600">View website ↗</a>
      <a href="{{ route('admin.settings.edit', 'theme') }}" class="rounded-lg border border-gray-200 px-3 py-2 hover:border-brand-600 hover:text-brand-600">Theme colours &amp; default theme</a>
      <a href="{{ route('admin.pages.index') }}" class="rounded-lg border border-gray-200 px-3 py-2 hover:border-brand-600 hover:text-brand-600">Page SEO &amp; custom code</a>
      <a href="{{ route('admin.settings.edit', 'code') }}" class="rounded-lg border border-gray-200 px-3 py-2 hover:border-brand-600 hover:text-brand-600">Custom scripts &amp; CSS (all pages)</a>
      <a href="{{ route('admin.content.create', 'insights') }}" class="rounded-lg border border-gray-200 px-3 py-2 hover:border-brand-600 hover:text-brand-600">+ New insight</a>
      <a href="{{ route('admin.content.create', 'projects') }}" class="rounded-lg border border-gray-200 px-3 py-2 hover:border-brand-600 hover:text-brand-600">+ New project</a>
      <form method="POST" action="{{ route('admin.cache.clear') }}">@csrf
        <button class="w-full rounded-lg bg-gray-900 px-3 py-2 text-left font-semibold text-white hover:bg-black">Clear cache</button>
      </form>
    </div>
  </div>
</div>
@endsection
