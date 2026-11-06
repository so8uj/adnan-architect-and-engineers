<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\Insight;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\WhyItem;
use Illuminate\Support\Facades\Cache;
use App\Support\Features;

/**
 * Builds the data the front-end JavaScript reads (window.SITE, window.PROJECTS ...).
 * The shapes are exactly what the old static data.js had, so the page scripts did not need to change.
 */
class SiteData
{
    public static function payload(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return Cache::rememberForever("site.payload.$locale", fn () => static::build($locale));
    }

    public static function flush(): void
    {
        foreach (['bn', 'en'] as $l) {
            Cache::forget("site.payload.$l");
        }
        Cache::forget('settings.all');
    }

    /** Default section headings [bn, en] — used until the owner saves them (Admin -> Home Page -> Section headings). */
    public const SECTION_DEFAULTS = [
        'services' => ['eyebrow' => ['আমাদের সেবাসমূহ', 'Our Services'], 'title' => ['ভবন নির্মাণের প্রতিটি ধাপে আপনার পাশে', 'By your side at every step of construction'], 'text' => ['অভিজ্ঞ আর্কিটেক্ট ও ইঞ্জিনিয়ার টিমের মাধ্যমে প্রয়োজনীয় সব ড্রয়িং, ডিজাইন ও অনুমোদন সেবা।', 'All the drawings, designs and approval services you need, from an experienced team of architects and engineers.']],
        'portfolio' => ['eyebrow' => ['আমাদের কাজ', 'Our Work'], 'title' => ['সম্পন্ন প্রজেক্টের নমুনা', 'Samples of completed projects'], 'text' => ['যেকোনো প্রজেক্টে ক্লিক করে সব ড্রয়িং ও বিস্তারিত বিবরণ দেখুন।', 'Click any project to see all drawings and full details.'], 'btn_label' => ['সব প্রজেক্ট দেখুন', 'View all projects'], 'btn_url' => '/portfolio'],
        'activities' => ['eyebrow' => ['গ্যালারি', 'Gallery'], 'title' => ['আমাদের কার্যক্রম', 'Our Activities'], 'text' => ['সাইট পরিদর্শন ও কাজের তদারকির নিয়মিত কিছু মুহূর্ত। ছবিতে ক্লিক করে বড় করে দেখুন।', 'Regular moments from site visits and work supervision. Click a photo to enlarge it.']],
        'process' => ['eyebrow' => ['কাজের ধাপ', 'Process'], 'title' => ['কীভাবে আমরা কাজ করি', 'How we work'], 'text' => ['', '']],
        'about' => ['eyebrow' => ['আমাদের সম্পর্কে', 'About Us'], 'title' => ['কেন Adnan Architects & Engineers?', 'Why Adnan Architects & Engineers?'], 'text' => ['আমরা শুধু নকশা করি না — আপনার জমির সর্বোচ্চ ব্যবহার, নিরাপদ স্ট্রাকচার এবং দ্রুত অনুমোদন নিশ্চিত করি।', 'We do more than design — we ensure the best use of your land, a safe structure and fast approval.']],
        'testimonials' => ['eyebrow' => ['ক্লায়েন্টদের কথা', 'Client Voices'], 'title' => ['যাঁরা আমাদের ওপর ভরসা করেছেন', 'People who have trusted us'], 'text' => ['', '']],
        'insights' => ['eyebrow' => ['ইনসাইট', 'Insights'], 'title' => ['জানুন, বুঝুন, সিদ্ধান্ত নিন', 'Learn, understand, decide'], 'text' => ['প্ল্যান পাস, ডিজাইন ও নির্মাণ নিয়ে দরকারি কিছু লেখা।', 'Some useful reads on plan approval, design and construction.'], 'btn_label' => ['সব ইনসাইট দেখুন', 'View all insights'], 'btn_url' => '/insights'],
        'faq' => ['eyebrow' => ['', ''], 'title' => ['সাধারণ জিজ্ঞাসা', 'Frequently Asked Questions'], 'text' => ['', '']],
        'contact' => ['eyebrow' => ['', ''], 'title' => ['আপনার প্রজেক্ট নিয়ে কথা বলুন', 'Talk to us about your project'], 'text' => ['ফর্মটি পূরণ করুন অথবা সরাসরি কল করুন। আমরা দ্রুত যোগাযোগ করব।', 'Fill in the form or call us directly. We will get back to you quickly.']],
    ];

    /** The same defaults as flat settings keys (sec_<section>_<field>), for the seeder and for the admin form. */
    public static function sectionSettingDefaults(): array
    {
        $out = [];
        foreach (self::SECTION_DEFAULTS as $id => $f) {
            foreach ($f as $k => $v) {
                $out["sec_{$id}_{$k}"] = is_array($v) ? ['bn' => $v[0], 'en' => $v[1]] : $v;
            }
            if (isset($f['btn_label'])) {
                $out["sec_{$id}_btn_show"] = true;
            }
        }

        return $out;
    }

