/*
 * Shared header / footer / page curtain, injected on every page so the markup lives in one place.
 * (In Laravel this becomes a Blade layout + partials.)
 * Runs synchronously at the end of <body>, i.e. before Alpine starts.
 */
(function () {
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
  const navDesktop = links.map(l =>
    `<a href="${l.href}" class="${l.active ? 'text-brand-600' : 'text-gray-600'} hover:text-brand-600 transition">${l.label}</a>`).join('');
  const navMobile = links.map(l =>
    `<a href="${l.href}" @click="menu=false" class="py-3 font-medium ${l.active ? 'text-brand-600' : 'text-gray-700'} border-b border-gray-50">${l.label}</a>`).join('');
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

  const head = `
  <div id="page-curtain" class="preloader fixed inset-0 z-[100] grid place-items-center">
    <div data-curtain-logo class="flex flex-col items-center gap-6">
      <div class="rounded-2xl bg-white p-5 shadow-2xl">
        <img src="${S.logo}" alt="Adnan Architects & Engineers" class="h-20 sm:h-24 w-auto">
      </div>
      <div class="preloader-bar"></div>
    </div>
  </div>

  ${H.topbar === false ? '' : `<div class="bg-ink-950 text-gray-300 text-sm">
    <div class="mx-auto max-w-7xl px-4 py-2 flex items-center justify-between gap-4">
      <span class="hidden sm:inline truncate">📍 ${esc(S.address)}</span>
      <div class="flex items-center gap-4 ml-auto">
        <a href="tel:${esc(S.phone)}" class="hover:text-white font-medium">📞 ${esc(S.phone)}</a>
        <a href="${esc(S.facebook)}" target="_blank" rel="noopener" class="hover:text-white">Facebook</a>
      </div>
    </div>
  </div>`}

  <header id="site-header" x-data="{menu:false, scrolled:false}" @scroll.window.passive="scrolled = window.scrollY > 10"
          class="sticky top-0 z-40 transition-shadow bg-white/95 border-b border-gray-100" :class="scrolled && 'shadow-md'">
    <div class="mx-auto max-w-7xl px-4 h-16 flex items-center justify-between">
      <a href="/" class="flex items-center" aria-label="Adnan Architects &amp; Engineers"><img src="${S.logo}" alt="Adnan Architects &amp; Engineers" class="h-12 w-auto"></a>
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
    text: esc(cr.text || (window.LANG === 'en' ? 'Design & Developed by' : 'ডিজাইন ও ডেভেলপ করেছেন')),
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
          <a href="https://m.me/${S.messenger}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" class="${social}" aria-label="Messenger-এ মেসেজ করুন" title="Messenger"><svg class="h-5 w-5" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.5 2 2 6.2 2 11.5c0 2.9 1.3 5.4 3.4 7.1V22l3.1-1.7c.8.2 1.6.3 2.5.3 5.5 0 10-4.2 10-9.5S17.5 2 12 2z"/><path fill="#121214" d="M5.8 14.2l3.5-3.7 2.6 2 3.9-2.3-3.6 3.8-2.6-2-3.8 2.2z"/></svg></a>
          <a href="https://wa.me/${S.whatsapp}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" class="${social}" aria-label="WhatsApp-এ মেসেজ করুন" title="WhatsApp"><svg class="h-5 w-5" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2z"/><path fill="#121214" transform="translate(6.2 6.2) scale(.5)" d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z"/></svg></a>
        </div>`}
      </div>

      ${F.showLinks === false ? '' : `<div data-reveal class="lg:col-span-2">
        <p class="font-semibold text-white mb-4">দ্রুত লিংক</p>
        <ul class="space-y-2.5 text-sm">${navFooter}</ul>
      </div>`}

      ${F.showServices === false ? '' : `<div data-reveal class="lg:col-span-3">
        <p class="font-semibold text-white mb-4">সেবা সমূহ</p>
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
      <svg class="h-7 w-7" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.5 2 2 6.2 2 11.5c0 2.9 1.3 5.4 3.4 7.1V22l3.1-1.7c.8.2 1.6.3 2.5.3 5.5 0 10-4.2 10-9.5S17.5 2 12 2z"/><path fill="#0084FF" d="M5.8 14.2l3.5-3.7 2.6 2 3.9-2.3-3.6 3.8-2.6-2-3.8 2.2z"/></svg>
    </a>
    <a href="https://wa.me/${S.whatsapp}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" data-magnetic title="WhatsApp" class="grid place-items-center h-12 w-12 sm:h-14 sm:w-14 rounded-full bg-[#25D366] text-white shadow-xl shadow-[#25D366]/40 hover:brightness-110 transition" aria-label="WhatsApp-এ মেসেজ করুন">
      <svg class="h-7 w-7" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2z"/><path fill="#25D366" transform="translate(6.2 6.2) scale(.5)" d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z"/></svg>
    </a>
    <a href="tel:${S.phone}" data-magnetic title="কল করুন" class="grid place-items-center h-12 w-12 sm:h-14 sm:w-14 rounded-full bg-brand-600 text-white shadow-xl shadow-brand-600/40 hover:bg-brand-700 transition" aria-label="কল করুন">
      <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z"/></svg>
    </a>
  </div>`;

  document.body.insertAdjacentHTML('afterbegin', head);
  document.body.insertAdjacentHTML('beforeend', foot);

  // English: translate the static text of the whole page (incl. what was just injected) before Alpine starts
  if (window.translateDom) window.translateDom(document.body);

  // a language switch reloads the page; skip the intro curtain that time
  try {
    if (sessionStorage.getItem('noCurtain')) {
      sessionStorage.removeItem('noCurtain');
      const c = document.getElementById('page-curtain');
      if (c) c.style.display = 'none';
    }
  } catch (e) { /* ignore */ }
})();
