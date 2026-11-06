# Adnan Architects & Engineers — Design System

The single reference for anyone (human or AI agent) who adds or edits UI on this site.
**Read this before touching a view, a stylesheet or `config/admin.php`.** Keep it current: when you change a token, a
component or a convention, update this file in the same change.

Last reviewed: 2026-11-03.

---

## 1. Principles

1. **Clean, professional, modern, eye-catching.** Generous white space, one strong accent (red), no decoration that
   does not earn its place. Motion is smooth and purposeful, never busy.
2. **The logo is the palette.** Crimson red + black + white + silver grey. Nothing else is a brand colour.
3. **Everything editable lives in the admin.** Colours (Admin → Theme), content (Admin → every list), slider options
   (Admin → Hero sliders). Do not hard-code copy or colours that an owner would want to change.
4. **Bilingual (Bangla default, English).** Every visible string exists in both languages.
5. **Light and dark are both first-class.** Check both whenever you touch colour.
6. **Offline-capable.** No CDN fonts/scripts. Everything is served from `public/assets/`.

---

## 2. Colour

### 2.1 Brand palette (from `public/assets/img/logo.png`)

| Role | Value | Notes |
|---|---|---|
| Primary red | `#e41431` | `--brand-600`. Buttons, links, accents, active states. |
| Black | `#0e0e10` / `#111113` | Text in light mode, the "tower" in the hero, dark surfaces. |
| White | `#ffffff` | Cards, the logo plate (the logo needs white behind it). |
| Silver grey | `#c8c8cc` | Rules, quiet UI. Body copy greys: `#5b5d63` (light), `#a3a5ad` (dark). |

### 2.2 Theme tokens (CSS variables, set from Admin → Theme)

`App\Support\Theme` writes one small `<style>` block with RGB-triplet variables, so **Tailwind classes follow the theme
without a rebuild**:

```
--brand-50 --brand-100 --brand-400 --brand-500 --brand-600 --brand-700 --brand-900   (derived from the primary)
--bg  --surface  --text                                                              (per mode)
```

Use them as `rgb(var(--brand-600))`, or with alpha: `rgb(var(--brand-600) / .18)`.
Tailwind utilities exist for the brand shades (`bg-brand-600`, `text-brand-700`, `ring-brand-500/30` …) and
`ink-800/900/950` (neutral blacks).

| Token | Light | Dark |
|---|---|---|
| Primary (`--brand-600`) | `#e41431` | `#e41431` |
| Page (`--bg`) | `#f7f7f8` | `#0c0c0e` |
| Cards / header (`--surface`) | `#ffffff` | `#161619` |
| Text (`--text`) | `#111113` | `#f2f2f3` |

Defaults for new installs live in `database/seeders/SiteSeeder.php` (`theme_light_*`, `theme_dark_*`); the live values
are rows in the `settings` table. **Changing a colour = change the setting + the seeder default + this table.**

### 2.3 Rules

- Red is an **accent**, not a background wash. Large surfaces are white / off-white (light) or near-black (dark).
- Never hard-code a brand hex in CSS or a view. Use a token. Neutral hexes (`#0e0e10`, `#5b5d63` …) are allowed
  where this document lists them.
- In dark mode brand-coloured **text** uses the lighter shade (`.dark .text-brand-700 → --brand-400`).
- Red-on-red is invisible: a `bg-brand-600` element must never sit on a red surface.
- Keep text contrast ≥ 4.5:1 (body) / 3:1 (large text). White on `#e41431` passes.

---

## 3. Typography

| Use | Font | Class |
|---|---|---|
| Body, Bangla + Latin | **Hind Siliguri** | `font-sans` (default) |
| Latin display / numbers | **Poppins** | `font-display` |
| Small spaced labels | monospace (`"DM Mono", ui-monospace, Consolas`) | inline in `.hv2-eyebrow`, `.hv2-topline` |

Fonts are self-hosted: `public/assets/fonts/fonts.css` (Hind Siliguri + Poppins only; DM Mono is **not** bundled and
falls back to the system monospace — add the woff2 there if you need it exact).

Scale (hero): H1 `clamp(2.2rem, 4.6vw, 4rem)`, weight 800, line-height **1.2+** (Bangla needs the height — never go
below 1.15). Body 1.1rem / 1.75. Labels .65–.78rem, uppercase, letter-spacing .14em.

