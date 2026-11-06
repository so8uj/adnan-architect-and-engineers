/*
 * Shared header / footer / page curtain, injected on every page so the markup lives in one place.
 * (In Laravel this becomes a Blade layout + partials.)
 * buildLayout(body) fills a <body> with them. It runs for the first page (before Alpine starts) and, with Turbo Drive, for every
 * incoming <body> BEFORE it is put on screen (turbo:before-render) — so the new page never appears without its header / footer
 * (that late injection was the "white band at the top, then the page jumps" on page changes).
 */
(function () {
  function buildLayout(body) {
  const S = window.SITE;
  const nav = window.PAGE;                       // set by the Blade layout: home | portfolio | insights
  const onPortfolio = nav === 'portfolio';
  const onInsights = nav === 'insights';

  const H = S.header || {};
  const F = S.footer || {};
  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const isActive = (href) => (href === '/' && nav === 'home') || (href === '/portfolio' && onPortfolio) || (href === '/insights' && onInsights);

  // The menu is edited in Admin -> Header. These links are only the fallback if none are set.
  const fallbackMenu = [
    { label: 'হোম', href: '/' }, { label: 'সেবা', href: '/#services' }, { label: 'আমাদের কাজ', href: '/portfolio' },
    { label: 'কার্যক্রম', href: '/#activities' }, { label: 'ইনসাইট', href: '/insights' }, { label: 'কাজের ধাপ', href: '/#process' },
    { label: 'আমাদের সম্পর্কে', href: '/#about' }, { label: 'যোগাযোগ', href: '/#contact' },
  ];
  const links = (H.menu && H.menu.length ? H.menu : fallbackMenu).map((m) => ({ label: esc(m.label), href: esc(m.href), active: isActive(m.href) }));
  // "Our Work" opens a sub menu with the two main categories (Admin -> Header -> "Show the Our Work dropdown").
  const WORK = window.WORK || {};
  const workItems = (H.workDropdown !== false && WORK.completed) ? ['completed', 'running'].filter((k) => WORK[k]).map((k) => ({
    k, label: esc(WORK[k].label), href: esc(WORK[k].url),
    count: window.num ? window.num(WORK[k].count) : WORK[k].count,
  })) : [];
  // modern line icons: completed = check in a circle, running = construction hard hat
  const svgI = (d) => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;
  const ICON = {
    completed: svgI('<path d="M21.8 10A10 10 0 1 1 17 3.3"/><path d="m9 11 3 3L22 4"/>'),
    running: svgI('<path d="M2 18a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1z"/><path d="M10 10V5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5"/><path d="M4 15v-3a6 6 0 0 1 6-6"/><path d="M14 6a6 6 0 0 1 6 6v3"/>'),
  };
  const projWord = window.LANG === 'en' ? 'projects' : 'টি প্রজেক্ট';
  const isWork = (l) => workItems.length && l.href === '/portfolio';
  const chev = '<svg class="nav-dd-chev" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>';
  const ddPanel = `<div class="nav-dd-panel" role="menu">${workItems.map((w) =>
    `<a role="menuitem" class="nav-dd-item" href="${w.href}"><span class="nav-dd-ico ${w.k}">${ICON[w.k]}</span><span class="nav-dd-txt"><b>${w.label}</b><small>${w.count} ${projWord}</small></span><span class="nav-dd-go">→</span></a>`).join('')}</div>`;
  const navDesktop = links.map(l => isWork(l)
    ? `<div class="nav-dd"><a href="${l.href}" class="inline-flex items-center gap-1 ${l.active ? 'text-brand-600' : 'text-gray-600'} hover:text-brand-600 transition">${l.label}${chev}</a>${ddPanel}</div>`
    : `<a href="${l.href}" class="${l.active ? 'text-brand-600' : 'text-gray-600'} hover:text-brand-600 transition">${l.label}</a>`).join('');
  const navMobile = links.map(l =>
    `<a href="${l.href}" @click="menu=false" class="py-3 font-medium ${l.active ? 'text-brand-600' : 'text-gray-700'} border-b border-gray-50">${l.label}</a>` +
    (isWork(l) ? workItems.map((w) => `<a href="${w.href}" @click="menu=false" class="py-2.5 pl-6 text-sm text-gray-600 border-b border-gray-50 flex items-center gap-2"><span class="nav-dd-ico ${w.k} is-sm">${ICON[w.k]}</span>${w.label} <span class="text-gray-400">(${w.count})</span></a>`).join('') : '')).join('');
  const navFooter = links.map(l =>
    `<li><a href="${l.href}" class="hover:text-white transition">${l.label}</a></li>`).join('');

  const other = window.LANG === 'en' ? 'bn' : 'en';
  const btn = 'grid place-items-center h-10 min-w-10 rounded-full border border-gray-200 text-sm font-semibold hover:border-brand-600 hover:text-brand-600 transition';
  const ctaLabel = esc(H.ctaLabel || 'ফ্রি পরামর্শ নিন');
  const ctaUrl = esc(H.ctaUrl || '/#contact');
  const langBtn = `<button type="button" onclick="setLang('${other}')" class="${btn} px-2" aria-label="ভাষা" title="${other === 'en' ? 'English' : 'বাংলা'}">${other === 'en' ? 'EN' : 'বাং'}</button>`;
  const themeBtn = `<button type="button" onclick="toggleTheme()" class="${btn} w-10" aria-label="থিম পরিবর্তন" title="Light / Dark">
      <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
      <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
    </button>`;

  // The preloader belongs to the FIRST visit only (admin: Animation & preloader). On every other page load the curtain is not even
  // created, so pages open instantly. app.js stores the 'preloaderSeen' flag on the first page.
  let seenBefore = false;
  try { seenBefore = !!sessionStorage.getItem('preloaderSeen'); } catch (e) { /* ignore */ }
  const preCfg = Object.assign({ show: true, onlyFirstVisit: true }, window.SITE && window.SITE.preloader);
  const showCurtain = preCfg.show && !(preCfg.onlyFirstVisit && seenBefore);

  const head = `
  ${showCurtain ? `  <div id="page-curtain" class="preloader fixed inset-0 z-[100] grid place-items-center">
    <div data-curtain-logo class="preloader-home">
      <svg viewBox="0 0 300 260" aria-hidden="true">
        <path class="ph-roof-fill" d="M8 102L150 10l142 92z"/>
        <rect class="ph-body" x="30" y="102" width="240" height="148"/>
        <path class="ph-p ph-roof" pathLength="1" d="M8 102L150 10l142 92"/>
        <path class="ph-p ph-wall" pathLength="1" d="M30 100v150h240V100"/>
        <path class="ph-p ph-ground" pathLength="1" d="M0 250h300"/>
      </svg>
      <img class="ph-logo" src="${S.logo}" alt="Adnan Architects & Engineers">
    </div>
  </div>` : ''}

  ${H.topbar === false ? '' : `<div id="site-topbar" class="bg-ink-950 text-gray-300 text-sm">
    <div class="mx-auto max-w-7xl px-4 py-2 flex items-center justify-between gap-4">
      <span class="hidden sm:inline truncate">📍 ${esc(S.address)}</span>
      <div class="flex items-center gap-4 ml-auto">
        <a href="tel:${esc(S.phone)}" class="hover:text-white font-medium">📞 ${esc(S.phone)}</a>
        <a href="${esc(S.facebook)}" target="_blank" rel="noopener" class="hover:text-white">Facebook</a>
      </div>
    </div>
  </div>`}

  <header id="site-header" x-data="{menu:false, scrolled:false}" @scroll.window.passive="scrolled = window.scrollY > 10"
          class="sticky top-0 z-40 transition-shadow bg-white/95 border-b border-gray-100" :class="scrolled && 'shadow-md is-scrolled'">
    <div class="mx-auto max-w-7xl px-4 h-20 flex items-center justify-between">
      <a href="/" class="flex items-center" aria-label="Adnan Architects &amp; Engineers"><img src="${S.logo}" alt="Adnan Architects &amp; Engineers" class="h-16 w-auto"></a>
      <nav class="hidden lg:flex items-center gap-8 text-[15px] font-medium">${navDesktop}</nav>
      <div class="flex items-center gap-2">
        ${H.showLang === false ? '' : langBtn}${H.showTheme === false ? '' : themeBtn}
        ${H.ctaShow === false ? '' : `<a href="${ctaUrl}" data-magnetic class="hidden sm:inline-flex items-center rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 transition">${ctaLabel}</a>`}
        <button class="lg:hidden p-2 -mr-2" @click="menu=!menu" aria-label="মেনু">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path x-show="!menu" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
            <path x-show="menu" x-cloak stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
          </svg>
        </button>
      </div>
    </div>
    <div x-show="menu" x-cloak x-collapse class="lg:hidden border-t border-gray-100 bg-white">
      <div class="px-4 py-3 flex flex-col">${navMobile}
        ${H.ctaShow === false ? '' : `<a href="${ctaUrl}" @click="menu=false" class="mt-3 text-center rounded-full bg-brand-600 text-white font-semibold py-3">${ctaLabel}</a>`}
      </div>
    </div>
  </header>`;

  const ico = (d) => `<svg class="h-5 w-5 shrink-0 text-brand-500" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">${d}</svg>`;
  const social = 'grid place-items-center h-10 w-10 rounded-full bg-white/10 text-white hover:bg-brand-600 hover:-translate-y-0.5 transition duration-300';
  const serviceLinks = (window.SERVICES || []).map(sv =>
    `<li><a href="/#services" class="hover:text-white hover:pl-1 transition-all duration-300">${sv.title}</a></li>`).join('');

  // "Design & developed by" is always shown (it is not an option). Built-in values are used if the settings are empty.
  const cr = S.credit || {};
  const credit = {
    text: esc(cr.text || (window.LANG === 'en' ? 'Designed & Developed by' : 'ডিজাইন ও ডেভেলপ করেছেন')),
    name: esc(cr.name || (window.LANG === 'en' ? 'Sobuj' : 'সবুজ')),
    url: esc(cr.url || 'https://www.facebook.com/so8uj'),
  };

  const foot = `
  <footer class="relative bg-ink-950 text-gray-400 overflow-hidden">
    <div class="absolute -top-32 -right-24 h-72 w-72 rounded-full bg-brand-600/10"></div>

    <!-- call-to-action band -->
    ${F.ctaShow === false ? '' : `<div class="relative border-b border-white/10">
      <div class="mx-auto max-w-7xl px-4 py-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div data-reveal>
          <p class="text-2xl sm:text-3xl font-bold text-white leading-snug">${esc(F.ctaTitle || 'প্রজেক্ট শুরু করতে প্রস্তুত?')}</p>
          <p class="mt-1 text-sm">${esc(F.ctaText || 'আজই কল করুন বা মেসেজ দিন — আমরা আপনার পাশে আছি।')}</p>
        </div>
        <div data-reveal class="flex flex-wrap gap-3">
          <a href="tel:${S.phone}" data-magnetic class="rounded-full bg-brand-600 hover:bg-brand-500 text-white font-semibold px-6 py-3 transition shadow-lg shadow-brand-600/30">📞 ${S.phone}</a>
          <a href="https://wa.me/${S.whatsapp}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" data-magnetic class="rounded-full border border-white/25 hover:bg-white/10 text-white font-semibold px-6 py-3 transition">WhatsApp</a>
        </div>
      </div>
    </div>`}

    <div class="relative mx-auto max-w-7xl pl-4 pr-16 sm:px-4 py-14 grid sm:grid-cols-2 lg:grid-cols-12 gap-10">
      <div data-reveal class="lg:col-span-4">
        <span class="inline-block rounded-xl bg-white p-3"><img src="${S.logo}" alt="Adnan Architects &amp; Engineers" class="h-14 w-auto"></span>
        <p class="mt-4 text-sm leading-relaxed max-w-sm">${esc(F.about || 'সম্পূর্ণ আর্কিটেকচার ও ইঞ্জিনিয়ারিং সেবা — সয়েল টেস্ট থেকে প্ল্যান পাস পর্যন্ত।')}</p>
        ${F.showSocial === false ? '' : `<p class="mt-6 text-sm font-semibold text-white">আমাদের অনুসরণ করুন</p>
        <div class="mt-3 flex gap-3">
          <a href="${S.facebook}" target="_blank" rel="noopener" class="${social}" aria-label="Facebook" title="Facebook"><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 8V6.5c0-.7.3-1 1-1h2V2h-3c-2.8 0-4 1.8-4 4v2H7v3.5h3V22h4v-10.5h2.8L17.5 8H14z"/></svg></a>
          <a href="https://m.me/${S.messenger}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" class="${social}" aria-label="Messenger-এ মেসেজ করুন" title="Messenger"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.652V24l4.088-2.242c1.092.3 2.246.464 3.443.464 6.627 0 12-4.974 12-11.111C24 4.974 18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26L10.732 8l3.131 3.259L19.752 8l-6.561 6.963z"/></svg></a>
          <a href="https://wa.me/${S.whatsapp}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" class="${social}" aria-label="WhatsApp-এ মেসেজ করুন" title="WhatsApp"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>
        </div>`}
      </div>

      ${F.showLinks === false ? '' : `<div data-reveal class="lg:col-span-2">
        <p class="font-semibold text-white mb-4">দ্রুত লিংক</p>
        <ul class="space-y-2.5 text-sm">${navFooter}</ul>
      </div>`}

      ${F.showServices === false ? '' : `<div data-reveal class="lg:col-span-3">
        <p class="font-semibold text-white mb-4">সেবাসমূহ</p>
        <ul class="space-y-2.5 text-sm">${serviceLinks}</ul>
      </div>`}

      <div data-reveal class="lg:col-span-3">
        <p class="font-semibold text-white mb-4">যোগাযোগ</p>
        <ul class="space-y-4 text-sm">
          <li class="flex gap-3">${ico('<path d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z"/>')}<a href="tel:${S.phone}" class="hover:text-white transition">${S.phone}</a></li>
          <li class="flex gap-3">${ico('<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>')}<a href="mailto:${S.email}" class="hover:text-white break-all transition">${S.email}</a></li>
          <li class="flex gap-3">${ico('<path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>')}<span>${S.address}</span></li>
        </ul>
      </div>
    </div>

    <div class="relative border-t border-white/10">
      <div class="mx-auto max-w-7xl px-4 sm:pr-24 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-4 text-xs">
        <div class="flex flex-col sm:flex-row items-center gap-x-4 gap-y-1 text-center sm:text-left">
          <p>${F.copyright ? esc(F.copyright) : `© ${new Date().getFullYear()} Adnan Architects &amp; Engineers. <span>সর্বস্বত্ব সংরক্ষিত।</span>`}</p>
          <p class="sm:border-l sm:border-white/15 sm:pl-4">${credit.text} <a href="${credit.url}" target="_blank" rel="noopener" class="font-semibold text-white hover:text-brand-500 transition">${credit.name}</a></p>
        </div>
        <button type="button" onclick="window.__lenis ? window.__lenis.scrollTo(0, { duration: 1.1 }) : window.scrollTo({ top: 0, behavior: 'smooth' })"
          class="inline-flex items-center gap-2 rounded-full border border-white/15 px-4 py-2 text-white hover:bg-brand-600 hover:border-brand-600 transition">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
          <span>ওপরে যান</span>
        </button>
      </div>
    </div>
  </footer>

  <div class="fixed bottom-4 right-4 sm:bottom-5 sm:right-5 z-30 flex flex-col items-center gap-2.5 sm:gap-3">
    <a href="https://m.me/${S.messenger}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" data-magnetic title="Messenger" class="grid place-items-center h-12 w-12 sm:h-14 sm:w-14 rounded-full bg-[#0084FF] text-white shadow-xl shadow-[#0084FF]/40 hover:brightness-110 transition" aria-label="Messenger-এ মেসেজ করুন">
      <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.652V24l4.088-2.242c1.092.3 2.246.464 3.443.464 6.627 0 12-4.974 12-11.111C24 4.974 18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26L10.732 8l3.131 3.259L19.752 8l-6.561 6.963z"/></svg>
    </a>
    <a href="https://wa.me/${S.whatsapp}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" data-magnetic title="WhatsApp" class="grid place-items-center h-12 w-12 sm:h-14 sm:w-14 rounded-full bg-[#25D366] text-white shadow-xl shadow-[#25D366]/40 hover:brightness-110 transition" aria-label="WhatsApp-এ মেসেজ করুন">
      <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>
    <a href="tel:${S.phone}" data-magnetic title="কল করুন" class="grid place-items-center h-12 w-12 sm:h-14 sm:w-14 rounded-full bg-black text-white ring-1 ring-white/30 shadow-xl shadow-black/40 hover:bg-neutral-800 transition" aria-label="কল করুন">
      <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z"/></svg>
    </a>
  </div>`;

  window.__preT0 = performance.now();   // the preloader animation starts now (see app.js curtainReady)
  body.insertAdjacentHTML('afterbegin', head);
  body.insertAdjacentHTML('beforeend', foot);

  // English: translate the static text of the whole page (incl. what was just injected) before Alpine starts
  if (window.translateDom) window.translateDom(body);

  // a language switch reloads the page; skip the intro curtain that time
  try {
    if (sessionStorage.getItem('noCurtain')) {
      sessionStorage.removeItem('noCurtain');
      const c = body.querySelector('#page-curtain');
      if (c) c.style.display = 'none';
    }
  } catch (e) { /* ignore */ }
  }

  window.buildLayout = buildLayout;
  buildLayout(document.body);                                                         // first page
  document.addEventListener('turbo:before-render', (e) => buildLayout(e.detail.newBody));   // every page after it
})();
