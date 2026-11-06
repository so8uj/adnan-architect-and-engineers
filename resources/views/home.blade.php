@extends('layouts.site')

@section('xdata', 'site()')

@section('content')
@verbatim
<main>
    <!-- HERO SLIDER v1: full-page Sobuj slider (admin -> Animation -> Hero slider style) -->
    <template x-if="window.HERO_CONFIG.style === 'v1'">
      <section id="home" data-hero x-data="sobujHero()" class="relative">
        <div id="sobuj-hero" style="height:100svh;min-height:560px"></div>
      </section>
    </template>

    <!-- HERO SLIDER v2 (white tone) -->
    <template x-if="window.HERO_CONFIG.style !== 'v1'">
    <section id="home" data-hero x-data="heroSlider()" class="hero-v2 relative overflow-hidden bg-white text-ink-800 blueprint-light"
      @mouseenter="paused = true" @mouseleave="paused = false" @pointermove="tilt($event)" @touchstart.passive="touchX = $event.touches[0].clientX"
      @touchend.passive="swipe($event.changedTouches[0].clientX)">
      <div data-blob class="absolute -top-40 -right-40 h-[32rem] w-[32rem] rounded-full bg-brand-100/70 blur-3xl"></div>
      <div data-blob class="absolute -bottom-40 -left-32 h-96 w-96 rounded-full bg-brand-50 blur-3xl"></div>

      <!-- engineering decoration (pure CSS/SVG, animated) -->
      <div class="hero-eng" aria-hidden="true">
        <svg class="eng-gear eng-gear-a" viewBox="0 0 200 200" fill="none" stroke="currentColor"><circle cx="100" cy="100" r="86" stroke-width="16" stroke-dasharray="11.25 11.25"/><circle cx="100" cy="100" r="76" stroke-width="8"/><circle cx="100" cy="100" r="26" stroke-width="6"/><path stroke-width="5" d="M100 74V30M100 126v44M74 100H30M126 100h44M81.6 81.6L50 50M118.4 118.4L150 150M118.4 81.6L150 50M81.6 118.4L50 150"/></svg>
        <svg class="eng-gear eng-gear-b" viewBox="0 0 200 200" fill="none" stroke="currentColor"><circle cx="100" cy="100" r="86" stroke-width="16" stroke-dasharray="11.25 11.25"/><circle cx="100" cy="100" r="76" stroke-width="8"/><circle cx="100" cy="100" r="30" stroke-width="8"/></svg>
        <svg class="eng-gear eng-gear-c" viewBox="0 0 200 200" fill="none" stroke="currentColor"><circle cx="100" cy="100" r="86" stroke-width="16" stroke-dasharray="11.25 11.25"/><circle cx="100" cy="100" r="76" stroke-width="8"/><circle cx="100" cy="100" r="26" stroke-width="6"/><path stroke-width="5" d="M100 74V30M100 126v44M74 100H30M126 100h44"/></svg>
        <svg class="eng-compass" viewBox="0 0 120 120" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="60" cy="60" r="54" stroke-dasharray="3 5"/><circle cx="60" cy="60" r="38"/><path d="M60 6v14M60 100v14M6 60h14M100 60h14M60 60l22-22"/><circle cx="60" cy="60" r="3" fill="currentColor"/></svg>
        <svg class="eng-cube eng-cube-a" viewBox="0 0 80 80" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M40 6l30 17v34L40 74 10 57V23z"/><path d="M40 40l30-17M40 40L10 23M40 40v34"/></svg>
        <svg class="eng-cube eng-cube-b" viewBox="0 0 80 80" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M40 6l30 17v34L40 74 10 57V23z"/><path d="M40 40l30-17M40 40L10 23M40 40v34"/></svg>
        <svg class="eng-dim" viewBox="0 0 260 30" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 4v22M258 4v22M2 15h256"/><path d="M2 15l9-5M2 15l9 5M258 15l-9-5M258 15l-9 5"/><rect x="100" y="7" width="60" height="16" rx="3" fill="rgb(var(--brand-50))" stroke="none"/><text x="130" y="19" text-anchor="middle" font-size="11" fill="currentColor" stroke="none" font-family="monospace">12.50 m</text></svg>
        <svg class="eng-skyline" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="2"><path d="M0 118h60V70h50v48h40V40h60v78h30V84h40v34h50V20h70v98h30V60h60v58h40V90h50v28h60V50h50v68h40V30h66v88h40V74h60v44h50V56h50v62h60V96h60v22h60V44h60v74h40V82h60v36"/></svg>
        <i class="eng-plus" style="top:16%;left:30%"></i><i class="eng-plus" style="top:70%;left:8%;animation-delay:-1.5s"></i>
        <i class="eng-plus" style="top:30%;left:52%;animation-delay:-3s"></i><i class="eng-plus" style="top:80%;left:46%;animation-delay:-4.5s"></i>
      </div>


      <!-- Prev / next: vertically centred on the left and right edges -->
      <button type="button" @click="prev()" aria-label="আগের স্লাইড"
        class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 z-20 grid place-items-center h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-brand-600 text-white text-xl shadow-lg shadow-brand-600/30 hover:bg-ink-950 hover:shadow-ink-950/30 transition">‹</button>
      <button type="button" @click="next()" aria-label="পরের স্লাইড"
        class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 z-20 grid place-items-center h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-brand-600 text-white text-xl shadow-lg shadow-brand-600/30 hover:bg-ink-950 hover:shadow-ink-950/30 transition">›</button>

      <div class="hv2-wrap">
        <!-- All slides share one grid cell, so the section height = tallest slide (no jumping) -->
        <div data-hero-slider class="hv2-stage">
          <template x-for="(s, n) in slides" :key="s.title1">
            <article class="hv2-slide" :class="{ 'is-on': n === i, 'is-leaving': n === prev && n !== i }" :aria-hidden="n !== i" :inert="n !== i">
              <div class="hv2-copy">
                <p class="hv2-eyebrow"><span class="hv2-dot"></span><span x-text="s.badge"></span></p>
                <h1 class="hv2-title">
                  <span x-text="s.title1"></span>
                  <span class="hv2-title-accent" x-text="s.title2"></span>
                </h1>
                <p class="hv2-text" x-text="s.text"></p>
                <div class="hv2-actions">
                  <a :href="s.cta.href" class="hv2-btn hv2-btn-primary"><span x-text="s.cta.label"></span><span aria-hidden="true">→</span></a>
                  <a :href="'tel:' + window.SITE.phone" class="hv2-btn hv2-btn-ghost">📞 <span x-text="window.SITE.phone"></span></a>
                </div>
              </div>

              <div class="hv2-visual-wrap">
                <div class="hv2-panel" aria-hidden="true"><i></i><i></i></div>
                <div class="hv2-roof" aria-hidden="true"></div>
                <div class="hv2-tower" aria-hidden="true"></div>
                <div class="hv2-visual">
                  <div class="hv2-img">
                    <img :src="s.image" :alt="s.title1 + ' ' + s.title2" :loading="n === 0 ? 'eager' : 'lazy'"
                      :class="s.fit === 'contain' ? 'is-contain' : ''" :style="'object-position:' + (s.pos || 'center')">
                    <div class="hv2-topline"><span>ARCHITECT / ENGINEER / BUILD</span><span x-text="String(n + 1).padStart(2, '0') + ' — ' + String(slides.length).padStart(2, '0')"></span></div>
                    <div class="hv2-chip">
                      <span class="hv2-chip-icon" x-text="s.chip.icon"></span>
                      <div>
                        <p class="hv2-chip-title" x-text="s.chip.title"></p>
                        <p class="hv2-chip-sub" x-text="s.chip.sub"></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          </template>
        </div>

        <!-- Slider controls -->
        <div class="hv2-controls">
          <template x-for="(s, n) in slides" :key="'d' + n">
            <button type="button" @click="go(n)" :aria-label="t('স্লাইড') + ' ' + (n + 1)" :aria-current="n === i" class="hv2-bar-btn">
              <span class="hv2-bar" :class="n === i && 'is-on'"><span :style="'width:' + (n === i ? progress : 0) + '%'"></span></span>
            </button>
          </template>
        </div>

      </div>
    </section>
    </template>

    <!-- Marquee of services: one plain brand-colour band, endless loop moving left -> right -->
    <div x-show="window.HERO_CONFIG.marquee" x-cloak class="bg-brand-600 text-white overflow-hidden py-4 select-none" aria-label="আমাদের সেবাসমূহ">
      <div class="flex whitespace-nowrap w-max" data-marquee="left">
        <!-- list is doubled so the GSAP loop (translateX -50% -> 0) is seamless -->
        <template x-for="(s,i) in [...services, ...services]" :key="i">
          <span class="flex items-center font-semibold text-lg sm:text-xl">
            <span class="px-6 sm:px-8" x-text="s.title"></span><span class="text-white/60">✦</span>
          </span>
        </template>
      </div>
    </div>

    <!-- INTRO / MD MESSAGE: full-screen video directly under the hero; scales OUT (shrinks to a rounded card) as you scroll (GSAP scrub in app.js) -->
    <section id="intro" class="sx-fullvideo relative overflow-hidden bg-white"
      x-data="{ playing: false, v: null, posterOk: !window.INTRO.poster,
        play() { const v = window.parseVideo(intro.video); if (!v) return; this.v = v; this.playing = true; },
        stop() { this.playing = false; this.v = null; } }"
      @keydown.escape.window="stop()">
      <div class="sx-fullvideo-wrap">
        <!-- no CSS transform/transition classes here: GSAP owns this element's scale -->
        <div data-scale-video class="sx-video-frame relative overflow-hidden bg-ink-950" :class="posterOk && 'is-ready'">
          <img :src="intro.poster" alt="" loading="eager" decoding="async" fetchpriority="high" class="sx-poster absolute inset-0 h-full w-full object-cover"
            @load="posterOk = true" x-init="$nextTick(() => { if ($el.complete && $el.naturalWidth) posterOk = true })">
          <div class="absolute inset-0 bg-ink-950/30"></div>

          <button type="button" @click="play()" :disabled="!hasIntroVideo" aria-label="ভিডিও চালু করুন"
            class="sx-play group absolute inset-0 grid place-items-center disabled:cursor-default">
            <span class="relative grid place-items-center h-20 w-20 sm:h-28 sm:w-28 rounded-full bg-brand-600 text-white shadow-2xl shadow-brand-600/50 group-hover:scale-110 transition duration-300">
              <span x-show="hasIntroVideo" class="absolute inset-0 rounded-full bg-brand-600 animate-ping opacity-40"></span>
              <svg class="relative h-9 w-9 sm:h-12 sm:w-12 ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
            </span>
          </button>

          <!-- inline player fills the frame -->
          <div x-show="playing" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0 z-20 bg-black">
            <template x-if="playing && v && v.kind === 'iframe'">
              <iframe :src="v.src" class="h-full w-full" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen title="ভিডিও"></iframe>
            </template>
            <template x-if="playing && v && v.kind === 'file'">
              <video :src="v.src" class="h-full w-full" controls autoplay playsinline></video>
            </template>
            <button @click="stop()" class="absolute top-3 right-4 z-30 grid place-items-center h-10 w-10 rounded-full bg-black/60 hover:bg-brand-600 text-white text-2xl leading-none transition" aria-label="বন্ধ করুন">&times;</button>
          </div>
        </div>
      </div>

      <!-- the 3 statistics (+ heading) sit around the video: they appear on the left, right, bottom and top while the video scales out -->
      <div class="sx-around" x-show="around.length" x-cloak>
        <template x-for="a in around" :key="a.pos">
          <p class="sx-around-stat" :class="'sx-around-' + { top: 't', left: 'l', right: 'r', bottom: 'b' }[a.pos]" :data-around="{ top: 't', left: 'l', right: 'r', bottom: 'b' }[a.pos]">
            <strong x-show="a.value !== null"><span data-around-count :data-count-to="a.value" :data-suffix="a.suffix" x-text="num(a.value) + a.suffix"></span></strong>
            <span class="sx-around-label" x-text="a.text"></span>
          </p>
        </template>
      </div>
    </section>

    <!-- SERVICES -->
    <section id="services" class="sx-sec sx-band-r py-20 lg:py-28 bg-gray-50">
      <div class="mx-auto max-w-7xl px-4">
        <div class="max-w-2xl" data-reveal>
          <p class="sx-eyebrow" x-show="sections.services.eyebrow"><span class="sx-ico"><svg class="sxa-tilt" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M3 17L17 3l4 4L7 21z"/><path d="M7 13l2 2M10 10l2 2M13 7l2 2"/></svg></span><span x-text="sections.services.eyebrow"></span></p>
          <h2 data-split class="mt-2 text-3xl sm:text-4xl font-bold" x-show="sections.services.title" x-text="sections.services.title"></h2>
          <p class="mt-4 text-gray-600" x-show="sections.services.text" x-text="sections.services.text"></p>
        </div>

        <div class="sx-services mt-12">
          <template x-for="(s,i) in services" :key="s.title">
            <article data-reveal data-shine
              class="sx-card group relative rounded-2xl bg-white p-6 border border-gray-100 hover:border-brand-500/40 hover:shadow-xl hover:-translate-y-1 transition duration-300">
              <div class="flex items-start justify-between">
                <span class="sx-card-ico">
                  <svg class="sxa-bob" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                    stroke-linejoin="round" viewBox="0 0 24 24" x-html="s.icon"></svg>
                </span>
                <span class="font-display text-3xl font-bold text-gray-100 group-hover:text-brand-100 transition"
                  x-text="String(i+1).padStart(2,'0')"></span>
              </div>
              <h3 class="mt-5 text-lg font-semibold" x-text="s.title"></h3>
              <p class="mt-2 text-sm text-gray-600 leading-relaxed" x-text="s.desc"></p>
            </article>
          </template>
        </div>
      </div>
    </section>

    <!-- PORTFOLIO -->
    <section id="portfolio" class="sx-sec sx-sec--dark py-20 lg:py-28 bg-ink-950 text-white">
      <div class="mx-auto max-w-7xl px-4" x-data="{ tab: 'completed' }">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6" data-reveal>
          <div class="max-w-2xl">
            <p class="sx-eyebrow sx-eyebrow--dark" x-show="sections.portfolio.eyebrow"><span class="sx-ico"><svg class="sxa-blink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M5 21V5l9-2v18"/><path d="M14 9h5v12"/><path class="w" d="M8 8h3M8 12h3M8 16h3M16.5 13h.01M16.5 17h.01"/><path d="M3 21h18"/></svg></span><span x-text="sections.portfolio.eyebrow"></span></p>
            <h2 data-split class="mt-2 text-3xl sm:text-4xl font-bold" x-show="sections.portfolio.title" x-text="sections.portfolio.title"></h2>
            <p class="mt-4 text-gray-600" x-show="sections.portfolio.text" x-text="sections.portfolio.text"></p>
          </div>
          <a :href="sections.portfolio.btn.url" x-show="sections.portfolio.btn.show && sections.portfolio.btn.label"
            class="inline-flex items-center gap-2 font-semibold text-brand-600 hover:gap-3 transition-all"><span x-text="sections.portfolio.btn.label"></span> <span>→</span></a>
        </div>

        <!-- the two main categories: Completed Projects / Running Projects -->
        <div class="sx-worktabs mt-8" role="tablist" data-reveal>
          <template x-for="st in ['completed', 'running']" :key="st">
            <button type="button" role="tab" @click="tab = st" :aria-selected="tab === st" :class="tab === st && 'is-on'">
              <span class="sx-worktabs-dot" :class="st"></span>
              <span x-text="WORK[st].label"></span>
              <em x-text="num(WORK[st].count)"></em>
            </button>
          </template>
        </div>
        <p class="sx-works-empty" x-show="!projects.some(q => q.status === tab)" x-cloak x-text="t('এই বিভাগে এখনো কোনো প্রজেক্ট নেই — শীঘ্রই আসছে।')"></p>
        <div class="sx-works mt-6">
          <template x-for="p in projects.filter(q => q.status === tab).slice(0, 4)" :key="p.slug">
            <a :href="'/project/' + p.slug" data-reveal="clip"
              class="group relative block rounded-3xl overflow-hidden bg-ink-900 ring-1 ring-white/10 hover:ring-brand-500/60 hover:-translate-y-1 hover:shadow-2xl transition duration-500">
              <div class="img-zoom sx-works-img overflow-hidden">
                <img :src="p.cover" :alt="p.title" loading="lazy" class="h-full w-full object-cover"
                  :style="'object-position:' + p.coverPos">
              </div>
              <span class="sx-go" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M8 7h9v9"/></svg></span>
              <div class="absolute inset-0 bg-gradient-to-t from-ink-950/90 via-ink-950/20 to-transparent"></div>
              <div class="absolute bottom-0 inset-x-0 p-6 sm:p-8 text-white">
                <div class="flex flex-wrap gap-2">
                  <span class="text-xs font-medium rounded-full bg-brand-600 px-3 py-1" x-text="p.category"></span>
                  <span class="text-xs font-medium rounded-full bg-white/15 backdrop-blur px-3 py-1"
                    x-text="num(p.images.length) + t('টি ড্রয়িং')"></span>
                  <span x-show="p.status === 'running'" class="sx-run-chip"><i></i><span x-text="t('চলমান')"></span></span>
                </div>
                <h3 class="mt-3 text-xl sm:text-2xl font-bold leading-snug" x-text="p.title"></h3>
                <p class="mt-1 text-sm text-gray-300" x-text="p.tagline"></p>
                <div class="sx-prog" x-show="p.status === 'running' && p.progress !== null"><div><i :style="'width:' + p.progress + '%'"></i></div><span x-text="num(p.progress) + '%'"></span></div>
                <span
                  class="mt-5 inline-flex items-center gap-2 text-sm font-semibold group-hover:gap-4 transition-all">বিস্তারিত
                  দেখুন <span>→</span></span>
              </div>
            </a>
          </template>
        </div>
      </div>
    </section>

    <!-- ACTIVITIES -->
    <section id="activities" class="sx-sec py-20 lg:py-28 bg-gray-50">
      <div class="mx-auto max-w-7xl px-4">
        <div class="max-w-2xl" data-reveal>
          <p class="sx-eyebrow" x-show="sections.activities.eyebrow"><span class="sx-ico"><svg class="sxa-flash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.6"/></svg></span><span x-text="sections.activities.eyebrow"></span></p>
          <h2 data-split class="mt-2 text-3xl sm:text-4xl font-bold" x-show="sections.activities.title" x-text="sections.activities.title"></h2>
          <p class="mt-4 text-gray-600" x-show="sections.activities.text" x-text="sections.activities.text"></p>
        </div>

        <div class="mt-12 columns-2 md:columns-3 gap-4 [&>*]:mb-4">
          <template x-for="(a,i) in activities" :key="a.src">
            <button type="button" @click="openLb(i)" data-reveal="clip"
              class="group relative block w-full break-inside-avoid overflow-hidden rounded-2xl bg-gray-200 cursor-zoom-in shadow-sm hover:shadow-2xl transition-shadow duration-500 text-left">
              <div class="img-zoom overflow-hidden">
                <img :src="a.src" :alt="a.title" loading="lazy" class="w-full h-auto block">
              </div>
              <div
                class="absolute inset-0 bg-gradient-to-t from-ink-950/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-500">
              </div>
              <div
                class="absolute bottom-0 inset-x-0 p-4 flex items-center justify-between text-white translate-y-3 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition duration-500">
                <span class="text-sm font-medium" x-text="a.title"></span>
                <span class="grid place-items-center h-9 w-9 rounded-full bg-brand-600">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M21 21l-4.3-4.3M11 8v6M8 11h6M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                  </svg>
                </span>
              </div>
            </button>
          </template>
        </div>
      </div>
    </section>

    <!-- PROCESS -->
    <section id="process" class="sx-sec sx-sec--red sx-gear py-16 lg:py-20">
      <div class="mx-auto max-w-7xl px-4">
        <div class="max-w-2xl" data-reveal>
          <p class="sx-eyebrow sx-eyebrow--dark" x-show="sections.process.eyebrow"><span class="sx-ico"><svg class="sxa-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><circle cx="12" cy="12" r="3.4"/><path d="M12 2.5v3M12 18.5v3M2.5 12h3M18.5 12h3M5.3 5.3l2.1 2.1M16.6 16.6l2.1 2.1M18.7 5.3l-2.1 2.1M7.4 16.6l-2.1 2.1"/></svg></span><span x-text="sections.process.eyebrow"></span></p>
          <h2 data-split class="mt-2 text-3xl sm:text-4xl font-bold" x-show="sections.process.title" x-text="sections.process.title"></h2>
          <p class="mt-4 text-gray-600" x-show="sections.process.text" x-text="sections.process.text"></p>
        </div>
        <div class="mt-12 relative">
          <div class="hidden lg:block absolute left-0 right-0 top-0 h-0.5 bg-white/10">
            <div data-progress class="h-full bg-brand-500"></div>
          </div>
          <ol class="lg:pt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <template x-for="(st,i) in steps" :key="st.title">
              <li data-reveal class="relative rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="sx-step">
                  <span class="sx-step-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" x-html="window.SX_STEP_ICONS[i % window.SX_STEP_ICONS.length]"></svg></span>
                  <span class="sx-step-n" x-text="String(i+1).padStart(2,'0')"></span>
                </div>
                <h3 class="mt-3 text-lg font-semibold" x-text="st.title"></h3>
                <p class="mt-2 text-sm text-gray-400 leading-relaxed" x-text="st.desc"></p>
              </li>
            </template>
          </ol>
        </div>
      </div>
    </section>

    <!-- WHY US + banner -->
    <section id="about" class="sx-sec sx-band-l py-20 lg:py-28">
      <div class="mx-auto max-w-7xl px-4 grid lg:grid-cols-2 gap-12 items-center">
        <div data-reveal="left" class="sx-rel rounded-3xl overflow-hidden shadow-xl ring-1 ring-gray-100">
          <div class="sx-badge" aria-hidden="true"><i></i><svg class="sxa-bob" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/></svg></div>
          <div class="overflow-hidden">
            <img data-parallax="0.04" src="/assets/img/571740278_830205606637811_8511991240464855537_n.jpg"
              alt="Adnan Architects & Engineers সেবাসমূহ" loading="lazy" class="w-full h-auto scale-110">
          </div>
        </div>
        <div>
          <div data-reveal>
            <p class="sx-eyebrow" x-show="sections.about.eyebrow"><span class="sx-ico"><svg class="sxa-draw" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path class="d" d="M8.5 12l2.5 2.5 4.5-5"/></svg></span><span x-text="sections.about.eyebrow"></span></p>
            <h2 data-split class="mt-2 text-3xl sm:text-4xl font-bold" x-show="sections.about.title" x-text="sections.about.title"></h2>
            <p class="mt-4 text-gray-600 leading-relaxed" x-show="sections.about.text" x-text="sections.about.text"></p>
          </div>
          <ul class="mt-8 space-y-5">
            <template x-for="w in why" :key="w.title">
              <li data-reveal="left" class="flex gap-4">
                <span class="sx-check mt-1 grid place-items-center h-6 w-6 shrink-0 rounded-full bg-brand-600 text-white">
                  <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </span>
                <div>
                  <h3 class="font-semibold" x-text="w.title"></h3>
                  <p class="text-sm text-gray-600" x-text="w.desc"></p>
                </div>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </section>

