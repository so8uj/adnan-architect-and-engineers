@extends('layouts.site')

@section('xdata', 'portfolio()')

@section('content')
@verbatim
<main>
    <section data-page-hero class="relative overflow-hidden bg-white blueprint-light border-b border-gray-100">
      <div data-blob class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-brand-100/70"></div>
      <div class="relative mx-auto max-w-7xl px-4 py-16 lg:py-24">
        <nav data-ph class="text-sm text-gray-500"><a href="/" class="hover:text-brand-600">হোম</a> <span
            class="mx-2">/</span> <span class="text-ink-800">আমাদের কাজ</span></nav>
        <h1 data-ph class="mt-4 text-4xl sm:text-5xl font-bold leading-[1.4]">আমাদের <span class="text-gradient">কাজ</span></h1>
        <p data-ph class="mt-4 max-w-2xl text-gray-600 text-lg">ফ্লোর প্ল্যান, ড্রইং ও ৩ডি ডিজাইনের নমুনা। যেকোনো প্রজেক্টে ক্লিক করে সব ছবি ও
          বিস্তারিত দেখুন।</p>
      </div>
    </section>

    <section class="py-16 lg:py-24">
      <div class="mx-auto max-w-7xl px-4">
        <div class="flex flex-wrap gap-2" data-reveal>
          <template x-for="c in categories" :key="c">
            <button @click="setFilter(c)" x-text="c"
              class="rounded-full px-5 py-2 text-sm font-medium border transition"
              :class="filter===c ? 'bg-brand-600 border-brand-600 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-500 hover:text-brand-600'"></button>
          </template>
        </div>

        <div x-ref="grid" class="mt-8 grid md:grid-cols-2 gap-6">
          <template x-for="p in filtered" :key="p.slug">
            <a :href="'/project/' + p.slug" data-card
              class="group relative block rounded-3xl overflow-hidden bg-gray-100 shadow-sm hover:shadow-2xl transition-shadow duration-500">
              <div class="img-zoom aspect-[4/3] overflow-hidden">
                <img :src="p.cover" :alt="p.title" loading="lazy" class="h-full w-full object-cover"
                  :style="'object-position:' + p.coverPos">
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-ink-950/90 via-ink-950/20 to-transparent"></div>
              <div class="absolute bottom-0 inset-x-0 p-6 sm:p-8 text-white">
                <div class="flex flex-wrap gap-2">
                  <span class="text-xs font-medium rounded-full bg-brand-600 px-3 py-1" x-text="p.category"></span>
                  <span class="text-xs font-medium rounded-full bg-white/15 backdrop-blur px-3 py-1"
                    x-text="num(p.images.length) + t('টি ড্রইং')"></span>
                </div>
                <h2 class="mt-3 text-xl sm:text-2xl font-bold leading-snug" x-text="p.title"></h2>
                <p class="mt-1 text-sm text-gray-300" x-text="p.tagline"></p>
                <span
                  class="mt-5 inline-flex items-center gap-2 text-sm font-semibold group-hover:gap-4 transition-all">বিস্তারিত
                  দেখুন <span>→</span></span>
              </div>
            </a>
          </template>
        </div>

        <p class="mt-10 text-center text-sm text-gray-500" data-reveal>আরও কাজ দেখতে আমাদের
          <a href="https://www.facebook.com/adnanarchitectsandengineers/" target="_blank" rel="noopener"
            class="text-brand-600 font-semibold hover:underline">Facebook পেজ</a> ভিজিট করুন।
        </p>
      </div>
    </section>

    <section class="pb-20 lg:pb-28">
      <div data-reveal class="mx-auto max-w-5xl px-4">
        <div
          class="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-400 text-white p-8 sm:p-12 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
          <div>
            <h2 class="text-2xl sm:text-3xl font-bold">আপনার প্লটের জন্য নকশা করাতে চান?</h2>
            <p class="mt-2 text-white/90">জমির মাপ ও প্রয়োজন জানান, আমরা দ্রুত পরামর্শ দেব।</p>
          </div>
          <a href="/#contact" data-magnetic
            class="shrink-0 rounded-full bg-white text-brand-700 font-semibold px-7 py-3.5 hover:bg-brand-50 transition">যোগাযোগ করুন</a>
        </div>
      </div>
    </section>
  </main>
@endverbatim
@endsection

@push('scripts')
@verbatim
<script>
    function portfolio() {
      return {
        filter: t('সব'),
        projects: window.PROJECTS,
        get categories() { return [t('সব'), ...new Set(this.projects.map(p => p.category))]; },
        get filtered() { return this.filter === t('সব') ? this.projects : this.projects.filter(p => p.category === this.filter); },
        setFilter(c) {
          this.filter = c;
          this.$nextTick(() => window.animateIn && window.animateIn(this.$refs.grid.querySelectorAll('[data-card]')));
        },
      };
    }
  </script>
@endverbatim
@endpush
