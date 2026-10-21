/*
 * Service marquee helper. The scrolling itself is a CSS animation (see style.css), so it runs even if
 * GSAP/Lenis fail to load. This file only: slows it on hover and speeds it up while the page is scrolling.
 */
(function () {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function start() {
    const tracks = Array.from(document.querySelectorAll('[data-marquee]'));
    if (!tracks.length) return;

    let hover = 1, boost = 0, current = 1, lastY = window.scrollY;
    tracks.forEach((t) => {
      t.parentElement.addEventListener('mouseenter', () => { hover = .2; });
      t.parentElement.addEventListener('mouseleave', () => { hover = 1; });
    });
    if (reduce) return;

    (function tick() {
      const y = window.scrollY;
      boost = boost * .92 + Math.abs(y - lastY) * .08;       // smoothed scroll speed (px/frame)
      lastY = y;
      const target = (1 + Math.min(boost / 5, 5)) * hover;   // 1x idle ... 6x while scrolling fast
      current += (target - current) * .08;
      if (!isFinite(current)) current = 1;                   // never let a bad number freeze it
      tracks.forEach((t) => t.getAnimations().forEach((a) => a.updatePlaybackRate(current)));
      requestAnimationFrame(tick);
    })();
  }

  // wait a frame so Alpine has rendered the x-for list
  const go = () => requestAnimationFrame(start);
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', go) : go();
})();