---

## 4. Layout, spacing, shape

- Page container: `mx-auto max-w-7xl px-4` (sections), hero container `.hv2-wrap` (max 80rem, `padding 0 4rem` ≥ 640px).
- Section rhythm: `py-20 lg:py-28`. Alternate `bg-white` / `bg-gray-50`; dark feature bands use `bg-ink-950 text-white`.
- Radius: buttons `.5rem` (hero) / `rounded-full` (site CTAs), cards `rounded-2xl`, hero picture frame `6px` (architectural, sharp).
- Shadows are soft and low-contrast: `0 14px 28px rgb(var(--brand-600)/.28)` for primary buttons,
  `0 34px 64px rgba(14,14,16,.24)` for the hero picture.
- Grids: Tailwind `grid` + `gap-6/8`; hero slide is a 2-column grid (`1fr 1fr`, gap 4.5rem) collapsing at <1024px.

---

## 5. Components

### 5.1 Buttons
| Variant | Where | Look |
|---|---|---|
| Primary | `.hv2-btn.hv2-btn-primary`, site CTAs `bg-brand-600 text-white rounded-full` | red, white text, soft red shadow, arrow nudges 4px on hover |
| Secondary / ghost | `.hv2-btn.hv2-btn-ghost` | 1.5px outline in `--text/.22`, turns red on hover |
Rules: hover may lift 2px (`translateY(-2px)`) and change colour — **never** scale or pull toward the cursor enough to
touch a neighbour. `data-magnetic` is capped at ±6px; do not put it on buttons that sit side by side.

### 5.2 Hero (home) — two styles, chosen in **Admin → Hero sliders → Slider settings**
Setting `hero_style` = `v1` | `v2` (default `v2`). Both read the same `hero_slides` rows.

**v2 — "logo" hero** (`resources/views/home.blade.php`, section `#home.hero-v2`, CSS prefix `hv2-`, Alpine `heroSlider()`):
- Left: eyebrow label → H1 (second line red + short red bar) → paragraph → primary + ghost button.
- Right: picture in a white frame (`.hv2-visual`, tilts with the mouse) over a **red block with white slashes**
  (`.hv2-panel`), a **black tower with a white window** (`.hv2-tower`) and a **red triangular roof** (`.hv2-roof`,
  95% of the frame width) — both sit *behind* the frame (z-index 1) so only their tops peek out. A white chip
  (`.hv2-chip`) shows the slide's icon + title. Below: progress bars (`.hv2-bar`). There is **no statistics block in the hero any more** — the numbers live in the "Texts around the intro video" (see below).
- Background: two faint slanted bands (`#home.hero-v2::before/::after`) + the engineering decoration (`.hero-eng`:
  grey gears, compass, cubes, dimension line, skyline, "+" marks). Decoration is grey and faint on purpose.
- Image box is **4:3** (upload 1200×900). Per-slide fields: heading ×2, paragraph, button, badge, image fit/position,
  floating chip.

**v1 — full-page "Sobuj" slider** (library `public/assets/lib/sobuj_slider/`, view `sobujHero()`):
- Full-screen background image revealed in N vertical strips, flat black overlay at the % set in admin, centred
  content, transparent menu over it until scrolled, top info bar hidden (`html.hero-v1`).
- Image: wide 16:9, **1920×1080** (min 1600×900). Admin options: columns 2–8, overlay 0–90 %, arrows, dots.

Shared admin options: autoplay, slide time (ms), pause on hover. The slide form (`config/admin.php` → `hero-slides`)
shows only the fields of the active style (`'styles' => ['v2']`, `'labels'`, `'helps'`).

**Bottom animations (v2, fixed — do not replace them):** two drawing animations in `.hero-eng`: the architect's dimension
line "12.50 m" at the bottom-left (`.eng-dim`) and the building skyline drawing itself along the whole bottom
(`.eng-skyline`). Nothing may cover them: keep `.hv2-wrap` bottom padding ≥ 5rem and keep the hero bottom free of other blocks. The red services marquee under the hero is optional (off by default).

