<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\Insight;
use App\Models\Page;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WhyItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Loads the content that used to live in the static data.js (+ data.en.js) into the database.
 * Safe to run again: it only fills tables that are still empty.
 */
class SiteSeeder extends Seeder
{
    private array $bn;

    private array $en;

    public function run(): void
    {
        $json = json_decode(file_get_contents(__DIR__.'/data/content.json'), true);
        $this->bn = $json['bn'];
        $this->en = $json['en'];

        $this->settings();
        $this->pages();
        $this->heroSlides();
        $this->simple(Service::class, 'SERVICES', fn ($b, $e) => [
            'title' => $this->bi($b['title'], $e['title']),
            'description' => $this->bi($b['desc'], $e['desc']),
            'icon' => $b['icon'],
        ]);
        $this->simple(ProcessStep::class, 'STEPS', fn ($b, $e) => [
            'title' => $this->bi($b['title'], $e['title']), 'description' => $this->bi($b['desc'], $e['desc']),
        ]);
        $this->simple(WhyItem::class, 'WHY', fn ($b, $e) => [
            'title' => $this->bi($b['title'], $e['title']), 'description' => $this->bi($b['desc'], $e['desc']),
        ]);
        $this->simple(Faq::class, 'FAQS', fn ($b, $e) => [
            'question' => $this->bi($b['q'], $e['q']), 'answer' => $this->bi($b['a'], $e['a']),
        ]);
        $this->simple(Activity::class, 'ACTIVITIES', fn ($b, $e) => [
            'title' => $this->bi($b['title'], $e['title']), 'image' => $b['src'],
        ]);
        $this->testimonials();
        $this->projects();
        $this->insights();
        $this->admin();

        Cache::flush();
    }

    private function bi(mixed $bn, mixed $en): array
    {
        return ['bn' => $bn, 'en' => $en];
    }

    private function simple(string $model, string $key, callable $map): void
    {
        if ($model::query()->exists()) {
            return;
        }
        foreach ($this->bn[$key] as $i => $b) {
            $model::create($map($b, $this->en[$key][$i]) + ['sort' => $i + 1]);
        }
    }

    private function link(string $href): string
    {
        return match (true) {
            $href === 'portfolio.html' => '/portfolio',
            str_starts_with($href, 'project.html?p=') => '/project/'.substr($href, 15),
            str_starts_with($href, 'insight.html?p=') => '/insight/'.substr($href, 15),
            str_starts_with($href, 'index.html#') => '/#'.substr($href, 11),
            default => $href,
        };
    }

