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

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return preg_match('#^(https?:)?//#', $path) ? $path : '/'.ltrim($path, '/');
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
                    'text' => $S('credit_text') ?: ($l === 'en' ? 'Design & Developed by' : 'ডিজাইন ও ডেভেলপ করেছেন'),
                    'name' => $S('credit_name') ?: ($l === 'en' ? 'Sobuj' : 'সবুজ'),
                    'url' => Setting::get('credit_url') ?: 'https://www.facebook.com/so8uj',
                ],
                'header' => [
                    'topbar' => (bool) Setting::get('header_topbar_show', true),
                    'ctaShow' => (bool) Setting::get('header_cta_show', true),
                    'ctaLabel' => $S('header_cta_label'),
                    'ctaUrl' => Setting::get('header_cta_url', '/#contact'),
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
            'STATS' => array_map(fn ($s) => [
                'value' => (int) ($s['value'] ?? 0), 'suffix' => $s['suffix'] ?? '',
                'label' => is_array($s['label'] ?? null) ? (($s['label'][$l] ?? '') ?: ($s['label']['bn'] ?? '')) : ($s['label'] ?? ''),
            ], (array) Setting::get('stats', [])),
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
                'eyebrow' => $S('intro_eyebrow'), 'title' => $S('intro_title'),
                'video' => Setting::get('intro_video', ''), 'poster' => static::url(Setting::get('intro_poster')),
                'paragraphs' => (array) $S('intro_paragraphs', []), 'points' => (array) $S('intro_points', []),
                'person' => ['name' => $S('intro_person_name'), 'role' => $S('intro_person_role')],
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

    public static function project(Project $p, string $l): array
    {
        return [
            'slug' => $p->slug,
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