**Order under the hero:** hero → red services band (`hero_marquee`, ON by default) → intro video. The video section is
`100svh − header height` tall and pins *below* the sticky header (`--hdr-h`, measured in `scaleVideo()`), so its top is never
covered by the header.

**Intro video poster loading:** the poster is preloaded in the head (home only), the `<img>` is eager, and the frame shimmers until `posterOk`
(img `load`) — the play button and the poster fade in together. Never show the black frame + play icon before the picture is there.

**Intro video (`#intro.sx-fullvideo`) — scale OUT + plain texts around it:** full screen, directly under the hero with **no gap**,
square corners. While its section is pinned (150 % of a screen of scrolling) GSAP (`scaleVideo()` in `app.js`, hook
`data-scale-video`) shrinks it to ~26 % (40 % on phones) with 48px corners. **In the same scroll** (one scrubbed linear timeline, nothing waits for anything) four plain one-line texts travel in
from their own screen edge — the intro title from the top, "500+ completed projects" from the left, "8 types of service" from
the right, "100 % client satisfaction" from the bottom — and land beside the video exactly when it reaches its small size
(linear easing on both ⇒ the text is always outside the video, no overlap). Scrolling back up: the video grows and each text
leaves through its own edge. Items are `.sx-around` + `data-around="t|l|r|b"`, same type size `--ar-size`, placed next to the
video edge via `--vs`; **GSAP owns their `transform`, so never centre them with a CSS transform** (use margins / left+right 0). **What is shown is configurable** in Admin → Settings → Home sections → *Texts around the intro video* (setting
`around_items`: side top/left/right/bottom, optional counting number + suffix, bilingual text; remove a row to remove that text).
The intro section has only two other settings: the video link and the poster image (the old heading / paragraphs / person fields were removed). **No icons, no sub-heading, no
underline** — keep it that minimal. Numbers count up (`countAround()`). Scrubbed, reverses on scroll up. Do **not** reverse the video back to "scale in".

### 5.3 Slide transition (v2) — do not break the order
Old slide exits first (text lifts up 0.4s, picture slides left), then the new one enters: picture from the right
(1.1s, 0.3s delay), red block (0.6s delay), text rising one piece at a time (+0.1s each). Classes `.is-on` and
`.is-leaving` on `.hv2-slide`; `heroSlider()` tracks `prev`. The mouse tilt lives on `.hv2-visual`, the slide motion on
`.hv2-visual-wrap` — **keep them on different elements** or the transitions fight.

### 5.4 Preloader (`#page-curtain`, built in `public/assets/js/layout.js`)
A house draws itself — red roof, walls, ground line — the body fills white, then the **full logo** fades in inside it
(`.preloader-home`, classes `ph-*`, `forwards` animations, not looping so the logo stays up). Timings are slow on
purpose (≈3.8 s total). **Rule:** the logo animation always plays fully at least once; the curtain then lifts as soon as the
page is loaded (already loaded → right after the logo; still loading → after the load; 15 s failsafe) — see `curtainReady()`
in `app.js`. Show/hide, minimum time (can only lengthen it) and "first visit only" are admin options (Animation & preloader).
**The preloader belongs to the first visit only** (setting ON by default): from the second page on, `layout.js` does not even create the
curtain and links navigate instantly — there is no page-transition wipe any more (it only made navigation feel slow).
GSAP in `app.js` animates `[data-curtain-logo]` (fade) and lifts the curtain; don't put transform animations on that
element itself.

### 5.5 Header, top bar, footer, floating buttons
Generated by `public/assets/js/layout.js` from `window.SITE` (so they are identical on every page). Top bar =
`#site-topbar`, header = `#site-header` (sticky; gets `.is-scrolled` + shadow after 10px). Floating actions (bottom
right): Messenger (blue `#0084FF`), WhatsApp (green `#25D366`) with the **official brand glyphs**, and a black call
button. Do not redraw these icons.

### 5.6 Section pattern (home)
`<section id="…" class="py-20 lg:py-28 [bg-gray-50 | bg-ink-950 text-white]">` → eyebrow + H2 + lead → content grid.
Existing ids: `home, intro, services, portfolio, activities, process, about, testimonials, insights, faq, contact`
(+ optional clients section behind a feature flag).

