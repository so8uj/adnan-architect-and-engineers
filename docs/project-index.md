# Project index — Adnan Architects & Engineers

State at **2026-11-06**: the public site and the admin are built; **68 feature tests pass**. Frontend design is considered complete.
Design rules live in [design-system.md](design-system.md) — read it before changing UI. Agents: see [../AGENTS.md](../AGENTS.md).

## Stack
Laravel 13 · PHP 8.4 · MySQL (XAMPP, db `adnan_architects`) · Laravel Herd (`https://adnan-architect-and-engineers.test`) ·
Alpine.js 3 · GSAP + ScrollTrigger · Lenis · **Turbo Drive** (SPA-style navigation) · Tailwind 3 (pre-compiled) · Sortable.js.
Everything is served locally (no CDN): `public/assets/vendor/`.

## Public site (routes in `routes/web.php`, controller `SiteController`)
| URL | View | Notes |
|---|---|---|
| `/` | `home.blade.php` | hero v1/v2 → services band → intro film → services → works → activities → process → about → clients* → testimonials → insights → FAQ → contact → map |
| `/portfolio`, `/portfolio/completed`, `/portfolio/running` | `portfolio.blade.php` | Our Work; two main categories (status) |
| `/project/{slug}` | `project.blade.php` | gallery + lightbox, running chip + progress |
| `/insights`, `/insight/{slug}` | `insights`, `insight` | blog |
| `POST /contact` | — | contact form → `contact_messages` (throttled) |
| `/sitemap.xml`, `/robots.txt` | — | built from settings |
| errors 403/404/419/429/500/503 | `errors/*` | branded |
\* clients section is behind a feature flag (`config/features.php`).

## Admin (`/admin`, `routes/admin.php`) — config-driven, one generic CRUD
`config/admin.php` defines every list (`resources`) and settings page (`settings`); `ContentController` + `SettingsController` + `FieldProcessor`
render and save them. Menu: `app/Support/AdminMenu.php`.
- **Sidebar (normalised, data in `config/admin.php` keys `website` + `crm`, built by `AdminMenu`):** Dashboard · Messages ·
  **Website Management** (Home Page · Our Work · Insights · Header · Footer · Global settings — each page shows a breadcrumb + a **tab strip**
  across the top; Home tabs: Hero slider, Section headings, Intro video, Services, Process, Why us, Testimonials, Activities, FAQs, Clients strip*, SEO & code) ·
  **CRM** (modules switched on, one line each in `admin.crm`) · **Administration** (User management, Departments, Roles & permissions, Profile — each shown only if the role allows).
  Add a tab = one line in `admin.website.<page>.tabs` (`content`|`setting`|`seo` pointer).
- Messages (contact form), Users (developer account hidden/protected), Profile, Cache clear. CRM modules (clients, quotes, invoices,
  attendance, accounting) are switched on in `.env` (`FUTURE_OPTIONS_ENABLED`, `FEATURE_*`); **when a module is on, every admin sees it**
  (the old "developer only" visibility is gone). Only **clients** is built so far.

## Roles & permissions (built 2026-11-07)
Every user has one **department** and one **role**. A role holds permissions `<module>.view` / `<module>.manage` (manage includes view); modules =
each Website Management page (`website.home`, `website.works` ...), each CRM line (`crm.clients`), `messages`, `users`, `departments`, `roles`
(catalogue built from `config/admin.php` by `App\Support\Permissions`, so a new menu page/CRM module gets permissions automatically).
`Http/Middleware/AuthorizeAdmin` maps every admin route to its permission (GET = view, anything else = manage; unmapped content/settings = Super Admin only);
`AdminMenu` hides what the role cannot open. **Super Admin** (protected system role) and the developer account pass everything; existing users were migrated to Super Admin;
a user with no role sees only dashboard + profile. Anti-escalation: you can only give roles/permissions you hold yourself, nobody changes their own role.
New module checklist: add its line to `admin.crm` (or `admin.website`) -> permissions appear in the role form -> protect custom routes by naming them `admin.<module>.*`
or extend `Permissions::required()`. Tests: `RolesAndPermissionsTest`.

