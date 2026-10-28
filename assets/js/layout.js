/*
 * Shared header / footer / page curtain, injected on every page so the markup lives in one place.
 * (In Laravel this becomes a Blade layout + partials.)
 * Runs synchronously at the end of <body>, i.e. before Alpine starts.
 */
(function () {
  const S = window.SITE;
  const page = location.pathname.split('/').pop() || 'index.html';
  const onPortfolio = page === 'portfolio.html' || page === 'project.html';
  const onInsights = page === 'insights.html' || page === 'insight.html';

  const links = [
    { label: 'হোম', href: 'index.html', active: page === 'index.html' || page === '' },
    { label: 'সেবা', href: 'index.html#services' },
    { label: 'আমাদের কাজ', href: 'portfolio.html', active: onPortfolio },
    { label: 'কার্যক্রম', href: 'index.html#activities' },
    { label: 'ইনসাইট', href: 'insights.html', active: onInsights },
    { label: 'কাজের ধাপ', href: 'index.html#process' },
    { label: 'আমাদের সম্পর্কে', href: 'index.html#about' },
    { label: 'যোগাযোগ', href: 'index.html#contact' },
  ];
  const navDesktop = links.map(l =>
    `<a href="${l.href}" class="${l.active ? 'text-brand-600' : 'text-gray-600'} hover:text-brand-600 transition">${l.label}</a>`).join('');
  const navMobile = links.map(l =>
    `<a href="${l.href}" @click="menu=false" class="py-3 font-medium ${l.active ? 'text-brand-600' : 'text-gray-700'} border-b border-gray-50">${l.label}</a>`).join('');
  const navFooter = links.map(l =>
    `<li><a href="${l.href}" class="hover:text-white transition">${l.label}</a></li>`).join('');

  const head = `
  <div id="page-curtain" class="fixed inset-0 z-[100] grid place-items-center bg-ink-950">
    <div data-curtain-logo class="rounded-2xl bg-white p-5 shadow-2xl">
      <img src="assets/img/logo.png" alt="Adnan Architects & Engineers" class="h-20 sm:h-24 w-auto">
    </div>
  </div>

  <div class="bg-ink-950 text-gray-300 text-sm">
    <div class="mx-auto max-w-7xl px-4 py-2 flex items-center justify-between gap-4">
      <span class="hidden sm:inline truncate">📍 ${S.address}</span>
      <div class="flex items-center gap-4 ml-auto">
        <a href="tel:${S.phone}" class="hover:text-white font-medium">📞 ${S.phone}</a>
        <a href="${S.facebook}" target="_blank" rel="noopener" class="hover:text-white">Facebook</a>
      </div>
    </div>
  </div>

  <header id="site-header" x-data="{menu:false, scrolled:false}" @scroll.window.passive="scrolled = window.scrollY > 10"
          class="sticky top-0 z-40 transition-shadow bg-white/95 border-b border-gray-100" :class="scrolled && 'shadow-md'">
    <div class="mx-auto max-w-7xl px-4 h-16 flex items-center justify-between">
      <a href="index.html" class="flex items-center" aria-label="Adnan Architects &amp; Engineers"><img src="assets/img/logo.png" alt="Adnan Architects &amp; Engineers" class="h-12 w-auto"></a>
      <nav class="hidden lg:flex items-center gap-8 text-[15px] font-medium">${navDesktop}</nav>
      <div class="flex items-center gap-2">
        <a href="index.html#contact" data-magnetic class="hidden sm:inline-flex items-center rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 transition">ফ্রি পরামর্শ নিন</a>
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
        <a href="index.html#contact" @click="menu=false" class="mt-3 text-center rounded-full bg-brand-600 text-white font-semibold py-3">ফ্রি পরামর্শ নিন</a>
      </div>
    </div>
  </header>`;

  const foot = `
  <footer class="bg-ink-950 text-gray-400">
    <div class="mx-auto max-w-7xl px-4 py-14 grid md:grid-cols-4 gap-10">
      <div data-reveal class="md:col-span-2">
        <span class="inline-block rounded-xl bg-white p-3"><img src="assets/img/logo.png" alt="Adnan Architects &amp; Engineers" class="h-14 w-auto"></span>
        <p class="mt-4 text-sm leading-relaxed max-w-sm">সম্পূর্ণ আর্কিটেকচার ও ইঞ্জিনিয়ারিং সেবা — সয়েল টেস্ট থেকে প্ল্যান পাস পর্যন্ত।</p>
      </div>
      <div data-reveal>
        <p class="font-semibold text-white mb-3">লিংক</p>
        <ul class="space-y-2 text-sm">${navFooter}</ul>
      </div>
      <div data-reveal>
        <p class="font-semibold text-white mb-3">যোগাযোগ</p>
        <ul class="space-y-2 text-sm">
          <li>📞 <a href="tel:${S.phone}" class="hover:text-white">${S.phone}</a></li>
          <li>✉️ <a href="mailto:${S.email}" class="hover:text-white break-all">${S.email}</a></li>
          <li>📍 ${S.address}</li>
          <li><a href="${S.facebook}" target="_blank" rel="noopener" class="hover:text-white">Facebook পেজ ↗</a></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-white/10 py-5 text-center text-xs">© ${new Date().getFullYear()} Adnan Architects &amp; Engineers. সর্বস্বত্ব সংরক্ষিত।</div>
  </footer>

  <div class="fixed bottom-5 right-5 z-30 flex flex-col items-center gap-3">
    <a href="https://m.me/${S.messenger}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" data-magnetic title="Messenger" class="grid place-items-center h-14 w-14 rounded-full bg-[#0084FF] text-white shadow-xl shadow-[#0084FF]/40 hover:brightness-110 transition" aria-label="Messenger-এ মেসেজ করুন">
      <svg class="h-7 w-7" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.5 2 2 6.2 2 11.5c0 2.9 1.3 5.4 3.4 7.1V22l3.1-1.7c.8.2 1.6.3 2.5.3 5.5 0 10-4.2 10-9.5S17.5 2 12 2z"/><path fill="#0084FF" d="M5.8 14.2l3.5-3.7 2.6 2 3.9-2.3-3.6 3.8-2.6-2-3.8 2.2z"/></svg>
    </a>
    <a href="https://wa.me/${S.whatsapp}?text=${encodeURIComponent(S.greeting)}" target="_blank" rel="noopener" data-magnetic title="WhatsApp" class="grid place-items-center h-14 w-14 rounded-full bg-[#25D366] text-white shadow-xl shadow-[#25D366]/40 hover:brightness-110 transition" aria-label="WhatsApp-এ মেসেজ করুন">
      <svg class="h-7 w-7" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2z"/><path fill="#25D366" transform="translate(6.2 6.2) scale(.5)" d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z"/></svg>
    </a>
    <a href="tel:${S.phone}" data-magnetic title="কল করুন" class="grid place-items-center h-14 w-14 rounded-full bg-brand-600 text-white shadow-xl shadow-brand-600/40 hover:bg-brand-700 transition" aria-label="কল করুন">
      <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z"/></svg>
    </a>
  </div>`;

  document.body.insertAdjacentHTML('afterbegin', head);
  document.body.insertAdjacentHTML('beforeend', foot);
})();