### 5.7 Section polish (`sx-` classes, `public/assets/css/style.css`)
Every home section follows one pattern so new sections match automatically:
- **Eyebrow with animated icon:** `<p class="sx-eyebrow [sx-eyebrow--dark]"><span class="sx-ico"><svg class="sxa-…">…</svg></span><span>Label</span></p>`.
  The icon sits in a rounded badge with a pulsing ring (`.sx-ico::after`). Keep the label as a plain text node so the
  English translator still finds it. Section `h2` gets a short red bar that "breathes" (`#id h2::after` — add your
  section id to that selector list in `style.css`).
- **Icon animations** (put on the `<svg>`): `sxa-spin` (gear), `sxa-bob` (float), `sxa-bounce` (pin), `sxa-tilt` (ruler),
  `sxa-shake` (phone), `sxa-flash` (camera), `sxa-glow` (bulb), `sxa-blink` (building windows, class `w` on the path),
  `sxa-draw` (check mark, class `d` on the path). Icons are 24×24, `stroke="currentColor"`, stroke-width 1.8.
- **Section class:** `sx-sec` (+ `sx-sec--dark` on dark bands) adds the two faint slanted bands from the hero.
  `sx-gear` adds the slow background gear (process).
- **Cards:** `sx-card` (red top line grows on hover) with `.sx-card-ico` (3.6rem icon tile that rotates/fills on hover);
  portfolio cards get `.sx-go` (arrow badge); process steps use `.sx-step` (+ `.sx-step-ico` with a rotating dashed ring,
  icons from `window.SX_STEP_ICONS`); why-us uses `.sx-check` (pulsing tick) and `.sx-badge` (spinning ring + house icon).
- **Contact rows:** `.sx-ico.sx-ico--light` instead of emoji.
- **Section rhythm** (so the page never feels repetitive): intro (white) → services (grey) → works (**black**) →
  activities (grey) → process (**red**) → about (white) → testimonials (**black**) → insights (grey) → FAQ (white) →
  contact (**red**). Red sections use `sx-sec--red` (white text, white eyebrow/arrows, white cards keep dark text);
  black sections use `bg-ink-950 text-white` + `sx-sec--dark`. Never put two red sections next to each other, and
  never put photo galleries on red (use white/grey). Keep the short, text-only sections (process) for red.
