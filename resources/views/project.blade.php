@extends('layouts.site')

@section('xdata', 'projectPage()')

@php
    use App\Support\SiteData;
    $p = SiteData::project($project, app()->getLocale());
    $others = collect($payload['PROJECTS'])->where('slug', '!=', $p['slug'])->values();
@endphp

@section('content')
<main>
  <section data-page-hero class="relative overflow-hidden bg-white blueprint-light border-b border-gray-100">
    <div data-blob class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-brand-100/70"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-14 lg:py-20">
      <nav data-ph class="text-sm text-gray-500">
        <a href="/" class="hover:text-brand-600">হোম</a><span class="mx-2">/</span>
        <a href="/portfolio" class="hover:text-brand-600">আমাদের কাজ</a><span class="mx-2">/</span>
        <span class="text-ink-800">{{ $p['category'] }}</span>
      </nav>
      <span data-ph class="mt-5 inline-block text-xs font-medium rounded-full bg-brand-600 text-white px-3 py-1">{{ $p['category'] }}</span>
      @if (($p['status'] ?? 'completed') === 'running')
        <span data-ph class="sx-run-chip sx-run-chip--solid"><i></i><span>চলমান</span></span>
        @if ($p['progress'] !== null)
          <div data-ph class="sx-prog sx-prog--page"><div><i style="width: {{ (int) $p['progress'] }}%"></i></div><span>{{ \App\Support\SiteData::digits((string) (int) $p['progress'], app()->getLocale()) }}%</span></div>
        @endif
      @endif
      <h1 data-ph class="mt-3 max-w-3xl text-3xl sm:text-5xl font-bold leading-[1.4]">{{ $p['title'] }}</h1>
      <p data-ph class="mt-4 max-w-2xl text-gray-600 text-lg">{{ $p['summary'] }}</p>
    </div>
  </section>

  <section class="py-14 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 grid lg:grid-cols-3 gap-10">
      <div class="lg:col-span-2">
        <div class="grid gap-5 {{ count($p['images']) > 1 ? 'sm:grid-cols-2' : '' }}">
          @foreach ($p['images'] as $i => $img)
          <figure data-reveal class="group {{ count($p['images']) === 1 ? 'max-w-2xl mx-auto w-full' : '' }}">
            <button type="button" @click="openLb({{ $i }})"
              class="relative block w-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm hover:shadow-xl transition-shadow duration-500 text-left cursor-zoom-in">
              <div class="img-zoom overflow-hidden">
                <img src="{{ $img['src'] }}" alt="{{ $img['title'] }}" loading="lazy" class="w-full h-auto">
              </div>
              <span class="absolute top-3 right-3 grid place-items-center h-9 w-9 rounded-full bg-ink-950/70 text-white opacity-0 group-hover:opacity-100 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 21l-4.3-4.3M11 8v6M8 11h6M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
              </span>
            </button>
            <figcaption class="mt-3 px-1">
              <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-semibold">{{ $img['title'] }}</h3>
                @if ($img['size'])
                <span class="text-xs font-medium rounded-full bg-brand-50 text-brand-700 px-2.5 py-0.5">{{ $img['size'] }}</span>
                @endif
              </div>
              <p class="mt-1 text-sm text-gray-600 leading-relaxed">{{ $img['note'] }}</p>
            </figcaption>
          </figure>
          @endforeach
        </div>

        <div class="mt-14" data-reveal>
          <h2 class="text-2xl font-bold">প্রজেক্ট বিবরণ</h2>
          <div class="mt-4 space-y-4 text-gray-600 leading-relaxed">
            @foreach ($p['description'] as $para)
            <p>{{ $para }}</p>
            @endforeach
          </div>

          @if (count($p['highlights']))
          <h3 class="mt-8 text-lg font-semibold">বিশেষ দিক</h3>
          <ul class="mt-4 grid sm:grid-cols-2 gap-3">
            @foreach ($p['highlights'] as $h)
            <li class="flex gap-3 rounded-xl bg-gray-50 p-4">
              <span class="mt-0.5 grid place-items-center h-5 w-5 shrink-0 rounded-full bg-brand-600 text-white">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
              </span>
              <span class="text-sm">{{ $h }}</span>
            </li>
            @endforeach
          </ul>
          @endif
        </div>
      </div>

      <aside class="lg:sticky lg:top-24 self-start" data-reveal>
        <div class="rounded-3xl border border-gray-100 bg-white shadow-lg p-6">
          <h2 class="text-lg font-bold">প্রজেক্ট তথ্য</h2>
          <dl class="mt-4 divide-y divide-gray-100">
            @foreach ($p['meta'] as $m)
            <div class="py-3 flex justify-between gap-4 text-sm">
              <dt class="text-gray-500">{{ $m['label'] ?? '' }}</dt>
              <dd class="font-medium text-right">{{ $m['value'] ?? '' }}</dd>
            </div>
            @endforeach
          </dl>
          <a href="/#contact" data-magnetic class="mt-6 block text-center rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3.5 transition">এমন নকশা করাতে চাই</a>
          <a :href="'tel:' + SITE.phone" class="mt-3 block text-center rounded-xl border border-gray-200 hover:border-brand-500 hover:text-brand-600 font-semibold py-3.5 transition">📞 <span x-text="SITE.phone"></span></a>
        </div>
      </aside>
    </div>
  </section>

  @if ($others->count())
  <section class="pb-20 lg:pb-28 bg-gray-50 pt-16">
    <div class="mx-auto max-w-7xl px-4">
      <h2 class="text-2xl sm:text-3xl font-bold" data-reveal>আরও প্রজেক্ট</h2>
      <div class="mt-8 grid md:grid-cols-2 gap-6">
        @foreach ($others as $o)
        <a href="/project/{{ $o['slug'] }}" data-reveal class="group relative block rounded-3xl overflow-hidden bg-gray-100 shadow-sm hover:shadow-2xl transition-shadow duration-500">
          <div class="img-zoom aspect-[16/10] overflow-hidden">
            <img src="{{ $o['cover'] }}" alt="{{ $o['title'] }}" loading="lazy" class="h-full w-full object-cover" style="object-position: {{ $o['coverPos'] }}">
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-ink-950/90 via-ink-950/20 to-transparent"></div>
          <div class="absolute bottom-0 inset-x-0 p-6 text-white">
            <span class="text-xs font-medium rounded-full bg-brand-600 px-3 py-1">{{ $o['category'] }}</span>
            <h3 class="mt-3 text-xl font-bold">{{ $o['title'] }}</h3>
          </div>
        </a>
        @endforeach
      </div>
    </div>
  </section>
  @endif
