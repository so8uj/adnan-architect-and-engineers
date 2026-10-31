@extends('layouts.site')

@section('xdata', '{}')

@php
    use App\Support\SiteData;
    $a = SiteData::insight($insight, app()->getLocale());
    $others = collect($payload['INSIGHTS'])->where('slug', '!=', $a['slug'])->take(3);
@endphp

@section('content')
<main>
  <section data-page-hero class="relative overflow-hidden bg-white blueprint-light border-b border-gray-100">
    <div data-blob class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-brand-100/70"></div>
    <div class="relative mx-auto max-w-4xl px-4 py-14 lg:py-20">
      <nav data-ph class="text-sm text-gray-500">
        <a href="/" class="hover:text-brand-600">হোম</a><span class="mx-2">/</span>
        <a href="/insights" class="hover:text-brand-600">ইনসাইট</a><span class="mx-2">/</span>
        <span class="text-ink-800">{{ $a['category'] }}</span>
      </nav>
      <span data-ph class="mt-5 inline-block text-xs font-medium rounded-full bg-brand-600 text-white px-3 py-1">{{ $a['category'] }}</span>
      <h1 data-ph class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-bold leading-[1.45]">{{ $a['title'] }}</h1>
      <p data-ph class="mt-4 text-sm text-gray-500">{{ $a['date'] }} · {{ $a['readTime'] }} <span>পড়া</span></p>
    </div>
  </section>

  <article class="py-12 lg:py-16">
    <div class="mx-auto max-w-3xl px-4">
      @if ($a['cover'])
      <div data-reveal class="rounded-2xl overflow-hidden border border-gray-100 bg-gray-50">
        <img src="{{ $a['cover'] }}" alt="{{ $a['title'] }}" class="w-full h-auto max-h-[28rem] {{ $a['coverFit'] === 'contain' ? 'object-contain bg-white p-3' : 'object-cover' }}">
      </div>
      @endif

      <p class="mt-8 text-xl text-gray-700 leading-relaxed font-medium">{{ $a['excerpt'] }}</p>

      <div class="article-body mt-8">{!! $a['body'] !!}</div>

      <div class="mt-12 rounded-3xl bg-gradient-to-br from-brand-600 to-brand-400 text-white p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
        <div>
          <h3 class="text-xl font-bold">আপনার প্রজেক্ট নিয়ে কথা বলতে চান?</h3>
          <p class="mt-1 text-white/90 text-sm">ফোন করুন অথবা ফর্ম পূরণ করুন, আমরা দ্রুত যোগাযোগ করব।</p>
        </div>
        <a href="/#contact" data-magnetic class="shrink-0 text-center rounded-full bg-white text-brand-700 font-semibold px-6 py-3 hover:bg-brand-50 transition">যোগাযোগ করুন</a>
      </div>
    </div>
  </article>

  @if ($others->count())
  <section class="pb-20 lg:pb-28 bg-gray-50 pt-16">
    <div class="mx-auto max-w-7xl px-4">
      <div class="flex items-end justify-between gap-4">
        <h2 class="text-2xl sm:text-3xl font-bold">আরও পড়ুন</h2>
        <a href="/insights" class="font-semibold text-brand-600 hover:underline">সব ইনসাইট →</a>
      </div>
      <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($others as $o)
        <a href="/insight/{{ $o['slug'] }}" class="group block rounded-2xl bg-white border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300">
          <div class="img-zoom aspect-[16/10] overflow-hidden bg-gray-100">
            <img src="{{ $o['cover'] }}" alt="{{ $o['title'] }}" loading="lazy" class="h-full w-full {{ $o['coverFit'] === 'contain' ? 'object-contain p-2 bg-white' : 'object-cover' }}">
          </div>
          <div class="p-6">
            <span class="rounded-full bg-brand-50 text-brand-700 text-xs font-medium px-3 py-1">{{ $o['category'] }}</span>
            <h3 class="mt-4 text-lg font-semibold leading-snug line-clamp-2 group-hover:text-brand-600 transition">{{ $o['title'] }}</h3>
            <p class="mt-2 text-sm text-gray-600 leading-relaxed line-clamp-3">{{ $o['excerpt'] }}</p>
          </div>
        </a>
        @endforeach
      </div>
    </div>
  </section>
  @endif
</main>
@endsection