    /** Section headings for the home page, in the visitor's language. */
    private static function sections(string $l): array
    {
        $pick = fn ($v) => is_array($v) ? (string) static::pick($v, $l) : (string) $v;
        $out = [];
        foreach (self::SECTION_DEFAULTS as $id => $f) {
            $row = [];
            foreach (['eyebrow', 'title', 'text'] as $k) {
                $row[$k] = $pick(Setting::get("sec_{$id}_{$k}", ['bn' => $f[$k][0], 'en' => $f[$k][1]]));
            }
            if (isset($f['btn_label'])) {
                $row['btn'] = [
                    'show' => (bool) Setting::get("sec_{$id}_btn_show", true),
                    'label' => $pick(Setting::get("sec_{$id}_btn_label", ['bn' => $f['btn_label'][0], 'en' => $f['btn_label'][1]])),
                    'url' => (string) Setting::get("sec_{$id}_btn_url", $f['btn_url']),
                ];
            }
            $out[$id] = $row;
        }

        return $out;
    }

    /** Our Work main categories [label, page heading, page paragraph] as [bn, en]. Edited in Admin -> Home Page -> Section headings. */
    public const WORK_DEFAULTS = [
        'completed' => ['label' => ['সম্পন্ন প্রজেক্ট', 'Completed Projects'], 'title' => ['আমাদের সম্পন্ন প্রজেক্ট', 'Our Completed Projects'], 'text' => ['যেসব প্রজেক্ট আমরা সফলভাবে সম্পন্ন করেছি — যেকোনোটিতে ক্লিক করে সব ছবি ও বিস্তারিত দেখুন।', 'Projects we have completed successfully — click any project to see all photos and details.']],
        'running' => ['label' => ['চলমান প্রজেক্ট', 'Running Projects'], 'title' => ['আমাদের চলমান প্রজেক্ট', 'Our Running Projects'], 'text' => ['যেসব প্রজেক্টের কাজ এখন চলছে — অগ্রগতিসহ দেখুন।', 'Projects whose work is in progress right now — see how far each one has come.']],
    ];

    /** Flat settings (work_<status>_<field>) with their defaults, for the seeder and the admin form. */
    public static function workSettingDefaults(): array
    {
        $out = [];
        foreach (self::WORK_DEFAULTS as $st => $f) {
            foreach ($f as $k => $v) {
                $out["work_{$st}_{$k}"] = ['bn' => $v[0], 'en' => $v[1]];
            }
        }

        return $out;
    }

    /** The two Our Work main categories (completed / running) with their labels, page texts, link and project count. */
    public static function work(string $l): array
    {
        $out = [];
        foreach (self::WORK_DEFAULTS as $st => $f) {
            $row = ['status' => $st, 'url' => '/portfolio/'.$st, 'count' => Project::where('active', true)->where('status', $st)->count()];
            foreach ($f as $k => $v) {
                $val = Setting::get("work_{$st}_{$k}", ['bn' => $v[0], 'en' => $v[1]]);
                $row[$k] = is_array($val) ? (string) static::pick($val, $l) : (string) $val;
            }
            $out[$st] = $row;
        }

        return $out;
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return preg_match('#^(https?:)?//#', $path) ? $path : '/'.ltrim($path, '/');
    }