@endverbatim
@if (\App\Support\Features::on('clients'))
@include('partials.clients-section')
@endif
@verbatim
    <!-- TESTIMONIALS: media on the left, details on the right -->
    <section id="testimonials" class="sx-sec sx-sec--dark sx-band-r py-20 lg:py-28 bg-ink-950 text-white blueprint">
      <div class="mx-auto max-w-7xl px-4" x-data="{ cur: 0,
          go(n) { this.cur = (n + testimonials.length) % testimonials.length; } }">
        <div class="max-w-2xl" data-reveal>
          <p class="sx-eyebrow sx-eyebrow--dark" x-show="sections.testimonials.eyebrow"><span class="sx-ico"><svg class="sxa-bob" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M7 17h3l2-4V7H6v6h3"/><path d="M15 17h3l2-4V7h-6v6h3"/></svg></span><span x-text="sections.testimonials.eyebrow"></span></p>
          <h2 data-split class="mt-2 text-3xl sm:text-4xl font-bold leading-[1.4]" x-show="sections.testimonials.title" x-text="sections.testimonials.title"></h2>
          <p class="mt-4 text-gray-600" x-show="sections.testimonials.text" x-text="sections.testimonials.text"></p>
        </div>

        <div data-reveal class="mt-12 grid">
          <template x-for="(c, n) in testimonials" :key="n">
            <article class="col-start-1 row-start-1 grid lg:grid-cols-2 gap-8 lg:gap-14 items-center transition-opacity duration-700"
              :class="n === cur ? 'opacity-100 z-10' : 'opacity-0 pointer-events-none z-0'" :aria-hidden="n !== t" :inert="n !== t">

              <!-- LEFT: image / video / (no media) quote panel -->
              <div class="relative">
                <span x-show="c.placeholder" class="absolute top-3 right-3 z-10 rounded-full bg-ink-950/80 text-white text-xs px-3 py-1">নমুনা</span>

                <template x-if="c.type === 'image'">
                  <div class="aspect-[4/3] overflow-hidden rounded-3xl bg-gray-800 ring-1 ring-white/10 shadow-2xl">
                    <img :src="c.image" :alt="c.name" loading="lazy" class="h-full w-full object-cover">
                  </div>
                </template>

                <template x-if="c.type === 'video'">
                  <button type="button" @click="openVideo(c.video)" :disabled="!c.video" aria-label="ভিডিও চালু করুন"
                    class="group relative block w-full aspect-[4/3] overflow-hidden rounded-3xl bg-gray-800 ring-1 ring-white/10 shadow-2xl disabled:cursor-default">
                    <div class="img-zoom h-full w-full"><img :src="c.poster" alt="" loading="lazy" class="h-full w-full object-cover"></div>
                    <span class="absolute inset-0 bg-ink-950/30"></span>
                    <span class="absolute inset-0 grid place-items-center">
                      <span class="relative grid place-items-center h-20 w-20 rounded-full bg-brand-600 text-white shadow-xl shadow-brand-600/40 group-hover:scale-110 transition duration-300">
                        <span x-show="c.video" class="absolute inset-0 rounded-full bg-brand-600 animate-ping opacity-40"></span>
                        <svg class="relative h-8 w-8 ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                      </span>
                    </span>
                    <span x-show="!c.video" class="absolute bottom-4 left-4 rounded-full bg-white/90 text-ink-800 px-4 py-1.5 text-sm font-medium">ভিডিও শীঘ্রই আসছে</span>
                  </button>
                </template>

                <template x-if="c.type === 'text'">
                  <div class="aspect-[4/3] grid place-items-center rounded-3xl bg-gradient-to-br from-brand-600 to-brand-400 shadow-2xl overflow-hidden relative">
                    <img x-show="c.avatar" :src="c.avatar" alt="" class="absolute inset-0 h-full w-full object-cover">
                    <span x-show="!c.avatar" class="font-display text-[10rem] leading-none text-white/30">&ldquo;</span>
                  </div>
                </template>
              </div>

              <!-- RIGHT: details -->
              <div>
                <span class="font-display text-7xl leading-none text-brand-600/70">&ldquo;</span>
                <div class="-mt-3 flex gap-0.5 text-amber-400 text-xl" aria-label="রেটিং"><template x-for="s in (c.rating || 5)" :key="s"><span>★</span></template></div>
                <blockquote class="mt-4 text-xl sm:text-2xl leading-[1.7] text-gray-100" x-text="c.text"></blockquote>
                <div class="mt-7 flex items-center gap-4">
                  <img x-show="c.avatar" :src="c.avatar" alt="" class="h-14 w-14 rounded-full object-cover ring-2 ring-brand-600">
                  <span x-show="!c.avatar" class="grid place-items-center h-14 w-14 rounded-full bg-brand-600 text-white font-bold text-xl" x-text="c.name.charAt(0)"></span>
                  <div>
                    <p class="font-semibold text-lg leading-tight" x-text="c.name"></p>
                    <p class="text-sm text-gray-400" x-text="c.role"></p>
                  </div>
                </div>
              </div>
            </article>
          </template>
        </div>

        <!-- controls -->
        <div class="mt-10 flex items-center gap-4">
          <button type="button" @click="go(cur - 1)" aria-label="আগের"
            class="grid place-items-center h-11 w-11 rounded-full bg-brand-600 text-white text-xl hover:bg-white hover:text-ink-950 transition">‹</button>
          <button type="button" @click="go(cur + 1)" aria-label="পরের"
            class="grid place-items-center h-11 w-11 rounded-full bg-brand-600 text-white text-xl hover:bg-white hover:text-ink-950 transition">›</button>
          <div class="flex items-center gap-2 ml-2">
            <template x-for="(c, n) in testimonials" :key="'td' + n">
              <button type="button" @click="go(n)" :aria-label="t('মন্তব্য') + ' ' + (n + 1)" :aria-current="n === cur" class="flex items-center py-3">
                <span class="block h-2 rounded-full transition-all duration-500" :class="n === cur ? 'w-12 bg-brand-500' : 'w-6 bg-white/25 hover:bg-white/50'"></span>
              </button>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- INSIGHTS (slider) -->
    <section id="insights" class="sx-sec py-20 lg:py-28 bg-gray-50">
      <div class="mx-auto max-w-7xl px-4" x-data="{
          scrollBy(d) { const el = this.$refs.track; el.scrollBy({ left: d * (el.firstElementChild.offsetWidth + 24), behavior: 'smooth' }); }
        }">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6" data-reveal>
          <div class="max-w-2xl">
            <p class="sx-eyebrow" x-show="sections.insights.eyebrow"><span class="sx-ico"><svg class="sxa-glow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 00-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0012 3z"/></svg></span><span x-text="sections.insights.eyebrow"></span></p>
            <h2 data-split class="mt-2 text-3xl sm:text-4xl font-bold" x-show="sections.insights.title" x-text="sections.insights.title"></h2>
            <p class="mt-4 text-gray-600" x-show="sections.insights.text" x-text="sections.insights.text"></p>
          </div>
          <div class="flex items-center gap-3">
            <button type="button" @click="scrollBy(-1)" aria-label="আগের"
              class="grid place-items-center h-11 w-11 rounded-full bg-brand-600 text-white text-xl hover:bg-ink-950 transition">‹</button>
            <button type="button" @click="scrollBy(1)" aria-label="পরের"
              class="grid place-items-center h-11 w-11 rounded-full bg-brand-600 text-white text-xl hover:bg-ink-950 transition">›</button>
            <a :href="sections.insights.btn.url" x-show="sections.insights.btn.show && sections.insights.btn.label" class="ml-2 inline-flex items-center gap-2 rounded-full border border-gray-300 hover:border-brand-600 hover:text-brand-600 px-5 py-2.5 font-semibold transition"><span x-text="sections.insights.btn.label"></span> <span>→</span></a>
          </div>
        </div>

        <div data-reveal x-ref="track"
          class="mt-10 flex gap-6 overflow-x-auto snap-x snap-mandatory scroll-pl-4 no-scrollbar pb-4 -mx-4 px-4">
          <template x-for="a in insights" :key="a.slug">
            <a :href="'/insight/' + a.slug"
              class="group snap-start shrink-0 w-[85%] sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] rounded-2xl bg-white border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300">
              <div class="img-zoom aspect-[16/10] overflow-hidden bg-gray-100">
                <img :src="a.cover" :alt="a.title" loading="lazy" class="h-full w-full"
                  :class="a.coverFit === 'contain' ? 'object-contain p-2 bg-white' : 'object-cover'">
              </div>
              <div class="p-6">
                <div class="flex items-center gap-3 text-xs text-gray-500">
                  <span class="rounded-full bg-brand-50 text-brand-700 font-medium px-3 py-1" x-text="a.category"></span>
                  <span x-text="a.readTime + ' ' + t('পড়া')"></span>
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

    <!-- FAQ -->
    <section class="sx-sec py-20 lg:py-28">
      <div class="mx-auto max-w-3xl px-4">
        <div class="sx-eyebrow sx-eyebrow--center"><span class="sx-ico"><svg class="sxa-bob" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><circle cx="12" cy="12" r="9"/><path d="M9.6 9.4a2.4 2.4 0 114.6.9c0 1.6-2.2 1.8-2.2 3.4M12 17h.01"/></svg></span></div>
        <h2 data-split class="mt-4 text-3xl font-bold text-center" x-show="sections.faq.title" x-text="sections.faq.title"></h2>
          <p class="mt-3 text-center text-gray-600" x-show="sections.faq.text" x-text="sections.faq.text"></p>
        <div data-reveal="scale" class="mt-10 divide-y divide-gray-200 border-y border-gray-200" x-data="{open:0}">
          <template x-for="(f,i) in faqs" :key="f.q">
            <div>
              <button class="w-full flex items-center justify-between gap-4 py-5 text-left font-semibold"
                @click="open = open===i ? null : i" :aria-expanded="open===i">
                <span x-text="f.q"></span>
                <svg class="h-5 w-5 shrink-0 text-brand-600 transition duration-300" :class="open===i && 'rotate-45'"
                  fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
              </button>
              <div x-show="open===i" x-collapse x-cloak class="pb-5 text-gray-600 leading-relaxed" x-text="f.a"></div>
            </div>
          </template>
        </div>
      </div>
    </section>

    <!-- CONTACT -->
    <section id="contact"
      class="py-20 lg:py-28 bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 text-white relative overflow-hidden">
      <div class="absolute inset-0 blueprint opacity-40"></div>
      <div class="relative mx-auto max-w-7xl px-4 grid lg:grid-cols-2 gap-12">
        <div>
          <div data-reveal>
            <h2 data-split class="text-3xl sm:text-4xl font-bold leading-tight" x-show="sections.contact.title" x-text="sections.contact.title"></h2>
            <p class="mt-4 text-white/90 max-w-md" x-show="sections.contact.text" x-text="sections.contact.text"></p>
          </div>
          <div class="mt-8 space-y-4">
            <a data-reveal="left" :href="'tel:' + window.SITE.phone"
              class="flex items-center gap-4 rounded-2xl bg-white/15 hover:bg-white/25 backdrop-blur px-5 py-4 transition">
              <span class="sx-ico sx-ico--light"><svg class="sxa-shake" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z"/></svg></span>
              <span><span class="block text-sm text-white/80">কল করুন</span><span
                  class="block text-xl font-bold font-display" x-text="window.SITE.phone"></span></span>
            </a>
            <a data-reveal="left" :href="'mailto:' + window.SITE.email"
              class="flex items-center gap-4 rounded-2xl bg-white/15 hover:bg-white/25 backdrop-blur px-5 py-4 transition">
              <span class="sx-ico sx-ico--light"><svg class="sxa-bob" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
              <span><span class="block text-sm text-white/80">ইমেইল</span><span
                  class="block font-semibold break-all" x-text="window.SITE.email"></span></span>
            </a>
            <div data-reveal="left" class="flex items-center gap-4 rounded-2xl bg-white/15 backdrop-blur px-5 py-4">
              <span class="sx-ico sx-ico--light"><svg class="sxa-bounce" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
              <span><span class="block text-sm text-white/80">অফিস</span><span class="block font-semibold" x-text="window.SITE.address"></span></span>
            </div>
            <a data-reveal="left" href="https://www.facebook.com/adnanarchitectsandengineers/" target="_blank" rel="noopener"
              class="flex items-center gap-4 rounded-2xl bg-white/15 hover:bg-white/25 backdrop-blur px-5 py-4 transition">
              <span class="sx-ico sx-ico--light"><svg class="sxa-bob" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M21 12a8 8 0 01-11.6 7.1L4 20l1-4.4A8 8 0 1121 12z"/><path d="M8.5 12h.01M12 12h.01M15.5 12h.01"/></svg></span>
              <span><span class="block text-sm text-white/80">Facebook পেজ</span><span
                  class="block font-semibold">adnanarchitectsandengineers</span></span>
            </a>
          </div>
        </div>

        <!-- Frontend-only form: backend (Laravel) will be connected later -->
        <form data-reveal="right" class="rounded-3xl bg-white text-ink-800 p-6 sm:p-8 shadow-2xl self-start"
          @submit.prevent="submitForm()">
          <div class="grid sm:grid-cols-2 gap-4">
            <label class="block"><span class="text-sm font-medium">আপনার নাম</span>
              <input required x-model="form.name" type="text"
                class="mt-1 w-full rounded-xl border-gray-200 border px-4 py-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 outline-none"></label>
            <label class="block"><span class="text-sm font-medium">মোবাইল নম্বর</span>
              <input required x-model="form.phone" type="tel"
                class="mt-1 w-full rounded-xl border-gray-200 border px-4 py-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 outline-none"></label>
          </div>
          <label class="block mt-4"><span class="text-sm font-medium">কোন সেবা প্রয়োজন?</span>
            <select x-model="form.service"
              class="mt-1 w-full rounded-xl border-gray-200 border px-4 py-3 bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 outline-none">
              <option value="">নির্বাচন করুন</option>
              <template x-for="s in services" :key="s.title">
                <option x-text="s.title"></option>
              </template>
            </select></label>
          <label class="block mt-4"><span class="text-sm font-medium">বার্তা</span>
            <textarea rows="4" x-model="form.message"
              class="mt-1 w-full rounded-xl border-gray-200 border px-4 py-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 outline-none"
              placeholder="জমির আকার, অবস্থান ও প্রয়োজন সংক্ষেপে লিখুন"></textarea></label>
          <button :disabled="sending"
            class="mt-6 w-full rounded-xl bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white font-semibold py-3.5 transition">বার্তা পাঠান</button>
          <p x-show="sent" x-cloak x-transition class="mt-4 text-center text-sm text-green-600 font-medium">✔ ধন্যবাদ!
            আমরা শীঘ্রই যোগাযোগ করব।</p>
          <p x-show="error" x-cloak x-text="error" class="mt-4 text-center text-sm text-red-600 font-medium"></p>
        </form>
      </div>
    </section>