- **Map above the footer (`#map.sx-map`):** a compact, full-width Google Maps embed (height `clamp(22rem, 50vh, 32rem)` — not full screen), last block of `<main>`. Address comes from
  Admin → General & Contact → *Google Maps embed* (setting `map_embed`, validated by `SiteData::mapSrc()` — only
  `https://www.google.com/maps/embed?…`; empty = no section). A click-to-interact overlay (`.sx-map-lock`) keeps the map from
  stealing page scroll. (No custom address card: Google's own place card already sits top-left and a second one overlapped it.) It needs internet (the one online-only piece besides video links).
- **Intro film:** the video in the intro section is a 56-second 1920×1080 film with a Bangla narration and a synthesised energetic music bed, built from `tools/intro-film/`
  (README there). When the phone number, address or the 1500+ / 8 / 1500+ numbers change, update the film text too (`composer.html`) and re-render.
- **Services grid (`.sx-services`):** flex, centred — 4 columns on desktop (the client's 8 services = 2 even rows), 2 on tablet, 1 on phone;
  a short last row stays centred, never left-aligned.
- **Section headings are admin-editable:** the small label, heading, paragraph and (works + insights only) the button of every home
  section come from Admin → Home Page → *Section headings* (settings `sec_<section>_<eyebrow|title|text|btn_show|btn_label|btn_url>`,
  defaults in `SiteData::SECTION_DEFAULTS`, exposed to the page as `window.SECTIONS`, bound in `home.blade.php` with
  `x-text="sections.<id>.…"`). An empty field hides that line. When you add a new home section, add it to `SECTION_DEFAULTS`, to the
  `sections` group in `config/admin.php`, and bind its header the same way — never hard-code the heading text.
- **Our Work main categories — Completed / Running:** every project has `status` (`completed` default | `running`) and an optional
  `progress` %. Pages: `/portfolio` (all), `/portfolio/completed`, `/portfolio/running` (`SiteController::portfolio($status)`).
  The header "Our Work" item opens a hover dropdown with the two categories (`layout.js`, classes `nav-dd*`, toggle in Admin →
  Header); the home works section has two tabs (`.sx-worktabs`) that filter the 4 cards; cards show a pulsing "Running" chip and a
  progress bar (`.sx-run-chip`, `.sx-prog`). Labels / page headings / paragraphs are editable in Admin → Home Page → Section
  headings (settings `work_<status>_<label|title|text>`, defaults `SiteData::WORK_DEFAULTS`, exposed as `window.WORK`).
- **Works section is compact on purpose:** `.sx-works` = flex, centred, **2 columns (col-6) × 2 rows = 4 projects** on tablet/desktop (1 on phone), 4:3 images and the original card typography (title `text-xl sm:text-2xl`, padding `p-6 sm:p-8`) — do not shrink these.
- **Slanted bands are optional and irregular:** add `sx-band-r` or `sx-band-l` to *some* sections only (currently
  services R, about L, testimonials R). Do not put them on every section.
All of it is infinite CSS, disabled only by `html.reduce-motion`.

---

## 5.8 Language & spelling rules (Bangla + English)
Use these forms everywhere (views, JS dictionary `i18n.js`, admin content, seeders). Keep new text consistent with them.
- **Bangla:** ড্রয়িং (not ড্রইং) · কীভাবে (not কিভাবে) · সেবাসমূহ (one word) · ওপর/ওপরে (not উপর) · ফায়ারফাইটিং (one word) ·
  বারান্দা (not ভেরান্ডা) · নামজারি (মিউটেশন)-এর (hyphen before -এর) · "3D" or "৩ডি" — the client writes the service name as
  "3D ও ল্যান্ডস্কেপ ডিজাইন"; project titles use ৩ডি.
- **English (British):** colours, visualisation, customised, realised · firefighting (one word) · Pourashava / Upazila / RAJUK
  (capitalised, never "municipality") · "Designed & Developed by" · "B.Sc. in Civil Engineering".
- Every static Bangla string shown in the English site needs a matching entry in `public/assets/js/i18n.js` (`EN = { … }`);
  if you change the Bangla text, change the dictionary key too.

## 5.9 Page navigation (Turbo Drive — SPA feel)
Links between site pages are handled by **Turbo Drive** (`@hotwired/turbo`, vendored to `public/assets/vendor/turbo.js`; refresh it with
`npm update @hotwired/turbo` then copy `node_modules/@hotwired/turbo/dist/turbo.es2017-umd.js` over it). Only the `<body>` is swapped, so
there is no reload, no flash and no preloader after the first page; a thin red bar shows while a page loads. Rules for page code:
- Everything that starts motion must be **built per page**: `app.js` runs `boot()` on `turbo:load` and `teardown()` on `turbo:before-render`
  (kills ScrollTriggers, Lenis, GSAP tweens, window listeners). Never register a `window` / `document` listener at the top of a page
  script without removing it — use `onWin(...)` in `app.js`, or Alpine's `destroy()` for timers (see `heroSlider` / `sobujHero`).
- Body scripts are re-executed on every visit (so `function site()` etc. exist for Alpine); head scripts only once. Put per-page state
  in a body `@push('scripts')` script; clean any `<html>` class it sets in `teardown()` (e.g. `hero-v1`).
- **Pages after the first appear IN PLACE:** on a Turbo visit nothing that is already on screen is animated in from below (`instantEntry` /
  `inView()` in `app.js`; hero + heading + reveal animations skip it) — that sliding-up read as a white flash. Only content below the
  fold still reveals on scroll. The new page is covered by the skeleton from the very first frame and the skeleton fades out once the page
  is built. When you add an entry animation, make it skip when `instantEntry` and the element is in view.
- **Smooth transitions:** a fast page simply swaps in (no dimming, no skeleton, nothing white). Only when the server takes > 400 ms a
  full-viewport shimmer skeleton (`#page-skeleton`, classes `sk*`, with its own fake top bar + header so the top is never blank — the
  real header may be scrolled away) covers the old page; the new `<body>` then arrives hidden under that skeleton (`body.is-entering`),
  `boot()` builds it and the skeleton fades out. Keep skeleton shapes at the real heights (hero image 4:3, cards 12rem).
- **Header / footer / preloader are built into the incoming `<body>` before it is shown** (`layout.js` → `buildLayout(body)`, called on
  `turbo:before-render`). `layout.js` is a head script that runs once; do **not** put page-wide markup injection in a body script — an
  external body script runs *after* the swap, which shows the new page without header/footer for a moment and then makes it jump.
- Pages are not cached (`turbo-cache-control: no-cache`), so Back/Forward always fetch a fresh, correctly initialised page.
- Pages that are not part of the site layout (admin, sitemap, robots) must not be linked with Turbo: add `data-turbo="false"`.

## 5.10 Responsive checklist (tested 360 / 390 / 768 / 1024 / 1366 / 1920 px)
- No horizontal scroll on any page at any width (checked by script: no element may extend past the viewport).
- 1024–1279 px is the tight range: the menu is 8 links + CTA — keep `white-space: nowrap` and the smaller gap in the media query in `style.css`.
- Phones: one column everywhere, hero card + red block + roof scale with the container, the video section shrinks to 40 % and the three
  around-video texts sit in one row under it, the footer has extra bottom padding for the floating buttons.
- A 16:9 image or poster in a portrait frame is centre-cropped by `object-cover` — never put text inside posters / hero images.

- **Phones (≤ 767 px):** hero v2 shows the slider picture **first** and the text underneath (`order: -1` on `.hv2-visual-wrap`). The intro video
  is a 16:9 box (`56.25vw` tall + room for the texts), **not** the full screen height: no pin, it scales out (to 60 %) while it scrolls
  through. `--fhalf` (half the frame height) positions the around-video texts for both layouts.

## 6. Motion

- Library: GSAP + ScrollTrigger + Lenis (smooth scroll), Alpine for state. All local.
- Data-attribute hooks (see header of `public/assets/js/app.js`): `data-reveal[=left|right|scale|clip]`, `data-split`,
  `data-parallax`, `data-scale-video`, `data-count` (+`data-suffix`), `data-magnetic`, `data-progress`, `data-float`,
  `data-shine`, `data-hero*`.
- **GSAP owns `transform` / `opacity` on elements with a `data-*` hook.** Do not add CSS transform/transition classes
  to those elements.
- Decorative CSS animation (hero `eng-*`, preloader `ph-*`, tower float) runs **forever or once as designed**. It must
  **not** use `@media (prefers-reduced-motion)` — the owner turned "follow reduce-motion" OFF on purpose. Use the
  class `html.reduce-motion`, which `app.js` sets only when the admin option *"Follow the visitor's reduce motion
  setting"* is ON.
- Easing: entrances `cubic-bezier(.16, 1, .3, 1)`, exits `cubic-bezier(.4, 0, 1, 1)`. Durations: micro 200ms,
  exit 300–500ms, entrance 800–1100ms. Stagger 100ms.
- Never animate `width/height/top/left`; use `transform` and `opacity`.

---

## 7. Naming & file map

| Prefix | Meaning | File |
|---|---|---|
| `hv2-` | hero v2 | `public/assets/css/style.css` |
| `hero-eng`, `eng-` | engineering decoration (gears, cubes …) | `style.css` |
| `ph-`, `preloader-` | preloader house | `style.css`, `layout.js` |
| `sx-`, `sxa-` | section polish: icons, cards, decoration, icon animations | `style.css`, `home.blade.php` |
| `ss-` | Sobuj slider (hero v1) | `public/assets/lib/sobuj_slider/` |
| `html.hero-v1`, `html.hero-v2`, `html.reduce-motion`, `html.dark` | state classes on `<html>` | set in `home.blade.php`, `app.js`, `i18n.js` |

```
resources/views/home.blade.php        home page (inside @verbatim: Blade is OFF, Alpine/JS expressions are literal)
resources/views/layouts/site.blade.php  <head>, SEO, asset links, theme <style>
public/assets/css/style.css           ALL custom CSS (tokens, hero, preloader, dark overrides). Edit this.
public/assets/css/tailwind.css        COMPILED Tailwind — never edit by hand
resources/css/tailwind.css            Tailwind source; tailwind.config.js has the brand/ink colours
public/assets/js/{app,layout,i18n,marquee,video}.js
app/Support/{Theme,SiteData,Assets,FieldProcessor,AdminMenu,Features,SeoBuilder}.php
config/admin.php                      admin screens: settings groups + content resources (+ field definitions)
config/features.php                   gated future modules (clients, quotes, invoices, attendance, accounting)
database/seeders/SiteSeeder.php       defaults for a fresh install (+ data/content.json)
```

**Tailwind gotcha:** utilities are pre-compiled. A class that is not already used somewhere in `resources/views`,
`public/assets/js`, `app/` or `config/admin.php` will not exist until you run `npm run build:css`. For new bespoke
UI, write a prefixed class in `style.css` instead (that is what the hero does).

---

## 8. Recipes

### 8.1 Add a new home section with admin-editable content
1. **Model + migration** (`php artisan make:model Thing -m`). Bilingual columns are JSON and use the `Translatable`
   trait; cast them to `array`. Include `sort` (int) and `active` (bool).
2. **Admin**: add an entry to `config/admin.php` → `resources` (title, model, `columns` for the list, `fields`
   using the types below). Add it to the menu in `App\Support\AdminMenu` if it needs a link. The generic
   `ContentController` gives list / create / edit / delete / drag-reorder for free.
3. **Public data**: add a key to `App\Support\SiteData::build()` (e.g. `'THINGS' => $list(Thing::class)->map(...)`),
   resolving the language with `$t($model, 'field')`. Never expose private data (phones, emails, notes).
4. **View**: add the `<section>` to `home.blade.php` (inside the `@verbatim` block, read `window.THINGS` through Alpine
   `x-for`), following §5.6. Add `data-reveal` for scroll-in.
5. **Seed** a sample row in `SiteSeeder` + `content.json`, **flush** the cache (`SiteData::flush()` runs on every admin
   save; after editing PHP/DB directly run `php artisan cache:clear`), and add a test in `tests/Feature`.

Field types for `config/admin.php`: `text url number date textarea code select toggle color image bi_text bi_textarea
bi_html bi_lines bi_pairs repeater gallery`. Useful keys: `required`, `help`, `group`, `options`, `feature`
(gate behind `config/features.php`), `developer_only`, and for style-dependent forms `styles`, `labels`, `helps`.

### 8.2 Add / change a colour
Admin → Theme (or the seeder default) → update §2.2 here. Do not touch CSS unless a new *role* is introduced.

### 8.3 Add a hero decoration or animation
Put the markup inside `.hero-eng` (aria-hidden), style it in `style.css` with the `eng-` prefix, `currentColor` for
colour, `opacity` ≤ .35, and animate only `transform`/`opacity` with `infinite` loops. Keep it grey/faint.

### 8.4 Add a button
Reuse `.hv2-btn` + a variant, or the site CTA classes. No new button styles without adding them to §5.1.

---

## 9. Checklist before you finish a UI change

- [ ] Looks right in **light and dark**, desktop and a 375px phone.
- [ ] Bangla **and** English text present; headings do not clip (line-height ≥ 1.2).
- [ ] No hard-coded brand hex; tokens only. Red never sits on red.
- [ ] No `@media (prefers-reduced-motion)` for decorative motion; `html.reduce-motion` only.
- [ ] No new Tailwind classes that are not compiled (or you ran `npm run build:css`).
- [ ] Admin option exists for anything an owner would edit, with a clear label + help text and **recommended image sizes**.
- [ ] `php artisan test` passes (`~/.config/herd/bin/php84/php.exe` — PHP is not on PATH here).
- [ ] This document updated if a token, component or convention changed.
- [ ] **Do not commit or push** unless the owner asks. The owner handles git.

---

## 10. Environment notes

- Local URL: `https://adnan-architect-and-engineers.test` (Laravel Herd, PHP 8.4); MySQL from XAMPP, DB `adnan_architects`.
- Admin: `/admin` (developer account `developer@sobuj.com`). The "Design & developed by Sobuj" footer credit is permanent.
- Compile Tailwind: `npm run build:css`. Run tests: `php artisan test` (sqlite in-memory).
- Browsers cache `style.css`/JS aggressively during design work: hard-refresh (Ctrl+F5). Asset URLs are versioned by
  file mtime via `App\Support\Assets::v()`.