</main>

<!-- Lightbox -->
<div x-show="lb.open" x-cloak x-transition.opacity.duration.250ms data-lenis-prevent
  class="fixed inset-0 z-[90] bg-black/90 flex flex-col" role="dialog" aria-modal="true" @click.self="closeLb()" @keydown.window="onKey($event)">
  <div class="flex flex-col h-full" @click.self="closeLb()">
    <div class="flex items-center justify-between px-4 py-3 text-white">
      <span class="text-sm text-gray-300" x-text="num(lb.i+1) + ' / ' + num(images.length)"></span>
      <button @click="closeLb()" class="text-3xl leading-none px-2" aria-label="বন্ধ করুন">&times;</button>
    </div>
    <div class="relative flex-1 min-h-0 flex items-center justify-center px-4 sm:px-16" @click.self="closeLb()">
      <button x-show="images.length > 1" @click="prev()" class="absolute left-2 sm:left-5 z-10 grid place-items-center h-11 w-11 rounded-full bg-white/10 hover:bg-brand-600 text-white transition" aria-label="আগের ছবি">‹</button>
      <img x-ref="lbimg" :src="images[lb.i].src" :alt="images[lb.i].title" class="max-h-full max-w-full object-contain rounded-lg bg-white">
      <button x-show="images.length > 1" @click="next()" class="absolute right-2 sm:right-5 z-10 grid place-items-center h-11 w-11 rounded-full bg-white/10 hover:bg-brand-600 text-white transition" aria-label="পরের ছবি">›</button>
    </div>
    <div class="px-4 py-4 text-center text-white">
      <p class="font-semibold" x-text="images[lb.i].title + (images[lb.i].size ? ' — ' + images[lb.i].size : '')"></p>
      <p class="text-sm text-gray-300" x-text="images[lb.i].note"></p>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function projectPage() {
    const project = (window.PROJECTS || []).find(p => p.slug === window.PAGE_SLUG);
    return {
      images: project ? project.images : [],
      lb: { open: false, i: 0 },
      init() {
        this.$watch('lb.open', (v) => { window.__lenis && (v ? window.__lenis.stop() : window.__lenis.start()); });
        this.$watch('lb.i', () => this.$nextTick(() => {
          if (window.gsap && this.$refs.lbimg) gsap.fromTo(this.$refs.lbimg, { opacity: 0, scale: .96 }, { opacity: 1, scale: 1, duration: .4, ease: 'power3.out' });
        }));
      },
      openLb(i) { this.lb.i = i; this.lb.open = true; },
      closeLb() { this.lb.open = false; },
      next() { this.lb.i = (this.lb.i + 1) % this.images.length; },
      prev() { this.lb.i = (this.lb.i - 1 + this.images.length) % this.images.length; },
      onKey(e) {
        if (!this.lb.open) return;
        if (e.key === 'Escape') this.closeLb();
        if (e.key === 'ArrowRight') this.next();
        if (e.key === 'ArrowLeft') this.prev();
      },
    };
  }
</script>
@endpush