@endverbatim
@php $mapSrc = \App\Support\SiteData::mapSrc(); @endphp
@if ($mapSrc)
    <!-- FULL-SCREEN MAP (admin: General & Contact -> Google Maps embed). The overlay stops the map from stealing the page scroll until it is clicked. -->
    <section id="map" class="sx-map" onmouseleave="this.classList.remove('is-active')">
      <iframe src="{{ $mapSrc }}" title="Adnan Architect &amp; Engineers" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
      <button type="button" class="sx-map-lock" onclick="this.parentElement.classList.add('is-active')"><span>ম্যাপে ক্লিক করে ঘুরে দেখুন</span></button>
    </section>
@endif
@verbatim
  </main>

  <!-- Video player (intro + testimonials). Needs internet for YouTube/Facebook; local .mp4 files work offline. -->
  <div x-show="vid.open" x-cloak x-transition.opacity.duration.250ms data-lenis-prevent
    class="fixed inset-0 z-[95] bg-black/90 flex flex-col items-center justify-center p-4" role="dialog" aria-modal="true"
    @click.self="closeVideo()">
    <button @click="closeVideo()" class="absolute top-3 right-4 text-4xl leading-none text-white px-2" aria-label="বন্ধ করুন">&times;</button>
    <div class="w-full max-w-5xl aspect-video rounded-xl overflow-hidden bg-black shadow-2xl">
      <template x-if="vid.open && vid.kind === 'iframe'">
        <iframe :src="vid.src" class="h-full w-full" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen
          title="ভিডিও"></iframe>
      </template>
      <template x-if="vid.open && vid.kind === 'file'">
        <video :src="vid.src" class="h-full w-full" controls autoplay playsinline></video>
      </template>
    </div>
  </div>

  <!-- Activities lightbox -->
  <div x-show="lb.open" x-cloak x-transition.opacity.duration.250ms data-lenis-prevent
    @keydown.window="onKey($event)"
    class="fixed inset-0 z-[90] bg-black/90 flex flex-col" role="dialog" aria-modal="true" @click.self="closeLb()">
    <div class="flex items-center justify-between px-4 py-3 text-white">
      <span class="text-sm text-gray-300"
        x-text="num(lb.i+1) + ' / ' + num(activities.length)"></span>
      <button @click="closeLb()" class="text-3xl leading-none px-2" aria-label="বন্ধ করুন">&times;</button>
    </div>
    <div class="relative flex-1 min-h-0 flex items-center justify-center px-4 sm:px-16" @click.self="closeLb()">
      <button @click="prev()"
        class="absolute left-2 sm:left-5 z-10 grid place-items-center h-11 w-11 rounded-full bg-white/10 hover:bg-brand-600 text-white transition"
        aria-label="আগের ছবি">‹</button>
      <img x-ref="lbimg" :src="activities[lb.i].src" :alt="activities[lb.i].title"
        class="max-h-full max-w-full object-contain rounded-lg">
      <button @click="next()"
        class="absolute right-2 sm:right-5 z-10 grid place-items-center h-11 w-11 rounded-full bg-white/10 hover:bg-brand-600 text-white transition"
        aria-label="পরের ছবি">›</button>
    </div>
    <div class="px-4 py-4 text-center text-white">
      <p class="font-semibold" x-text="activities[lb.i].title"></p>
      <p class="text-sm text-gray-300" x-text="activities[lb.i].note || ''"></p>
    </div>
  </div>
