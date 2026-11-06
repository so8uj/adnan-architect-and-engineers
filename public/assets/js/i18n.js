/*
 * Language (bn / en) + theme (light / dark). Loaded FIRST on every page (before data.js) so the right language
 * and theme are known before anything renders.
 *
 *  - Data (projects, insights, services ...) lives in data.js in Bangla, with an English overlay at its end.
 *  - Static page text is translated here: every Bangla string in the HTML is looked up in EN below (exact text match).
 *    A string with no entry simply stays in Bangla, so a missing translation never breaks a page.
 *  - Switching language reloads the page (simple and flicker-free); the choice is remembered in localStorage.
 *  Laravel: replace with lang/en.json + __('...') and a locale middleware.
 */
(function () {
  const store = {
    get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* private mode */ } },
  };

  // The server decides the language (cookie "lang") and writes it into <html lang>; the page just follows it.
  window.LANG = document.documentElement.lang === 'en' ? 'en' : 'bn';
  window.LOCALE = window.LANG === 'en' ? 'en-US' : 'bn-BD';

  // Theme: the visitor's own choice wins, otherwise the default set in Admin -> Theme.
  const savedTheme = store.get('theme');
  if ((savedTheme || window.DEFAULT_THEME) === 'dark') document.documentElement.classList.add('dark');

  // Language switch: the glass logo overlay fades in, the page reloads, and the overlay fades away on the new page
  // (hides the Bangla -> English flash). `lang-in` is set before first paint and cleared by a CSS animation, so it can never get stuck.
  try {
    if (sessionStorage.getItem('noCurtain')) document.documentElement.classList.add('lang-in');
  } catch (e) { /* ignore */ }

  window.setLang = function (l) {
    if (l === window.LANG) return;
    document.cookie = 'lang=' + l + '; path=/; max-age=31536000; SameSite=Lax';
    try { sessionStorage.setItem('noCurtain', '1'); } catch (e) { /* ignore */ }   // no intro curtain on a language switch
    document.documentElement.classList.add('lang-out');
    setTimeout(() => location.reload(), 300);
  };
  window.toggleTheme = function () {
    const root = document.documentElement;
    root.classList.add('theme-fade');                       // colours glide instead of snapping
    const dark = root.classList.toggle('dark');
    store.set('theme', dark ? 'dark' : 'light');
    setTimeout(() => root.classList.remove('theme-fade'), 450);
  };

  const EN = {
    // ---- titles / meta
    'Adnan Architects & Engineers | আর্কিটেকচার ও ইঞ্জিনিয়ারিং সেবা': 'Adnan Architects & Engineers | Architecture & Engineering Services',
    'আর্কিটেকচারাল, স্ট্রাকচারাল ও ইলেকট্রিক্যাল ড্রয়িং, প্লাম্বিং ও ফায়ারফাইটিং, সয়েল টেস্ট, 3D ও ল্যান্ডস্কেপ ডিজাইন, ইন্টেরিয়র ডিজাইন এবং রাজউক, পৌরসভা ও উপজেলা প্ল্যান।': 'Architectural, structural and electrical drawings, plumbing & firefighting, soil test, 3D & landscape design, interior design, and RAJUK, Pourashava & Upazila plan approval.',
    'আমাদের কাজ | Adnan Architects & Engineers': 'Our Work | Adnan Architects & Engineers',
    'Adnan Architects & Engineers-এর সম্পন্ন প্রজেক্ট, ফ্লোর প্ল্যান ও ৩ডি ডিজাইনের নমুনা।': 'Completed projects, floor plans and 3D design samples by Adnan Architects & Engineers.',
    'প্রজেক্ট | Adnan Architects & Engineers': 'Project | Adnan Architects & Engineers',
    'ইনসাইট | Adnan Architects & Engineers': 'Insights | Adnan Architects & Engineers',
    'প্ল্যান পাস, ডিজাইন ও নির্মাণ নিয়ে দরকারি লেখা ও পরামর্শ।': 'Useful articles and advice on plan approval, design and construction.',

    // ---- navigation / layout
    'হোম': 'Home', 'সেবা': 'Services', 'আমাদের কাজ': 'Our Work', 'কার্যক্রম': 'Activities', 'ইনসাইট': 'Insights',
    'কাজের ধাপ': 'Process', 'আমাদের সম্পর্কে': 'About Us', 'যোগাযোগ': 'Contact',
    'ফ্রি পরামর্শ নিন': 'Get Free Advice', 'মেনু': 'Menu', 'লিংক': 'Links',
    'সম্পূর্ণ আর্কিটেকচার ও ইঞ্জিনিয়ারিং সেবা — সয়েল টেস্ট থেকে প্ল্যান পাস পর্যন্ত।': 'Complete architecture & engineering services — from soil test to plan approval.',
    'Facebook পেজ ↗': 'Facebook Page ↗', 'Facebook পেজ': 'Facebook Page',
    'সর্বস্বত্ব সংরক্ষিত।': 'All rights reserved.',
    'Messenger-এ মেসেজ করুন': 'Message us on Messenger', 'WhatsApp-এ মেসেজ করুন': 'Message us on WhatsApp',
    'কল করুন': 'Call us',
    'প্রজেক্ট শুরু করতে প্রস্তুত?': 'Ready to start your project?',
    'আজই কল করুন বা মেসেজ দিন — আমরা আপনার পাশে আছি।': 'Call or message us today — we are by your side.',
    'আমাদের অনুসরণ করুন': 'Follow us', 'দ্রুত লিংক': 'Quick links', 'সেবাসমূহ': 'Our services', 'ওপরে যান': 'Back to top', 'ভাষা': 'Language', 'থিম পরিবর্তন': 'Toggle theme',

    // ---- home sections
    'আমাদের সেবাসমূহ': 'Our Services',
    'ভবন নির্মাণের প্রতিটি ধাপে আপনার পাশে': 'By your side at every step of construction',
    'অভিজ্ঞ আর্কিটেক্ট ও ইঞ্জিনিয়ার টিমের মাধ্যমে প্রয়োজনীয় সব ড্রয়িং, ডিজাইন ও অনুমোদন সেবা।': 'All the drawings, designs and approval services you need, from an experienced team of architects and engineers.',
    'সম্পন্ন প্রজেক্টের নমুনা': 'Samples of completed projects',
    'যেকোনো প্রজেক্টে ক্লিক করে সব ড্রয়িং ও বিস্তারিত বিবরণ দেখুন।': 'Click any project to see all drawings and full details.',
    'সব প্রজেক্ট দেখুন': 'View all projects', 'বিস্তারিত দেখুন': 'View details',
    'গ্যালারি': 'Gallery', 'আমাদের কার্যক্রম': 'Our Activities',
    'সাইট পরিদর্শন ও কাজের তদারকির নিয়মিত কিছু মুহূর্ত। ছবিতে ক্লিক করে বড় করে দেখুন।': 'Regular moments from site visits and work supervision. Click a photo to enlarge it.',
    'কীভাবে আমরা কাজ করি': 'How we work',
    'কেন Adnan Architects & Engineers?': 'Why Adnan Architects & Engineers?',
    'আমরা শুধু নকশা করি না — আপনার জমির সর্বোচ্চ ব্যবহার, নিরাপদ স্ট্রাকচার এবং দ্রুত অনুমোদন নিশ্চিত করি।': 'We do more than design — we ensure the best use of your land, a safe structure and fast approval.',
    'ক্লায়েন্টদের কথা': 'Client Voices', 'যাঁরা আমাদের ওপর ভরসা করেছেন': 'People who have trusted us',
    'আমাদের ক্লায়েন্ট': 'Our Clients', 'যাঁরা আমাদের ওপর আস্থা রেখেছেন': 'Trusted by',
    'নমুনা': 'Sample', 'ভিডিও শীঘ্রই আসছে': 'Video coming soon',
    'জানুন, বুঝুন, সিদ্ধান্ত নিন': 'Learn, understand, decide',
    'প্ল্যান পাস, ডিজাইন ও নির্মাণ নিয়ে দরকারি কিছু লেখা।': 'Some useful reads on plan approval, design and construction.',
    'সব ইনসাইট দেখুন': 'View all insights', 'পড়ুন': 'Read', 'পড়া': 'read',
    'সাধারণ জিজ্ঞাসা': 'Frequently Asked Questions',
    'আপনার প্রজেক্ট নিয়ে কথা বলুন': 'Talk to us about your project',
    'ফর্মটি পূরণ করুন অথবা সরাসরি কল করুন। আমরা দ্রুত যোগাযোগ করব।': 'Fill in the form or call us directly. We will get back to you quickly.',
    'ইমেইল': 'Email', 'অফিস': 'Office',
    'ম্যাপে ক্লিক করে ঘুরে দেখুন': 'Click to explore the map',
    'চলমান': 'Running', 'সম্পন্ন': 'Completed', 'এই বিভাগে এখনো কোনো প্রজেক্ট নেই — শীঘ্রই আসছে।': 'No projects in this category yet — coming soon.',
    'আপনার নাম': 'Your name', 'মোবাইল নম্বর': 'Mobile number', 'কোন সেবা প্রয়োজন?': 'Which service do you need?',
    'নির্বাচন করুন': 'Select one', 'বার্তা': 'Message', 'বার্তা পাঠান': 'Send message',
    '✔ ধন্যবাদ! আমরা শীঘ্রই যোগাযোগ করব।': '✔ Thank you! We will contact you soon.',
    'পাঠানো যায়নি, অনুগ্রহ করে আবার চেষ্টা করুন।': 'Could not send. Please try again.',
    'জমির আকার, অবস্থান ও প্রয়োজন সংক্ষেপে লিখুন': 'Briefly describe the plot size, location and your needs',

    // ---- controls / a11y
    'আগের স্লাইড': 'Previous slide', 'পরের স্লাইড': 'Next slide', 'স্লাইড': 'Slide', 'মন্তব্য': 'Testimonial',
    'ভিডিও চালু করুন': 'Play video', 'ভিডিও': 'Video', 'বন্ধ করুন': 'Close', 'রেটিং': 'Rating',
    'আগের': 'Previous', 'পরের': 'Next', 'আগের ছবি': 'Previous photo', 'পরের ছবি': 'Next photo',
    'Adnan Architects & Engineers সেবাসমূহ': 'Adnan Architects & Engineers services',

    // ---- portfolio / project / insights pages
    'আমাদের': 'Our', 'কাজ': 'Work',
    'ফ্লোর প্ল্যান, ড্রয়িং ও ৩ডি ডিজাইনের নমুনা। যেকোনো প্রজেক্টে ক্লিক করে সব ছবি ও বিস্তারিত দেখুন।': 'Samples of floor plans, drawings and 3D designs. Click any project to see all photos and details.',
    'আরও কাজ দেখতে আমাদের': 'To see more of our work, visit our', 'ভিজিট করুন।': '.',
    'আপনার প্লটের জন্য নকশা করাতে চান?': 'Want a design for your plot?',
    'জমির মাপ ও প্রয়োজন জানান, আমরা দ্রুত পরামর্শ দেব।': 'Tell us the plot size and your needs, and we will advise you quickly.',
    'যোগাযোগ করুন': 'Contact us',
    'প্রজেক্টটি খুঁজে পাওয়া যায়নি': 'Project not found', 'প্রজেক্ট বিবরণ': 'Project description', 'বিশেষ দিক': 'Highlights',
    'প্রজেক্ট তথ্য': 'Project info', 'এমন নকশা করাতে চাই': 'I want a design like this', 'আরও প্রজেক্ট': 'More projects',
    'লেখাটি খুঁজে পাওয়া যায়নি': 'Article not found',
    'আপনার প্রজেক্ট নিয়ে কথা বলতে চান?': 'Want to talk about your project?',
    'ফোন করুন অথবা ফর্ম পূরণ করুন, আমরা দ্রুত যোগাযোগ করব।': 'Call us or fill in the form and we will get back to you quickly.',
    'আরও পড়ুন': 'Read more', 'সব ইনসাইট →': 'All insights →',
    'সব': 'All',
    'টি ড্রয়িং': ' drawings',
  };

  window.t = (s) => (window.LANG === 'en' && EN[s] !== undefined ? EN[s] : s);
  window.num = (x) => Number(x).toLocaleString(window.LOCALE);

  const norm = (s) => s.replace(/\s+/g, ' ').trim();
  const BN = /[ঀ-৿]/;

  // Translate static HTML (text nodes + a few attributes), including the content of <template> blocks.
  window.translateDom = function (root) {
    if (window.LANG !== 'en') return;
    root = root || document.body;
    const attrs = ['aria-label', 'placeholder', 'alt', 'title'];
    (function walk(node) {
      for (const n of Array.from(node.childNodes)) {
        if (n.nodeType === 3) {
          const key = norm(n.nodeValue);
          if (key && BN.test(key) && EN[key] !== undefined) {
            const tr = EN[key];
            const lead = /^[.,!?;:]/.test(tr) ? '' : (/^\s/.test(n.nodeValue) ? ' ' : '');
            n.nodeValue = lead + tr + (/\s$/.test(n.nodeValue) ? ' ' : '');
          }
        } else if (n.nodeType === 1) {
          if (n.tagName === 'SCRIPT' || n.tagName === 'STYLE') continue;
          for (const a of attrs) {
            const v = n.getAttribute(a);
            if (v && BN.test(v) && EN[norm(v)] !== undefined) n.setAttribute(a, EN[norm(v)]);
          }
          if (n.tagName === 'TEMPLATE') walk(n.content);
          walk(n);
        }
      }
    })(root);

    if (BN.test(document.title) && EN[norm(document.title)] !== undefined) document.title = EN[norm(document.title)];
    const md = document.querySelector('meta[name="description"]');
    if (md && EN[norm(md.content)] !== undefined) md.content = EN[norm(md.content)];
  };
})();
