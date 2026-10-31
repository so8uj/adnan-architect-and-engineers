@extends('layouts.site')

@section('xdata', 'insightsPage()')

@section('content')
@verbatim
<main>
    <section data-page-hero class="relative overflow-hidden bg-white blueprint-light border-b border-gray-100">
      <div data-blob class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-brand-100/70"></div>
      <div class="relative mx-auto max-w-7xl px-4 py-16 lg:py-24">
        <nav data-ph class="text-sm text-gray-500"><a href="/" class="hover:text-brand-600">হোম</a> <span
            class="mx-2">/</span> <span class="text-ink-800">ইনসাইট</span></nav>
        <h1 data-ph class="mt-4 text-4xl sm:text-5xl font-bold leading-[1.4]">আমাদের <span class="text-gradient">ইনসাইট</span></h1>
        <p data-ph class="mt-4 max-w-2xl text-gray-600 text-lg">প্ল্যান পাস, ডিজাইন ও নির্মাণ নিয়ে দরকারি লেখা ও পরামর্শ।</p>
      </div>
    </section>

    <section class="py-16 lg:py-24">
      <div class="mx-auto max-w-7xl px-4">
        <div class="flex flex-wrap gap-2">
          <template x-for="c in categories" :key="c">
            <button @click="setFilter(c)" x-text="c" class="rounded-full px-5 py-2 text-sm font-medium border transition"
              :class="filter===c ? 'bg-brand-600 border-brand-600 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-500 hover:text-brand-600'"></button>
          </template>
        </div>

        <div x-ref="grid" class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <template x-for="a in filtered" :key="a.slug">
            <a :href="'/insight/' + a.slug" data-card
              class="group block rounded-2xl bg-white border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300">
              <div class="img-zoom aspect-[16/10] overflow-hidden bg-gray-100">
                <img :src="a.cover" :alt="a.title" loading="lazy" class="h-full w-full"
                  :class="a.coverFit === 'contain' ? 'object-contain p-2 bg-white' : 'object-cover'">
              </div>
              <div class="p-6">
                <div class="flex items-center gap-3 text-xs text-gray-500">
                  <span class="rounded-full bg-brand-50 text-brand-700 font-medium px-3 py-1" x-text="a.category"></span>
                  <span x-text="a.date + ' · ' + a.readTime + ' ' + t('পড়া')"></span>
                </div>
                <h3 class="mt-4 text-lg font-semibold leading-snug line-clamp-2 group-hover:text-brand-600 transition" x-text="a.title"></h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed line-clamp-3" x-text="a.excerpt"></p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-brand-600 group-hover:gap-3 transition-all">পড়ুন <span>→</span></span>
              </div>
            </a>
          </template>
        </div>
      </div>
    </section>
  </main>
@endverbatim
@endsection

@push('scripts')
@verbatim
<script>
    function insightsPage() {
      return {
        filter: t('সব'),
        items: window.INSIGHTS,
        get categories() { return [t('সব'), ...new Set(this.items.map(a => a.category))]; },
        get filtered() { return this.filter === t('সব') ? this.items : this.items.filter(a => a.category === this.filter); },
        setFilter(c) {
          this.filter = c;
          this.$nextTick(() => window.animateIn && window.animateIn(this.$refs.grid.querySelectorAll('[data-card]')));
        },
      };
    }
  </script>
@endverbatim
@endpush