@endverbatim
@endsection

@push('scripts')
<script>window.SX_STEP_ICONS = ["<path d=\"M3 17L17 3l4 4L7 21z\"/><path d=\"M7 13l2 2M10 10l2 2M13 7l2 2\"/>", "<path d=\"M4 20l1-4L16 5l3 3L8 19z\"/><path d=\"M14 7l3 3\"/>", "<path d=\"M12 3l8 4.5v9L12 21l-8-4.5v-9z\"/><path d=\"M12 12l8-4.5M12 12L4 7.5M12 12v9\"/>", "<path d=\"M5 21V5l9-2v18\"/><path d=\"M14 9h5v12\"/><path d=\"M8 8h3M8 12h3M8 16h3\"/>", "<circle cx=\"8\" cy=\"15\" r=\"4\"/><path d=\"M11 12l9-9M16 6l3 3M14 8l2 2\"/>", "<path d=\"M5 13l4 4L19 7\"/>"];</script>
<link rel="stylesheet" href="{{ \App\Support\Assets::v('assets/lib/sobuj_slider/sobuj-slider.css') }}">
<script src="{{ \App\Support\Assets::v('assets/lib/sobuj_slider/sobuj-slider.js') }}"></script>
<script>
  (function () {
    const v1 = window.HERO_CONFIG && window.HERO_CONFIG.style === 'v1';
    document.documentElement.classList.add(v1 ? 'hero-v1' : 'hero-v2');
  })();
