{{-- Homepage "our clients" logo strip. Included by home.blade.php only when the "clients" future feature is switched on (config/features.php). --}}
@verbatim
    <!-- CLIENTS -->
    <section id="clients" x-show="clients.length" x-cloak class="py-16 lg:py-24 bg-gray-50">
      <div class="mx-auto max-w-7xl px-4">
        <div class="mx-auto max-w-2xl text-center" data-reveal>
          <p class="text-brand-600 font-semibold tracking-wide" x-text="clientsHead.subtitle || t('যাঁরা আমাদের ওপর আস্থা রেখেছেন')"></p>
          <h2 class="mt-2 text-3xl sm:text-4xl font-bold leading-[1.4]" x-text="clientsHead.title || t('আমাদের ক্লায়েন্ট')"></h2>
        </div>

        <div data-reveal class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
          <template x-for="c in clients" :key="c.name + (c.logo || '')">
            <a :href="c.url" :target="c.url ? '_blank' : null" rel="noopener" :title="c.name"
              class="group flex h-24 items-center justify-center rounded-2xl border border-gray-100 bg-[#fff] p-4 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl"
              :class="!c.url && 'cursor-default'">
              <img x-show="c.logo" :src="c.logo" :alt="c.name" loading="lazy"
                class="max-h-14 w-auto max-w-full object-contain opacity-70 grayscale transition duration-300 group-hover:opacity-100 group-hover:grayscale-0">
              <span x-show="!c.logo" x-text="c.name" class="text-center text-sm font-semibold text-[#4b5563] transition group-hover:text-brand-600"></span>
            </a>
          </template>
        </div>
      </div>
    </section>

@endverbatim