## HR & Attendance (plan: [hr-plan.md](hr-plan.md))
Phase 1 built (shifts, clock in/out, late, per-user "Needs attendance", daily sheet + corrections). Phases 2-5 (holidays+leave, daily tasks, payroll, reports) next.
Sidebar block `admin.hr` in `config/admin.php`; flags `FEATURE_ATTENDANCE|LEAVE|TASKS|PAYROLL`. Demo people: `php artisan db:seed --class=DemoStaffSeeder` (password123).

## Data (migrations in `database/migrations/`)
`site tables` (settings, pages, seo_meta, hero_slides, services, process_steps, why_items, faqs, testimonials, activities, projects,
insights, contact_messages) · `users.is_developer` · `clients` · `projects.status/progress`. Bilingual columns are JSON `{bn,en}`
(`Translatable` trait). Seed: `SiteSeeder` + `database/seeders/data/content.json`.

## Code map (`app/`)
- `Support/SiteData.php` — the one payload the pages read (`window.SITE`, `HERO_*`, `AROUND`, `SECTIONS`, `WORK`, `PROJECTS` …), cached; `flush()` on every admin save.
- `Support/Theme.php` (colour tokens → CSS vars), `Assets.php` (versioned URLs), `SeoBuilder.php`, `Features.php`, `AdminMenu.php`, `FieldProcessor.php`.
- `Http/Middleware`: `SecurityHeaders`, `SetLocale`. Models in `app/Models` (+ `Concerns/{Translatable,HasSeo}`).

## Front-end code (`public/assets/`)
`css/style.css` (all custom CSS; `tailwind.css` is compiled — never edit) · `js/layout.js` (header/footer/preloader, built per page, also into the incoming
Turbo body) · `js/app.js` (GSAP/Lenis/page lifecycle, skeleton, intro-video scale-out) · `js/i18n.js` (EN dictionary) · `js/video.js` · `js/marquee.js` ·
`lib/sobuj_slider/` (hero v1) · `vendor/*` (incl. `turbo.js`).

## Design features (see design-system.md for details)
Logo-based palette (red `#e41431` / black / white) · light + dark · bilingual (Bangla default) · hero v1 (full-page slider) / v2 (logo-motif card,
engineering decoration, dimension line + skyline) · section rhythm (light/black/red) with animated icons · Our Work dropdown (Completed / Running) ·
full-screen intro film (56 s, Bangla voice-over + music) that scales out with texts around it · Turbo page changes with skeleton · house-draw preloader on first visit only ·
responsive checked at 360–1920 px.

## Tests (`tests/Feature`, run `php artisan test`)
`SiteTest`, `GoLiveTest`, `UsersAndFooterTest`, `FutureFeaturesTest`, `HeroSliderTest` (hero options, around-video texts, section headings), `WorkCategoriesTest`.

## Content & media
Contact: 01511092580 · anik.ph7@gmail.com · 293 Ali Ahmad Chunka Road, K. K. Tower, Bandar, Narayanganj. 8 services (client list). 15 projects
(2 originals + 12 single 3D & Landscape designs; #13 hidden — carries another company's watermark). Media in `public/uploads/2026/11/`
(**git-ignored — upload separately when deploying**): hero images, 3D renders, `adnan-intro-film-v3.mp4` + poster.
Film source & how to re-render: `tools/intro-film/README.md`. Raw client pictures/video: `demo_pictures/`.

## Open items before launch
- Replace the 3 sample testimonials (marked "Sample") · decide on watermarked render #13 · confirm WhatsApp number format.
- Deploy: upload `public/uploads/`, run `npm run build:css` only if Tailwind classes change, `php artisan migrate`, set `.env` (APP_URL, DB, mail), cache config/routes. `deploy/cpanel-root.htaccess` is for cPanel.
- Preloader "first visit only" should stay ON; hero style default is v2.
- Nothing from this session is committed (the owner handles git).