</script>
@verbatim
<script>
    // Hero slider v1: the full-page Sobuj slider, fed by the same Hero sliders as v2
    function sobujHero() {
      const esc = (v) => String(v ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
      return {
        destroy() { if (this._slider && this._slider.timer) clearInterval(this._slider.timer); },
        init() {
          const cfg = Object.assign({ autoplay: true, interval: 6000, pauseOnHover: true }, window.HERO_CONFIG);
          const slides = (window.HERO_SLIDES || []).map((s) => ({
            image: s.image,
            pos: esc(s.pos),
            title: esc([s.title1, s.title2].filter(Boolean).join(' ')),
            description: esc(s.text),
            btnText: esc(s.cta && s.cta.label),
            btnLink: esc(s.cta && s.cta.href),
          }));
          if (!slides.length || !window.SobujSlider) return;
          const slider = this._slider = new window.SobujSlider('#sobuj-hero', { slides, duration: cfg.interval, height: '100svh', columns: cfg.columns || 4, arrows: cfg.arrows !== false, dots: cfg.dots !== false });
          document.getElementById('sobuj-hero').style.setProperty('--ss-overlay', (cfg.overlay ?? 45) / 100);
          const el = document.getElementById('sobuj-hero');
          const stop = () => slider.timer && clearInterval(slider.timer);
          if (!cfg.autoplay) setTimeout(stop, 300);
          else if (cfg.pauseOnHover) {
            el.addEventListener('mouseenter', stop);
            el.addEventListener('mouseleave', () => slider.startAutoPlay());
          }
        },
      };
    }

    function heroSlider() {
      const TICK = 60;
      const cfg = Object.assign({ autoplay: true, interval: 6000, pauseOnHover: true }, window.HERO_CONFIG);
      return {
        slides: window.HERO_SLIDES,
        i: 0, prev: -1, progress: 0, paused: false, touchX: 0,
        init() {
          const respect = !(window.SITE && window.SITE.respectReducedMotion === false);
          if (!cfg.autoplay || (respect && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) return;
          this.$watch('paused', (v) => { if (v && !cfg.pauseOnHover) this.paused = false; });
          this._timer = setInterval(() => {
            if (this.paused || document.hidden) return;
            this.progress += 100 * TICK / cfg.interval;
            if (this.progress >= 100) this.next();
          }, TICK);
        },
        tilt(e) {
          if (e.pointerType === 'touch' || document.documentElement.classList.contains('reduce-motion')) return;
          const r = this.$root.getBoundingClientRect();
          this.$root.style.setProperty('--tilt-y', (((e.clientX - r.left) / r.width - .5) * 7).toFixed(2) + 'deg');
          this.$root.style.setProperty('--tilt-x', ((-((e.clientY - r.top) / r.height - .5)) * 5).toFixed(2) + 'deg');
        },
        destroy() { clearInterval(this._timer); },   // Alpine calls this when Turbo swaps the page out
        go(n) { const t = (n + this.slides.length) % this.slides.length; if (t !== this.i) this.prev = this.i; this.i = t; this.progress = 0; },
        next() { this.go(this.i + 1); },
        prev() { this.go(this.i - 1); },
        swipe(x) {
          const d = x - this.touchX;
          if (Math.abs(d) > 50) d < 0 ? this.next() : this.prev();
        },
      };
    }

    function site() {
      const icon = {
        soil: '<path d="M3 20h18M5 20l2-6h10l2 6M9 14V8l3-4 3 4v6"/>',
        arch: '<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/>',
        struct: '<path d="M4 20V10l8-6 8 6v10M4 14h16M9 20v-6M15 20v-6"/>',
        elec: '<path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/>',
        fire: '<path d="M12 3c1 3 5 5 5 10a5 5 0 01-10 0c0-2 1-3 2-4 0 2 1 3 2 3 0-3-1-6 1-9z"/>',
        design: '<path d="M4 20l4-1L19 8a2.1 2.1 0 00-3-3L5 16l-1 4zM14 7l3 3"/>',
        cube: '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3zM12 12l8-4.5M12 12v9M12 12L4 7.5"/>',
        rajuk: '<path d="M3 10l9-6 9 6M5 10v8M9 10v8M15 10v8M19 10v8M3 20h18"/>',
        city: '<path d="M4 21V7l6-3v17M10 21V11l10 3v7M7 10h0M7 14h0M14 16h0M17 17h0"/>',
      };
      return {
        sent: false, sending: false, error: '',
        form: { name: '', phone: '', service: '', message: '' },
        projects: window.PROJECTS,
        activities: window.ACTIVITIES,
        insights: window.INSIGHTS,
        intro: window.INTRO,
        testimonials: window.TESTIMONIALS,
        clients: window.CLIENTS || [],
        clientsHead: window.CLIENTS_SECTION || {},
        vid: { open: false, kind: '', src: '' },
        get hasIntroVideo() { return !!window.parseVideo(this.intro.video); },
        openVideo(url) {
          const v = window.parseVideo(url);
          if (!v) return;
          this.vid = { open: true, ...v };
        },
        closeVideo() { this.vid = { open: false, kind: '', src: '' }; },
        lb: { open: false, i: 0 },
        init() {
          this.$watch('lb.open', (v) => { window.__lenis && (v ? window.__lenis.stop() : window.__lenis.start()); });
          this.$watch('vid.open', (v) => { window.__lenis && (v ? window.__lenis.stop() : window.__lenis.start()); });
          window.addEventListener('keydown', (e) => { if (e.key === 'Escape' && this.vid.open) this.closeVideo(); });
          this.$watch('lb.i', () => this.$nextTick(() => {
            if (window.gsap && this.$refs.lbimg) gsap.fromTo(this.$refs.lbimg, { opacity: 0, scale: .96 }, { opacity: 1, scale: 1, duration: .4, ease: 'power3.out' });
          }));
        },
        openLb(i) { this.lb.i = i; this.lb.open = true; },
        closeLb() { this.lb.open = false; },
        next() { this.lb.i = (this.lb.i + 1) % this.activities.length; },
        prev() { this.lb.i = (this.lb.i - 1 + this.activities.length) % this.activities.length; },
        onKey(e) {
          if (!this.lb.open) return;
          if (e.key === 'Escape') this.closeLb();
          if (e.key === 'ArrowRight') this.next();
          if (e.key === 'ArrowLeft') this.prev();
        },

        around: window.AROUND || [],
        sections: window.SECTIONS || {},
        WORK: window.WORK || {},
        services: window.SERVICES.map(s => ({ ...s, icon: icon[s.icon] })),
        steps: window.STEPS,
        why: window.WHY,
        faqs: window.FAQS,

        async submitForm() {
          this.sending = true; this.error = '';
          try {
            const res = await fetch('/contact', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
              body: JSON.stringify(this.form),
            });
            if (res.ok) {
              this.sent = true;
              this.form = { name: '', phone: '', service: '', message: '' };
              setTimeout(() => this.sent = false, 5000);
            } else {
              this.error = t('পাঠানো যায়নি, অনুগ্রহ করে আবার চেষ্টা করুন।');
            }
          } catch (e) {
            this.error = t('পাঠানো যায়নি, অনুগ্রহ করে আবার চেষ্টা করুন।');
          } finally {
            this.sending = false;
          }
        },
      };
    }
  </script>
@endverbatim
@endpush
