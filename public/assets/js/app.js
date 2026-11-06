/*
 * Site-wide motion: Lenis smooth scroll + GSAP / ScrollTrigger.
 *
 * Markup hooks (data attributes):
 *   data-reveal[=left|right|scale|clip]   fade/slide in when scrolled into view (batched + staggered)
 *   data-split              heading animates word by word
 *   data-parallax="0.15"    image drifts inside its overflow-hidden parent
 *   data-scale-video        scales 1 -> small (scrubbed, "scale out") while its section is pinned; [data-around] items fade in around it
 *   data-count="500"       number counts up (data-suffix="+")
 *   data-magnetic           element follows the cursor slightly
 *   data-progress           scaleX scrubbed by scroll (process line)
 *   data-float              gentle endless bobbing
 *   (marquee is pure CSS + assets/js/marquee.js)
 *   data-shine              cursor-following highlight on cards
 *   data-hero + [data-hero-*] / [data-line]   intro timeline on the home page
 */
(function () {
  // OS "reduce motion" switches all animation off (no Lenis, no GSAP). Set SITE.respectReducedMotion = false in data.js to override.
  const respect = !(window.SITE && window.SITE.respectReducedMotion === false);
  const reduce = respect && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) document.documentElement.classList.add('reduce-motion');
  let curtain = null;   // looked up again on every page (Turbo swaps the <body>)
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

  // Preloader rule: the house + logo animation always plays to the end at least once (LOGO_MS), then the curtain
  // lifts as soon as the page has finished loading. Page already loaded -> it leaves right after the logo is shown;
  // page still loading -> it waits for the load, then leaves. The admin "minimum time" can only make it longer.
  // Admin: Animation & preloader (show / first visit only / minimum time).
  const LOGO_MS = 4300;   // ph-* animations in style.css finish at ~3.8 s + a short hold on the finished logo
  const pre = Object.assign({ show: true, minMs: 0, onlyFirstVisit: false }, window.SITE && window.SITE.preloader);
  let seen = false;
  try { seen = !!sessionStorage.getItem('preloaderSeen'); sessionStorage.setItem('preloaderSeen', '1'); } catch (e) { /* ignore */ }
  const skipPreloader = !pre.show || (pre.onlyFirstVisit && seen);
  // resolves when the logo has been shown long enough AND the page is loaded (15 s failsafe so it can never get stuck)
  const curtainReady = () => {
    if (skipPreloader) return Promise.resolve();
    const t0 = window.__preT0 || 0;
    const shown = new Promise((res) => setTimeout(res, Math.max(0, t0 + Math.max(pre.minMs, LOGO_MS) - performance.now())));
    const loaded = new Promise((res) => (document.readyState === 'complete' ? res() : window.addEventListener('load', res, { once: true })));
    const failsafe = new Promise((res) => setTimeout(res, 15000));
    return Promise.race([Promise.all([shown, loaded]), failsafe]);
  };

  // ---- page-transition feedback (Turbo Drive) -------------------------------------------------------------------------------
  // click -> the old page dims at once (and, if the server is slow, a shimmer skeleton with the same layout covers it);
  // the new page is swapped in invisible (body.is-entering), built by boot(), then fades in. No flash, no jumping heights.
  const rootEl = document.documentElement;
  let skTimer = null;
  const skeletonHtml = `
    <div class="sk-top">
      <div class="sk-strip"></div>
      <div class="sk-head"><div class="sk sk-logo"></div><div class="sk-nav"><i class="sk"></i><i class="sk"></i><i class="sk"></i><i class="sk"></i><i class="sk"></i><i class="sk"></i></div><div class="sk sk-cta"></div></div>
    </div>
    <div class="sk-wrap">
      <div class="sk-row">
        <div class="sk-col">
          <div class="sk sk-pill"></div><div class="sk sk-h1"></div><div class="sk sk-h1 sk-h1--short"></div>
          <div class="sk sk-line"></div><div class="sk sk-line sk-line--short"></div>
          <div class="sk-btns"><div class="sk sk-btn"></div><div class="sk sk-btn sk-btn--ghost"></div></div>
        </div>
        <div class="sk sk-media"></div>
      </div>
      <div class="sk-cards"><div class="sk sk-card"></div><div class="sk sk-card"></div><div class="sk sk-card"></div></div>
    </div>`;
  const buildSkeleton = (instant) => {
    const el = document.createElement('div');
    el.id = 'page-skeleton';
    el.setAttribute('aria-hidden', 'true');
    el.innerHTML = skeletonHtml;
    if (instant) el.classList.add('is-on', 'no-fade');
    return el;
  };
  const showSkeleton = () => {
    if (document.getElementById('page-skeleton')) return;
    const el = buildSkeleton(false);
    document.body.appendChild(el);
    requestAnimationFrame(() => el.classList.add('is-on'));
  };
  const hideSkeleton = () => {
    clearTimeout(skTimer);
    const el = document.getElementById('page-skeleton');
    if (!el) return;
    el.classList.remove('no-fade', 'is-on');           // fades out, then goes
    setTimeout(() => el.remove(), 320);
  };
  const leaving = () => {
    clearTimeout(skTimer);
    skTimer = setTimeout(showSkeleton, 400);       // only when the page really takes a while; a fast page swaps in directly
  };
  if (document.querySelector('script[src*="turbo"]')) {
    if (window.Turbo && Turbo.setProgressBarDelay) Turbo.setProgressBarDelay(120);
    document.addEventListener('turbo:visit', leaving);
    document.addEventListener('turbo:before-render', (e) => {
      clearTimeout(skTimer);
      if (document.getElementById('page-skeleton')) {
        // slow load: the skeleton is already on screen -> the new body arrives with it and stays hidden underneath until boot() is done
        e.detail.newBody.classList.add('is-entering');
        e.detail.newBody.appendChild(buildSkeleton(true));
        setTimeout(() => { document.body.classList.remove('is-entering'); hideSkeleton(); }, 2500);   // failsafe
      }
    });
    document.addEventListener('turbo:fetch-request-error', hideSkeleton);
  }
  const entered = () => requestAnimationFrame(() => { document.body.classList.remove('is-entering'); hideSkeleton(); });

  // pages after the first one appear in place: whatever is already on screen is NOT animated in from below again
  // (that read as "white screen, then the page sliding up"); only content below the fold still reveals on scroll
  let visits = 0;
  let instantEntry = false;
  const inView = (el) => el.getBoundingClientRect().top < window.innerHeight * 0.92;

  const motionOff = () => !window.gsap || !window.ScrollTrigger || reduce;

  // no GSAP motion (reduced motion / offline libs): still show the preloader for its minimum time, then fade it out
  function fallbackBoot() {
    if (curtain && curtain.style.display !== 'none') {
      const c = curtain;
      curtainReady().then(() => {
        c.style.transition = 'opacity .45s ease';
        c.style.opacity = '0';
        setTimeout(() => c.remove(), 500);
      });
    } else if (curtain) curtain.remove();
  }

  // ---- page lifecycle (Turbo Drive: pages are swapped without a reload, so everything must be built per page and cleaned up) ----
  const cleanups = [];
  const onWin = (ev, fn) => { window.addEventListener(ev, fn); cleanups.push(() => window.removeEventListener(ev, fn)); };

  function teardown() {
    cleanups.splice(0).forEach((f) => f());
    if (window.ScrollTrigger) ScrollTrigger.getAll().forEach((t) => t.kill());
    if (window.gsap) gsap.globalTimeline.clear();
    if (window.__lenis) {
      if (window.gsap && window.__lenisRaf) gsap.ticker.remove(window.__lenisRaf);
      window.__lenis.destroy();
      window.__lenis = null;
    }
    // state set by page scripts of the page we are leaving
    const root = document.documentElement;
    root.classList.remove('hero-v1', 'hero-v2');
    root.style.removeProperty('--hdr-h');
  }

  // Re-usable by pages after Alpine re-renders a list (e.g. portfolio filter)
  // Tailwind's `transition` classes also animate transform/opacity, which would fight GSAP's per-frame updates.
  // So CSS transitions are switched off while GSAP drives an element, then restored.
  const lockCss = (els) => $$_(els).forEach((e) => { e.style.transition = 'none'; });
  const unlockCss = (els) => $$_(els).forEach((e) => { e.style.transition = ''; });
  function $$_(x) { return typeof x === 'string' ? Array.from(document.querySelectorAll(x)) : Array.from(x); }

  window.animateIn = (els) => {
    lockCss(els);
    return gsap.fromTo(els,
      { opacity: 0, y: 40 },
      { opacity: 1, y: 0, duration: .7, stagger: .08, ease: 'power3.out', clearProps: 'transform', onComplete: () => unlockCss(els) });
  };

  function smoothScroll() {
    if (!window.Lenis) return null;
    // duration + exponential ease = the familiar "buttery" Lenis feel (a bare lerp stops abruptly)
    const lenis = new Lenis({
      duration: 0.9,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      smoothWheel: true,
      wheelMultiplier: 1.25,
      touchMultiplier: 1.5,
    });
    window.__lenis = lenis;
    lenis.on('scroll', ScrollTrigger.update);
    window.__lenisRaf = (t) => lenis.raf(t * 1000);
    gsap.ticker.add(window.__lenisRaf);              // one shared clock for Lenis + GSAP
    gsap.ticker.lagSmoothing(0);
    ScrollTrigger.config({ ignoreMobileResize: true });
    return lenis;
  }

  const norm = (p) => p.replace(/index\.html$/, '');

  function links() {
    if (window.__linksBound) return;
    window.__linksBound = true;
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a[href]');
      if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank' || a.hasAttribute('download')) return;
      if (a.protocol !== location.protocol || (location.protocol !== 'file:' && a.host !== location.host)) return;

      const samePage = norm(a.pathname) === norm(location.pathname);
      if (samePage && a.hash) {                       // in-page anchor -> smooth scroll
        const target = $(a.hash);
        if (!target) return;
        e.preventDefault();
        window.__lenis ? window.__lenis.scrollTo(target, { offset: -70, duration: 1.1 }) : target.scrollIntoView({ behavior: 'smooth' });
        history.pushState(null, '', a.hash);
        return;
      }
      if (samePage && !a.hash) return;
      // other page -> Turbo Drive swaps the page (SPA-style): no reload, no curtain wipe, no preloader
    });
    // coming back via the browser's back button (bfcache)
    window.addEventListener('pageshow', (e) => { if (e.persisted && curtain) curtain.style.display = 'none'; });
  }

  function intro(done) {
    if (!curtain || curtain.style.display === 'none') return done();
    gsap.set(curtain, { visibility: 'visible', opacity: 1 });
    // the logo animation is pure CSS and starts with the page: do not fade it in again here, only wait, then leave
    curtainReady().then(() => {
      gsap.timeline({ onComplete: () => { curtain.style.display = 'none'; } })
        .to('[data-curtain-logo]', { opacity: 0, y: -14, duration: .3, ease: 'power2.in' })
        .to(curtain, { yPercent: -100, duration: .85, ease: 'expo.inOut' }, '-=.05')
        .add(done, '-=.45');
    });
  }

  function hero() {
    if (!$('[data-hero]')) return null;
    // built paused so the hidden start state is applied immediately; played when the curtain lifts
    const tl = gsap.timeline({ paused: true, defaults: { ease: 'power3.out' } });
    // slide content is animated by CSS (index.html heroSlider); GSAP only fades the whole slider in
    if (!instantEntry) tl.from('[data-hero-slider]', { opacity: 0, y: 30, duration: .9 });

    // drifting background blobs
    $$('[data-blob]').forEach((el, i) => gsap.to(el, {
      x: i % 2 ? -50 : 60, y: i % 2 ? 40 : -50, duration: 7 + i * 2, repeat: -1, yoyo: true, ease: 'sine.inOut',
    }));
    return tl;
  }

  function pageHeroes() {
    const el = $('[data-page-hero]');
    if (!el || instantEntry) return;
    gsap.from($$('[data-page-hero] [data-ph]'), { opacity: 0, y: 36, duration: .9, stagger: .12, ease: 'power3.out', delay: 1 });
  }

  function reveals() {
    const items = $$('[data-reveal]').filter((el) => !(instantEntry && inView(el)));
    const from = {
      left: { opacity: 0, x: -70 },
      right: { opacity: 0, x: 70 },
      scale: { opacity: 0, y: 30, scale: .92 },
      clip: { opacity: 0, y: 60, clipPath: 'inset(0 0 100% 0 round 1.5rem)' },
    };
    lockCss(items);
    items.forEach((el) => gsap.set(el, from[el.dataset.reveal] || { opacity: 0, y: 44 }));
    ScrollTrigger.batch(items, {
      start: 'top 90%', once: true,
      onEnter: (els) => gsap.to(els, {
        opacity: 1, x: 0, y: 0, scale: 1, clipPath: 'inset(0 0 0% 0 round 1.5rem)',
        duration: 1, stagger: .12, ease: 'power3.out', overwrite: true,
        onComplete: () => { gsap.set(els, { clearProps: 'transform,clipPath' }); unlockCss(els); },
      }),
    });
  }

  // Headings: split into words and slide each up from behind a mask
  function splitHeadings() {
    $$('[data-split]').forEach((h) => {
      if (instantEntry && inView(h)) return;
      const words = h.textContent.trim().split(/\s+/);
      h.setAttribute('aria-label', h.textContent.trim());
      h.innerHTML = words.map((w) =>
        `<span aria-hidden="true" style="display:inline-block;overflow:hidden;vertical-align:top;padding:.2em .1em;margin:-.2em -.1em"><span data-w style="display:inline-block">${w}</span></span>`
      ).join(' ');
      gsap.from($$('[data-w]', h), {
        yPercent: 120, rotate: 4, duration: .9, stagger: .07, ease: 'expo.out',
        scrollTrigger: { trigger: h, start: 'top 88%', once: true },
      });
    });
  }

  // Extra per-section motion
  function sectionExtras() {
    // service icons pop in with a little spin once their card is visible
    const fresh = (els) => els.filter((e) => !(instantEntry && inView(e)));
    fresh($$('#services article svg')).forEach((svg) => gsap.from(svg, {
      rotate: -90, scale: .3, opacity: 0, duration: .9, ease: 'back.out(1.8)',
      scrollTrigger: { trigger: svg, start: 'top 92%', once: true },
    }));
    // process numbers drop in
    fresh($$('#process li > span')).forEach((n) => gsap.from(n, {
      y: -30, opacity: 0, scale: 1.6, duration: .8, ease: 'power3.out',
      scrollTrigger: { trigger: n, start: 'top 92%', once: true },
    }));
    // ticks in the "why us" list
    fresh($$('#about li > span')).forEach((t) => gsap.from(t, {
      scale: 0, rotate: -180, duration: .7, ease: 'back.out(2)',
      scrollTrigger: { trigger: t, start: 'top 92%', once: true },
    }));
    // section colour blobs drift with scroll
    $$('[data-blob]').forEach((b) => {
      if (b.closest('[data-hero]')) return;
      gsap.to(b, { yPercent: 30, ease: 'none', scrollTrigger: { trigger: b.parentElement, start: 'top bottom', end: 'bottom top', scrub: true } });
    });
  }

  function parallax() {
    $$('[data-parallax]').forEach((el) => {
      const amt = parseFloat(el.dataset.parallax) || .12;
      gsap.fromTo(el, { yPercent: -amt * 100 }, {
        yPercent: amt * 100, ease: 'none',
        scrollTrigger: { trigger: el.parentElement, start: 'top bottom', end: 'bottom top', scrub: true },
      });
    });
  }

  // Intro video, SCALE OUT: starts full screen (square corners, directly under the hero, no gap). While the section is pinned the
  // video shrinks to a small rounded card and the three statistics + heading fade in around it (left / right / bottom / top)
  // and count up. Scrubbed, so everything reverses when scrolling back up.
  function scaleVideo() {
    const phone = window.matchMedia('(max-width: 767px)').matches;
    const small = phone ? .6 : .26;
    $$('[data-scale-video]').forEach((el) => {
      const section = el.closest('section');
      const around = $$('[data-around]', section);
      // the sticky header covers the top of the screen: the pinned video starts right under it and fills the rest (nothing is cut off)
      const hdr = () => (document.getElementById('site-header') || { offsetHeight: 0 }).offsetHeight;
      const setHdr = () => section.style.setProperty('--hdr-h', hdr() + 'px');
      setHdr(); onWin('resize', setHdr);
      section.style.setProperty('--vs', small);   // lets the CSS place the texts right next to the shrunken video
      gsap.set(el, { transformOrigin: '50% 50%', willChange: 'transform' });
      const tl = gsap.timeline({
        defaults: { ease: 'none' },
        scrollTrigger: {
          trigger: section, scrub: .5, invalidateOnRefresh: true,
          // desktop / tablet: pinned under the header for 1.5 screens; phone: a short 16:9 block, no pin (nothing blank under it)
          ...(phone ? { start: 'top 80%', end: 'bottom 30%' } : { start: () => 'top ' + hdr() + 'px', end: '+=150%', pin: true, anticipatePin: 1 }),
          onUpdate: (self) => around.length && countAround(section, self.progress),
        },
      });
      // ONE scroll drives everything at the same time, all with linear easing: the video shrinks while every text travels in from its
      // own screen edge (top from the top, left from the left, right from the right, bottom from the bottom) and lands beside the
      // video exactly when the video reaches its small size. Because both move linearly toward their final spots the text is always
      // outside the video, so nothing overlaps. Scrolling back up plays it in reverse: the video grows and each text leaves through its edge.
      tl.fromTo(el, { scale: 1, borderRadius: 0 }, { scale: small, borderRadius: 48, duration: 1 }, 0);
      around.forEach((n) => {
        const k = n.dataset.around;
        const start = {
          opacity: 0,
          x: k === 'l' ? () => -(n.offsetLeft + n.offsetWidth) : k === 'r' ? () => section.clientWidth - n.offsetLeft : 0,
          y: k === 't' ? () => -(n.offsetTop + n.offsetHeight) : k === 'b' ? () => section.clientHeight - n.offsetTop : 0,
        };
        tl.fromTo(n, start, { opacity: 1, x: 0, y: 0, duration: 1 }, 0);
      });
      tl.to({}, { duration: .25 });   // hold the finished layout for a moment before the section scrolls on
    });
  }

  // count-up for the numbers around the video: runs when the layout is mostly in, resets when scrolled back up
  function countAround(section, progress) {
    $$('[data-around-count]', section).forEach((el) => {
      const end = parseFloat(el.dataset.countTo), suffix = el.dataset.suffix || '';
      const fmt = (v) => Math.round(v).toLocaleString(window.LOCALE || 'bn-BD') + suffix;
      if (progress > .78 && !el._counted) {
        el._counted = true;
        const o = { v: 0 };
        gsap.to(o, { v: end, duration: 1.6, ease: 'power2.out', onUpdate: () => { el.textContent = fmt(o.v); } });
      } else if (progress < .45 && el._counted) {
        el._counted = false;
        el.textContent = fmt(0);
      }
    });
  }

  function counters() {
    $$('[data-count]').forEach((el) => {
      const end = parseFloat(el.dataset.count), suffix = el.dataset.suffix || '';
      const o = { v: 0 };
      el.textContent = (0).toLocaleString(window.LOCALE || 'bn-BD') + suffix;
      ScrollTrigger.create({
        trigger: el, start: 'top 95%', once: true,
        onEnter: () => gsap.to(o, {
          v: end, duration: 2, ease: 'power2.out',
          onUpdate: () => { el.textContent = Math.round(o.v).toLocaleString(window.LOCALE || 'bn-BD') + suffix; },
        }),
      });
    });
  }

  function progressLine() {
    $$('[data-progress]').forEach((el) => gsap.fromTo(el, { scaleX: 0 }, {
      scaleX: 1, ease: 'none', transformOrigin: 'left center',
      scrollTrigger: { trigger: el.closest('section'), start: 'top 60%', end: 'bottom 80%', scrub: .6 },
    }));
  }

  function floats() {
    $$('[data-float]').forEach((el, i) => gsap.to(el, { y: -10, duration: 2.2 + i * .3, repeat: -1, yoyo: true, ease: 'sine.inOut' }));
  }

  function magnetic() {
    if (!window.matchMedia('(hover: hover)').matches) return;
    $$('[data-magnetic]').forEach((el) => {
      // keep colour/shadow transitions but never transition transform/opacity (GSAP owns those)
      el.style.transitionProperty = 'color, background-color, border-color, box-shadow';
      const xTo = gsap.quickTo(el, 'x', { duration: .5, ease: 'power3' });
      const yTo = gsap.quickTo(el, 'y', { duration: .5, ease: 'power3' });
      el.addEventListener('mousemove', (e) => {
        const r = el.getBoundingClientRect();
        // small pull, capped, so neighbouring buttons never run into each other
        const clamp = (v) => Math.max(-6, Math.min(6, v));
        xTo(clamp((e.clientX - (r.left + r.width / 2)) * .1));
        yTo(clamp((e.clientY - (r.top + r.height / 2)) * .1));
      });
      el.addEventListener('mouseleave', () => { xTo(0); yTo(0); });
    });
  }

  function shine() {
    $$('[data-shine]').forEach((el) => {
      el.classList.add('shine');
      el.addEventListener('mousemove', (e) => {
        const r = el.getBoundingClientRect();
        el.style.setProperty('--mx', (e.clientX - r.left) + 'px');
        el.style.setProperty('--my', (e.clientY - r.top) + 'px');
      });
    });
  }

  function headerBehaviour(lenis) {
    const header = $('#site-header');
    if (!header || !lenis) return;
    lenis.on('scroll', ({ scroll, direction }) => {
      const hide = direction === 1 && scroll > 300;
      if (header._hidden === hide) return;
      header._hidden = hide;
      gsap.to(header, { yPercent: hide ? -110 : 0, duration: .45, ease: 'power3.out', overwrite: true });
    });
  }

  let pluginsRegistered = false;

  // runs for the first page and again after every Turbo visit
  function boot() {
    instantEntry = visits++ > 0;
    curtain = document.getElementById('page-curtain');
    if (motionOff()) { fallbackBoot(); entered(); return; }
    if (!pluginsRegistered) { gsap.registerPlugin(ScrollTrigger); pluginsRegistered = true; }
    const lenis = smoothScroll();
    links();
    headerBehaviour(lenis);
    pageHeroes();
    splitHeadings(); reveals(); sectionExtras(); parallax(); scaleVideo(); counters(); progressLine(); floats(); magnetic(); shine();
    const heroTl = hero();
    intro(() => heroTl && heroTl.play());
    entered();
    // pinned sections change the page height after the first paint: measure again, then honour a #hash that came with the visit
    setTimeout(() => {
      ScrollTrigger.refresh();
      if (location.hash) {
        const t = $(location.hash);
        if (t) window.__lenis ? window.__lenis.scrollTo(t, { offset: -70, immediate: true }) : t.scrollIntoView();
      }
    }, 450);
  }

  // Deferred scripts run before DOMContentLoaded; wait a frame so Alpine has rendered its x-for lists.
  const go = () => requestAnimationFrame(boot);
  if (document.querySelector('script[src*="turbo"]')) {
    // Turbo fires turbo:load for the first page too, then after every visit; turbo:before-render = the old page is about to go
    document.addEventListener('turbo:before-render', teardown);
    document.addEventListener('turbo:load', go);
  } else {
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', go) : go();
  }
})();