    /**
     * The Google Maps embed address for the full-screen map above the footer (Admin -> General & Contact -> Map).
     * The admin may paste the whole <iframe ...> code or just the src address; only https://www.google.com/maps/embed
     * addresses are accepted, so nothing else can be framed into the page. Empty / invalid = no map section.
     */
    public static function mapSrc(): ?string
    {
        $v = trim((string) Setting::get('map_embed', ''));
        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $v, $m)) {
            $v = html_entity_decode($m[1]);
        }

        return preg_match('#^https://www\.google\.com/maps/embed\?#', $v) ? $v : null;
    }

    public static function digits(string $s, string $locale): string
    {
        return $locale === 'bn' ? strtr($s, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']) : $s;
    }

    private static function build(string $l): array
    {
        $t = fn ($m, $f) => $m->tr($f, $l);
        $S = fn (string $k, $d = '') => Setting::tr($k, $l, $d);
        $list = fn ($model) => $model::query()->where('active', true)->orderBy('sort')->orderBy('id')->get();

        return [
            'SITE' => [
                'name' => $S('site_name'),
                'phone' => Setting::get('phone', ''),
                'email' => Setting::get('email', ''),
                'address' => $S('address'),
                'facebook' => Setting::get('facebook', ''),
                'whatsapp' => Setting::get('whatsapp', ''),
                'messenger' => Setting::get('messenger', ''),
                'greeting' => Setting::get('greeting', 'Hello'),
                'logo' => static::url(Setting::get('logo', 'assets/img/logo.png')),
                'defaultTheme' => Setting::get('theme_default', 'light'),
                'respectReducedMotion' => (bool) Setting::get('respect_reduced_motion', false),
                'preloader' => [
                    'show' => (bool) Setting::get('preloader_show', true),
                    'minMs' => (int) Setting::get('preloader_min_ms', 1200),
                    'onlyFirstVisit' => (bool) Setting::get('preloader_first_visit_only', true),
                ],
                // the credit can not be switched off: empty values fall back to the built-in ones
                'credit' => [
                    'show' => true,
                    'text' => $S('credit_text') ?: ($l === 'en' ? 'Designed & Developed by' : 'ডিজাইন ও ডেভেলপ করেছেন'),
                    'name' => $S('credit_name') ?: ($l === 'en' ? 'Sobuj' : 'সবুজ'),
                    'url' => Setting::get('credit_url') ?: 'https://www.facebook.com/so8uj',
                ],
                'header' => [
                    'topbar' => (bool) Setting::get('header_topbar_show', true),
                    'ctaShow' => (bool) Setting::get('header_cta_show', true),
                    'ctaLabel' => $S('header_cta_label'),
                    'ctaUrl' => Setting::get('header_cta_url', '/#contact'),
                    'workDropdown' => (bool) Setting::get('header_work_dropdown', true),
                    'showLang' => (bool) Setting::get('header_show_lang', true),
                    'showTheme' => (bool) Setting::get('header_show_theme', true),
                    'menu' => array_values(array_filter(array_map(fn ($m) => [
                        'label' => is_array($m['label'] ?? null) ? ((($m['label'][$l] ?? '') ?: ($m['label']['bn'] ?? ''))) : ($m['label'] ?? ''),
                        'href' => $m['url'] ?? '/',
                    ], (array) Setting::get('header_menu', [])), fn ($m) => $m['label'] !== ''))
                ],
                'footer' => [
                    'ctaShow' => (bool) Setting::get('footer_cta_show', true),
                    'ctaTitle' => $S('footer_cta_title'), 'ctaText' => $S('footer_cta_text'),
                    'about' => $S('footer_about'),
                    'showSocial' => (bool) Setting::get('footer_show_social', true),
                    'showLinks' => (bool) Setting::get('footer_show_links', true),
                    'showServices' => (bool) Setting::get('footer_show_services', true),
                    'copyright' => str_replace('{year}', date('Y'), (string) $S('footer_copyright')),
                ],
            ],
            'HERO_CONFIG' => [
                'style' => Setting::get('hero_style', 'v2') === 'v1' ? 'v1' : 'v2',
                'marquee' => (bool) Setting::get('hero_marquee', true),
                'columns' => max(2, min(8, (int) Setting::get('hero_columns', 4))),
                'overlay' => max(0, min(90, (int) Setting::get('hero_overlay', 45))),
                'arrows' => (bool) Setting::get('hero_arrows', true),
                'dots' => (bool) Setting::get('hero_dots', true),
                'autoplay' => (bool) Setting::get('hero_autoplay', true),
                'interval' => (int) Setting::get('hero_interval', 6000),
                'pauseOnHover' => (bool) Setting::get('hero_pause_on_hover', true),
            ],
            'HERO_SLIDES' => $list(HeroSlide::class)->map(fn ($s) => [
                'badge' => $t($s, 'badge'), 'title1' => $t($s, 'title1'), 'title2' => $t($s, 'title2'), 'text' => $t($s, 'text'),
                'image' => static::url($s->image), 'fit' => $s->image_fit, 'pos' => $s->image_pos,
                'cta' => ['label' => $t($s, 'cta_label'), 'href' => $s->cta_url],
                'chip' => ['icon' => $s->chip_icon, 'title' => $t($s, 'chip_title'), 'sub' => $t($s, 'chip_sub')],
            ])->all(),
            'AROUND' => static::around($l),
            'SECTIONS' => static::sections($l),
            'WORK' => static::work($l),
            'SERVICES' => $list(Service::class)->map(fn ($m) => ['title' => $t($m, 'title'), 'icon' => $m->icon, 'desc' => $t($m, 'description')])->all(),
            'STEPS' => $list(ProcessStep::class)->map(fn ($m) => ['title' => $t($m, 'title'), 'desc' => $t($m, 'description')])->all(),
            'WHY' => $list(WhyItem::class)->map(fn ($m) => ['title' => $t($m, 'title'), 'desc' => $t($m, 'description')])->all(),
            'FAQS' => $list(Faq::class)->map(fn ($m) => ['q' => $t($m, 'question'), 'a' => $t($m, 'answer')])->all(),
            'ACTIVITIES' => $list(Activity::class)->map(fn ($m) => ['src' => static::url($m->image), 'title' => $t($m, 'title'), 'note' => $t($m, 'note')])->all(),
            'PROJECTS' => $list(Project::class)->map(fn ($p) => static::project($p, $l))->all(),
            'INSIGHTS' => $list(Insight::class)->map(fn ($i) => static::insight($i, $l))->all(),
            // future feature: absent (empty) unless switched on in .env. Private client details never go in here.
            'CLIENTS' => Features::on('clients') ? static::clients($l) : [],
            'CLIENTS_SECTION' => Features::on('clients') ? ['title' => $S('clients_title'), 'subtitle' => $S('clients_subtitle')] : null,
            'INTRO' => [
                'video' => Setting::get('intro_video', ''), 'poster' => static::url(Setting::get('intro_poster')),
            ],
            'TESTIMONIALS' => $list(Testimonial::class)->map(fn ($m) => [
                'type' => $m->type, 'name' => $t($m, 'name'), 'role' => $t($m, 'role'), 'text' => $t($m, 'text'), 'rating' => $m->rating,
                'avatar' => static::url($m->avatar), 'image' => static::url($m->image), 'video' => $m->video_url, 'poster' => static::url($m->poster),
                'placeholder' => $m->is_sample,
            ])->all(),
        ];
    }

    private static function clients(string $l): array
    {
        $limit = max(1, min(24, (int) (Setting::get('clients_home_limit') ?: 10)));

        return Client::query()->where('active', true)->where('show_on_home', true)
            ->orderBy('sort')->orderBy('id')->limit($limit)->get()
            ->map(fn (Client $c) => ['name' => $c->tr('name', $l), 'logo' => static::url($c->logo), 'url' => $c->website ?: null])
            ->all();
    }

    private static function pick(?array $v, string $l): mixed
    {
        $x = $v[$l] ?? null;

        return ($x === null || $x === '' || $x === []) ? ($v['bn'] ?? null) : $x;
    }

    /**
     * The plain texts shown around the intro video (Admin -> Settings -> Home sections -> "Texts around the intro video").
     * One item per position (top / left / right / bottom); a number is optional (it counts up). Not saved yet = no texts.
     */
    private static function around(string $l): array
    {
        $rows = Setting::get('around_items');
        if (! is_array($rows)) {
            $rows = [];
        }

        $byPos = [];
        foreach ($rows as $r) {
            $pos = $r['pos'] ?? '';
            if (! in_array($pos, ['top', 'left', 'right', 'bottom'], true) || isset($byPos[$pos])) {
                continue;
            }
            $text = is_array($r['text'] ?? null) ? (string) static::pick($r['text'], $l) : (string) ($r['text'] ?? '');
            $has = isset($r['value']) && $r['value'] !== '' && $r['value'] !== null;
            if ($text === '' && ! $has) {
                continue;
            }
            $byPos[$pos] = ['pos' => $pos, 'value' => $has ? (int) $r['value'] : null, 'suffix' => (string) ($r['suffix'] ?? ''), 'text' => $text];
        }

        return array_values(array_filter(array_map(fn ($p) => $byPos[$p] ?? null, ['top', 'left', 'right', 'bottom'])));
    }

    public static function project(Project $p, string $l): array
    {
        return [
            'slug' => $p->slug,
            'status' => $p->status === 'running' ? 'running' : 'completed', 'progress' => $p->progress,
            'title' => $p->tr('title', $l), 'category' => $p->tr('category', $l), 'tagline' => $p->tr('tagline', $l), 'summary' => $p->tr('summary', $l),
            'cover' => static::url($p->cover), 'coverPos' => $p->cover_pos ?: 'center',
            'meta' => (array) static::pick($p->meta, $l),
            'description' => (array) static::pick($p->description, $l),
            'highlights' => (array) static::pick($p->highlights, $l),
            'images' => array_map(fn ($i) => [
                'src' => static::url($i['src'] ?? ''),
                'title' => static::pick($i['title'] ?? null, $l), 'size' => static::pick($i['size'] ?? null, $l), 'note' => static::pick($i['note'] ?? null, $l),
            ], (array) $p->images),
        ];
    }

    public static function insight(Insight $i, string $l): array
    {
        return [
            'slug' => $i->slug,
            'title' => $i->tr('title', $l), 'category' => $i->tr('category', $l), 'excerpt' => $i->tr('excerpt', $l),
            'date' => static::digits($i->published_at?->format('Y-m-d') ?? '', $l),
            'readTime' => $i->tr('read_time', $l),
            'cover' => static::url($i->cover), 'coverFit' => $i->cover_fit,
            'body' => $i->tr('body', $l),
        ];
    }
}