    private function settings(): void
    {
        $site = $this->bn['SITE'];
        $en = $this->en['SITE'];
        $intro = $this->bn['INTRO'];
        $introEn = $this->en['INTRO'];

        $defaults = [
            // general
            'site_name' => $this->bi('Adnan Architects & Engineers', 'Adnan Architects & Engineers'),
            'tagline' => $this->bi('আর্কিটেকচার ও ইঞ্জিনিয়ারিং সেবা', 'Architecture & Engineering Services'),
            'phone' => $site['phone'],
            'email' => $site['email'],
            'address' => $this->bi($site['address'], $en['address']),
            'facebook' => $site['facebook'],
            'whatsapp' => $site['whatsapp'],
            'messenger' => $site['messenger'],
            'greeting' => 'Hello',
            'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3655.694160491198!2d90.50938587589422!3d23.615299093948135!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b100442ac6bf%3A0x59efcac10ae1ab04!2sAdnan%20Architect%20%26%20Engineers!5e0!3m2!1sen!2sbd!4v1793714194041!5m2!1sen!2sbd',
            'logo' => 'assets/img/logo.png',
            'favicon' => 'assets/img/logo.png',
            // clients section (future feature)
            'clients_title' => $this->bi('আমাদের ক্লায়েন্ট', 'Our Clients'),
            'clients_subtitle' => $this->bi('যাঁরা আমাদের ওপর আস্থা রেখেছেন', 'Trusted by'),
            'clients_home_limit' => 10,
            // header
            'header_topbar_show' => true,
            'header_menu' => array_map(fn ($m) => ['label' => $this->bi($m[0], $m[1]), 'url' => $m[2]], [
                ['হোম', 'Home', '/'], ['সেবা', 'Services', '/#services'], ['আমাদের কাজ', 'Our Work', '/portfolio'],
                ['কার্যক্রম', 'Activities', '/#activities'], ['ইনসাইট', 'Insights', '/insights'], ['কাজের ধাপ', 'Process', '/#process'],
                ['আমাদের সম্পর্কে', 'About Us', '/#about'], ['যোগাযোগ', 'Contact', '/#contact'],
            ]),
            'header_cta_show' => true,
            'header_cta_label' => $this->bi('ফ্রি পরামর্শ নিন', 'Get Free Advice'),
            'header_cta_url' => '/#contact',
            'header_show_lang' => true,
            'header_show_theme' => true,
            // footer
            'footer_cta_show' => true,
            'footer_cta_title' => $this->bi('প্রজেক্ট শুরু করতে প্রস্তুত?', 'Ready to start your project?'),
            'footer_cta_text' => $this->bi('আজই কল করুন বা মেসেজ দিন — আমরা আপনার পাশে আছি।', 'Call or message us today — we are by your side.'),
            'footer_about' => $this->bi('সম্পূর্ণ আর্কিটেকচার ও ইঞ্জিনিয়ারিং সেবা — সয়েল টেস্ট থেকে প্ল্যান পাস পর্যন্ত।', 'Complete architecture & engineering services — from soil test to plan approval.'),
            'footer_show_links' => true,
            'footer_show_services' => true,
            'footer_show_social' => true,
            'footer_copyright' => $this->bi('© {year} Adnan Architects & Engineers. সর্বস্বত্ব সংরক্ষিত।', '© {year} Adnan Architects & Engineers. All rights reserved.'),
            'credit_text' => $this->bi($site['credit']['text'], $en['credit']['text']),
            'credit_name' => $this->bi($site['credit']['name'], $en['credit']['name']),
            'credit_url' => $site['credit']['url'],
            // motion
            'hero_style' => 'v2', 'hero_columns' => 4, 'hero_overlay' => 45, 'hero_arrows' => true, 'hero_dots' => true, 'hero_autoplay' => true, 'hero_interval' => 6000, 'hero_pause_on_hover' => true,
            'preloader_show' => true, 'preloader_min_ms' => 1200, 'preloader_first_visit_only' => true,
            'respect_reduced_motion' => false,
            // intro video section
            // section headings (label / heading / paragraph / buttons) above every home page section
            ...\App\Support\SiteData::sectionSettingDefaults(),
            ...\App\Support\SiteData::workSettingDefaults(),
            // the plain texts around the intro video (admin: Settings -> Home sections): title on top, the three statistics left / right / bottom
            'around_items' => array_merge(
                [['pos' => 'top', 'value' => null, 'suffix' => '', 'text' => $this->bi($intro['title'], $introEn['title'])]],
                array_map(fn ($s, $e, $pos) => ['pos' => $pos, 'value' => $s['value'], 'suffix' => $s['suffix'], 'text' => $this->bi($s['label'], $e['label'])], $this->bn['STATS'], $this->en['STATS'], ['left', 'right', 'bottom']),
            ),
            'intro_video' => $intro['video'],
            'intro_poster' => $intro['poster'],
            // theme
            'theme_default' => 'light',
            'theme_light_primary' => '#e41431', 'theme_light_bg' => '#f7f7f8', 'theme_light_surface' => '#ffffff', 'theme_light_text' => '#111113',
            'theme_dark_primary' => '#e41431', 'theme_dark_bg' => '#0c0c0e', 'theme_dark_surface' => '#161619', 'theme_dark_text' => '#f2f2f3',
            // custom code (global)
            'code_head' => '', 'code_body_start' => '', 'code_body_end' => '', 'code_css' => '', 'code_js' => '',
            // SEO (global)
            'seo_title_separator' => '|',
            'seo_title_suffix' => $this->bi('Adnan Architects & Engineers', 'Adnan Architects & Engineers'),
            'seo_default_description' => $this->bi('আর্কিটেকচারাল, স্ট্রাকচারাল ও ইলেকট্রিক্যাল ড্রয়িং, প্লাম্বিং ও ফায়ারফাইটিং, সয়েল টেস্ট, 3D ও ল্যান্ডস্কেপ ডিজাইন, ইন্টেরিয়র ডিজাইন এবং রাজউক, পৌরসভা ও উপজেলা প্ল্যান।', 'Architectural, structural and electrical drawings, plumbing & firefighting, soil test, 3D & landscape design, interior design, and RAJUK, Pourashava & Upazila plan approval.'),
            'seo_default_og_image' => 'assets/img/cover.jpg',
            'seo_twitter_handle' => '', 'seo_google_verification' => '', 'seo_bing_verification' => '',
            'seo_ga_id' => '', 'seo_gtm_id' => '', 'seo_fb_pixel_id' => '',
            'seo_sitemap_enabled' => true,
            'seo_robots_txt' => "User-agent: *\nDisallow: /admin\nAllow: /",
            'seo_org_schema' => true,
        ];

        foreach ($defaults as $key => $value) {
            if (Setting::query()->where('key', $key)->doesntExist()) {
                Setting::put($key, $value);
            }
        }
    }

    private function pages(): void
    {
        $seed = [
            'home' => ['হোম পেজ / Home', 'Adnan Architects & Engineers | আর্কিটেকচার ও ইঞ্জিনিয়ারিং সেবা', 'Adnan Architects & Engineers | Architecture & Engineering Services'],
            'portfolio' => ['আমাদের কাজ / Portfolio', 'আমাদের কাজ', 'Our Work'],
            'insights' => ['ইনসাইট / Insights', 'ইনসাইট', 'Insights'],
        ];
        foreach ($seed as $key => [$name, $bn, $en]) {
            $page = Page::firstOrCreate(['key' => $key], ['name' => $name]);
            if (! $page->seo) {
                $page->seo()->create(['meta_title' => $this->bi($bn, $en)]);
            }
        }
    }

