/*
 * Site-wide motion: Lenis smooth scroll + GSAP / ScrollTrigger.
 *
 * Markup hooks (data attributes):
 *   data-reveal[=left|right|scale|clip]   fade/slide in when scrolled into view (batched + staggered)
 *   data-split              heading animates word by word
 *   data-parallax="0.15"    image drifts inside its overflow-hidden parent
 *   data-count="500"        number counts up (data-suffix="+")
 *   data-magnetic           element follows the cursor slightly
 *   data-progress           scaleX scrubbed by scroll (process line)
 *   data-float              gentle endless bobbing
 *   (marquee is pure CSS + assets/js/marquee.js)
 *   data-shine              cursor-following highlight on cards
 *   data-hero + [data-hero-*] / [data-line]   intro timeline on the home page
 */
(function () {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const curtain = document.getElementById('page-curtain');
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

  if (!window.gsap || !window.ScrollTrigger || reduce) {
    if (curtain) curtain.remove();
    return;
  }
  gsap.registerPlugin(ScrollTrigger);

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
      duration: 1.3,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      smoothWheel: true,
      wheelMultiplier: 1,
      touchMultiplier: 1.5,
    });
    window.__lenis = lenis;
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((t) => lenis.raf(t * 1000));   // one shared clock for Lenis + GSAP
    gsap.ticker.lagSmoothing(0);
    ScrollTrigger.config({ ignoreMobileResize: true });
    return lenis;
  }

  const norm = (p) => p.replace(/index\.html$/, '');

  function links(lenis) {
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a[href]');
      if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank' || a.hasAttribute('download')) return;
      if (a.protocol !== location.protocol || (location.protocol !== 'file:' && a.host !== location.host)) return;

      const samePage = norm(a.pathname) === norm(location.pathname);
      if (samePage && a.hash) {                       // in-page anchor -> smooth scroll
        const target = $(a.hash);
        if (!target) return;
        e.preventDefault();
        lenis ? lenis.scrollTo(target, { offset: -70, duration: 1.4 }) : target.scrollIntoView({ behavior: 'smooth' });
        history.pushState(null, '', a.hash);
        return;
      }
      if (samePage && !a.hash) return;
      // other page -> curtain wipe, then navigate
      if (!curtain) return;
      e.preventDefault();
      gsap.set(curtain, { display: 'grid', yPercent: 100 });
      gsap.set('[data-curtain-logo]', { opacity: 0 });
      gsap.to(curtain, { yPercent: 0, duration: .6, ease: 'power3.inOut', onComplete: () => { location.href = a.href; } });
    });
    // coming back via the browser's back button (bfcache)
    window.addEventListener('pageshow', (e) => { if (e.persisted && curtain) gsap.set(curtain, { display: 'none' }); });
  }

  function intro(done) {
    if (!curtain) return done();
    gsap.set(curtain, { visibility: 'visible', opacity: 1 });
    gsap.timeline({ onComplete: () => { curtain.style.display = 'none'; } })
      .fromTo('[data-curtain-logo]', { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: .45, ease: 'power2.out' })
      .to('[data-curtain-logo]', { opacity: 0, y: -14, duration: .3, ease: 'power2.in' }, '+=.15')
      .to(curtain, { yPercent: -100, duration: .85, ease: 'expo.inOut' }, '-=.05')
      .add(done, '-=.45');
  }

  function hero() {
    if (!$('[data-hero]')) return null;
    // built paused so the hidden start state is applied immediately; played when the curtain lifts
    const tl = gsap.timeline({ paused: true, defaults: { ease: 'power3.out' } });
    // slide content is animated by CSS (index.html heroSlider); GSAP only fades the whole slider and stats in
    tl.from('[data-hero-slider]', { opacity: 0, y: 30, duration: .9 })
      .from('[data-hero-stat]', { opacity: 0, y: 24, duration: .6, stagger: .1 }, '-=.4');

    // drifting background blobs
    $$('[data-blob]').forEach((el, i) => gsap.to(el, {
      x: i % 2 ? -50 : 60, y: i % 2 ? 40 : -50, duration: 7 + i * 2, repeat: -1, yoyo: true, ease: 'sine.inOut',
    }));
    return tl;
  }

  function pageHeroes() {
    const el = $('[data-page-hero]');
    if (!el) return;
    gsap.from($$('[data-page-hero] [data-ph]'), { opacity: 0, y: 36, duration: .9, stagger: .12, ease: 'power3.out', delay: 1 });
  }

  function reveals() {
    const items = $$('[data-reveal]');
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
      const words = h.textContent.trim().split(/\s+/);
      h.setAttribute('aria-label', h.textContent.trim());
      h.innerHTML = words.map((w) =>
        `<span aria-hidden="true" style="display:inline-block;overflow:hidden;vertical-align:top;padding:.2em 0;margin:-.2em 0"><span data-w style="display:inline-block">${w}</span></span>`
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
    $$('#services article svg').forEach((svg) => gsap.from(svg, {
      rotate: -90, scale: .3, opacity: 0, duration: .9, ease: 'back.out(1.8)',
      scrollTrigger: { trigger: svg, start: 'top 92%', once: true },
    }));
    // process numbers drop in
    $$('#process li > span').forEach((n) => gsap.from(n, {
      y: -30, opacity: 0, scale: 1.6, duration: .8, ease: 'power3.out',
      scrollTrigger: { trigger: n, start: 'top 92%', once: true },
    }));
    // ticks in the "why us" list
    $$('#about li > span').forEach((t) => gsap.from(t, {
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

  function counters() {
    $$('[data-count]').forEach((el) => {
      const end = parseFloat(el.dataset.count), suffix = el.dataset.suffix || '';
      const o = { v: 0 };
      el.textContent = (0).toLocaleString('bn-BD') + suffix;
      ScrollTrigger.create({
        trigger: el, start: 'top 95%', once: true,
        onEnter: () => gsap.to(o, {
          v: end, duration: 2, ease: 'power2.out',
          onUpdate: () => { el.textContent = Math.round(o.v).toLocaleString('bn-BD') + suffix; },
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
        xTo((e.clientX - (r.left + r.width / 2)) * .3);
        yTo((e.clientY - (r.top + r.height / 2)) * .3);
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

  function start() {
    const lenis = smoothScroll();
    links(lenis);
    headerBehaviour(lenis);
    pageHeroes();
    splitHeadings(); reveals(); sectionExtras(); parallax(); counters(); progressLine(); floats(); magnetic(); shine();
    const heroTl = hero();
    intro(() => heroTl && heroTl.play());
    window.addEventListener('load', () => ScrollTrigger.refresh());
  }

  // Deferred scripts run before DOMContentLoaded; wait a frame so Alpine has rendered its x-for lists.
  const go = () => requestAnimationFrame(start);
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', go) : go();
})();