    private function heroSlides(): void
    {
        if (HeroSlide::query()->exists()) {
            return;
        }
        foreach ($this->bn['HERO_SLIDES'] as $i => $b) {
            $e = $this->en['HERO_SLIDES'][$i];
            HeroSlide::create([
                'badge' => $this->bi($b['badge'], $e['badge']),
                'title1' => $this->bi($b['title1'], $e['title1']),
                'title2' => $this->bi($b['title2'], $e['title2']),
                'text' => $this->bi($b['text'], $e['text']),
                'image' => $b['image'],
                'image_fit' => $b['fit'] ?? 'cover',
                'image_pos' => $b['pos'] ?? null,
                'cta_label' => $this->bi($b['cta']['label'], $e['cta']['label']),
                'cta_url' => $this->link($b['cta']['href']),
                'chip_icon' => $b['chip']['icon'],
                'chip_title' => $this->bi($b['chip']['title'], $e['chip']['title']),
                'chip_sub' => $this->bi($b['chip']['sub'], $e['chip']['sub']),
                'sort' => $i + 1,
            ]);
        }
    }

    private function testimonials(): void
    {
        if (Testimonial::query()->exists()) {
            return;
        }
        foreach ($this->bn['TESTIMONIALS'] as $i => $b) {
            $e = $this->en['TESTIMONIALS'][$i];
            Testimonial::create([
                'type' => $b['type'],
                'name' => $this->bi($b['name'], $e['name']),
                'role' => $this->bi($b['role'], $e['role']),
                'text' => $this->bi($b['text'], $e['text']),
                'rating' => $b['rating'] ?? 5,
                'image' => $b['image'] ?? null,
                'video_url' => $b['video'] ?? null,
                'poster' => $b['poster'] ?? null,
                'is_sample' => ! empty($b['placeholder']),
                'sort' => $i + 1,
            ]);
        }
    }

    private function projects(): void
    {
        if (Project::query()->exists()) {
            return;
        }
        foreach ($this->bn['PROJECTS'] as $i => $b) {
            $e = $this->en['PROJECTS'][$i];
            Project::create([
                'slug' => $b['slug'],
                'title' => $this->bi($b['title'], $e['title']),
                'category' => $this->bi($b['category'], $e['category']),
                'tagline' => $this->bi($b['tagline'], $e['tagline']),
                'summary' => $this->bi($b['summary'], $e['summary']),
                'cover' => $b['cover'],
                'cover_pos' => $b['coverPos'] ?? null,
                'meta' => $this->bi($b['meta'], $e['meta']),
                'description' => $this->bi($b['description'], $e['description']),
                'highlights' => $this->bi($b['highlights'], $e['highlights']),
                'images' => array_map(fn ($img, $k) => [
                    'src' => $img['src'],
                    'title' => $this->bi($img['title'], $e['images'][$k]['title']),
                    'size' => $this->bi($img['size'], $e['images'][$k]['size']),
                    'note' => $this->bi($img['note'], $e['images'][$k]['note']),
                ], $b['images'], array_keys($b['images'])),
                'sort' => $i + 1,
            ]);
        }
    }

    private function html(array $blocks): string
    {
        $out = '';
        foreach ($blocks as $b) {
            if (isset($b['h'])) {
                $out .= '<h2>'.e($b['h'])."</h2>\n";
            } elseif (isset($b['p'])) {
                $out .= '<p>'.e($b['p'])."</p>\n";
            } elseif (isset($b['ul'])) {
                $out .= '<ul>'.implode('', array_map(fn ($li) => '<li>'.e($li).'</li>', $b['ul']))."</ul>\n";
            }
        }

        return $out;
    }

    private function insights(): void
    {
        if (Insight::query()->exists()) {
            return;
        }
        foreach ($this->bn['INSIGHTS'] as $i => $b) {
            $e = $this->en['INSIGHTS'][$i];
            Insight::create([
                'slug' => $b['slug'],
                'title' => $this->bi($b['title'], $e['title']),
                'category' => $this->bi($b['category'], $e['category']),
                'excerpt' => $this->bi($b['excerpt'], $e['excerpt']),
                'body' => $this->bi($this->html($b['body']), $this->html($e['body'])),
                'read_time' => $this->bi($b['readTime'], $e['readTime']),
                'cover' => $b['cover'],
                'cover_fit' => $b['coverFit'] ?? 'cover',
                'published_at' => $e['date'],
                'sort' => $i + 1,
            ]);
        }
    }

    /** The developer account (hidden from every other user). Created only when no developer exists yet. */
    private function admin(): void
    {
        if (User::query()->where('is_developer', true)->exists()) {
            return;
        }

        $user = User::query()->firstOrNew(['email' => 'developer@sobuj.com']);
        $user->name = 'Sobuj';
        $user->password = $user->exists ? $user->password : Hash::make(env('ADMIN_PASSWORD', 'password'));
        $user->forceFill(['is_developer' => true])->save();
    }
}
