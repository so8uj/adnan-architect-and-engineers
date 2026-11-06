<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
    }

    private function admin(): static
    {
        return $this->actingAs(User::first());
    }

    public function test_public_pages_render_in_both_languages(): void
    {
        foreach (['/', '/portfolio', '/insights', '/project/residential-2d-floor-plans', '/insight/why-soil-test-matters'] as $url) {
            $this->get($url)->assertOk()->assertSee('<title>', false);
            $this->withCookie('lang', 'en')->get($url)->assertOk();
        }
        $this->get('/project/nope')->assertNotFound();
    }

    public function test_english_cookie_switches_content_and_seo(): void
    {
        $this->withUnencryptedCookie('lang', 'en')->get('/insight/why-soil-test-matters')
            ->assertSee('Why a soil test matters before foundation design', false)
            ->assertSee('lang="en"', false)
            ->assertSee('property="og:locale" content="en_US"', false);
    }

    public function test_seo_tags_are_in_the_head(): void
    {
        $this->get('/')
            ->assertSee('<meta name="robots" content="index,follow">', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('property="og:title"', false);
    }

    public function test_sitemap_and_robots(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee('/project/residential-2d-floor-plans', false)->assertSee('/insight/why-soil-test-matters', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap:');
    }

    public function test_contact_form_saves_a_message(): void
    {
        $this->postJson('/contact', ['name' => 'Rahim', 'phone' => '01700000000', 'service' => 'Soil test', 'message' => 'Hello'])->assertOk()->assertJson(['ok' => true]);
        $this->assertSame(1, ContactMessage::count());
        $this->postJson('/contact', ['name' => ''])->assertStatus(422);
    }

    public function test_admin_requires_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->post('/admin/login', ['email' => 'admin@adnan.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => 'developer@sobuj.com', 'password' => 'password'])->assertRedirect('/admin');
    }

    public function test_every_admin_screen_loads(): void
    {
        $this->admin();
        $this->get('/admin')->assertOk()->assertSee('View website')->assertSee('Clear cache');
        $this->get('/admin/messages')->assertOk();
        $this->get('/admin/pages')->assertOk();
        foreach (Page::all() as $page) {
            $this->get('/admin/pages/'.$page->id)->assertOk()->assertSee('Custom code');
        }
        foreach (config('admin.settings') as $group => $def) {
            if (! empty($def['feature'])) {
                $this->get("/admin/settings/$group")->assertNotFound();                  // future feature: off by default
                continue;
            }
            $this->get("/admin/settings/$group")->assertOk();
        }
        foreach (config('admin.resources') as $key => $def) {
            if (! empty($def['feature'])) {
                $this->get("/admin/c/$key")->assertNotFound();
                continue;
            }
            $this->get("/admin/c/$key")->assertOk();
            $this->get("/admin/c/$key/create")->assertOk();
            $first = $def['model']::first();
            if ($first) {
                $this->get("/admin/c/$key/{$first->id}/edit")->assertOk();
            }
        }
    }

    public function test_theme_settings_change_the_css_variables_and_default_theme(): void
    {
        $this->admin()->put('/admin/settings/theme', [
            'theme_default' => 'dark',
            'theme_light_primary' => '#0055ff', 'theme_light_bg' => '#f8f8f8', 'theme_light_surface' => '#ffffff', 'theme_light_text' => '#111111',
            'theme_dark_primary' => '#00ccaa', 'theme_dark_bg' => '#000000', 'theme_dark_surface' => '#101010', 'theme_dark_text' => '#eeeeee',
        ])->assertRedirect();

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('--brand-600:0 85 255', $html);          // light primary
        $this->assertStringContainsString('--brand-600:0 204 170', $html);         // dark primary
        $this->assertStringContainsString('window.DEFAULT_THEME = "dark"', $html);
    }

    public function test_custom_code_is_injected_in_the_right_places(): void
    {
        $this->admin()->put('/admin/settings/code', [
            'code_head' => '<meta name="x-test" content="head">',
            'code_css' => '.x{color:red}',
            'code_body_start' => '<!-- body-start -->',
            'code_body_end' => '<!-- body-end -->',
            'code_js' => 'window.xTest=1;',
        ])->assertRedirect();

        $html = $this->get('/')->getContent();
        $this->assertGreaterThan(strpos($html, '<head>'), strpos($html, 'name="x-test"'));
        $this->assertLessThan(strpos($html, '</head>'), strpos($html, 'name="x-test"'));
        $this->assertStringContainsString('<style id="custom-css">.x{color:red}</style>', $html);
        $this->assertGreaterThan(strpos($html, '<body'), strpos($html, '<!-- body-start -->'));
        $this->assertGreaterThan(strpos($html, '<!-- body-start -->'), strpos($html, '<!-- body-end -->'));
        $this->assertLessThan(strpos($html, '</body>'), strpos($html, 'window.xTest=1;'));
    }

    public function test_page_seo_and_page_level_code(): void
    {
        $page = Page::where('key', 'portfolio')->first();
        $this->admin()->put("/admin/pages/{$page->id}", ['seo' => [
            'meta_title' => ['bn' => 'আমাদের কাজ - টেস্ট', 'en' => 'My Portfolio'],
            'meta_description' => ['bn' => 'বিবরণ', 'en' => 'A custom description'],
            'meta_keywords' => ['bn' => '', 'en' => 'architect, dhaka'],
            'canonical_url' => 'https://example.com/portfolio',
            'robots_index' => '0', 'robots_follow' => '1', 'robots_extra' => 'noarchive',
            'og_title' => ['bn' => '', 'en' => ''], 'og_description' => ['bn' => '', 'en' => ''],
            'og_type' => 'website', 'twitter_card' => 'summary',
            'schema_json' => '{"@context":"https://schema.org","@type":"WebPage","name":"x"}',
            'head_code' => '<meta name="page-only" content="1">',
            'custom_css' => '.page-only{display:none}', 'custom_js' => 'window.pageOnly=1;',
            'body_start_code' => '', 'body_end_code' => '',
        ]])->assertRedirect();

        $en = $this->withUnencryptedCookie('lang', 'en')->get('/portfolio')->getContent();
        $this->assertStringContainsString('<title>My Portfolio | Adnan Architects &amp; Engineers</title>', $en);
        $this->assertStringContainsString('content="A custom description"', $en);
        $this->assertStringContainsString('content="architect, dhaka"', $en);
        $this->assertStringContainsString('<meta name="robots" content="noindex,follow,noarchive">', $en);
        $this->assertStringContainsString('href="https://example.com/portfolio"', $en);
        $this->assertStringContainsString('"@type":"WebPage"', $en);
        $this->assertStringContainsString('name="page-only"', $en);
        $this->assertStringContainsString('window.pageOnly=1;', $en);
        // other pages are untouched
        $this->assertStringNotContainsString('page-only', $this->get('/')->getContent());
    }

    public function test_global_seo_analytics_and_verification(): void
    {
        $this->admin()->put('/admin/settings/seo', [
            'seo_title_suffix' => ['bn' => 'আদনান', 'en' => 'Adnan'], 'seo_title_separator' => '–',
            'seo_default_description' => ['bn' => 'ডিফল্ট', 'en' => 'Default desc'],
            'seo_twitter_handle' => '@adnan', 'seo_google_verification' => 'g-code', 'seo_bing_verification' => 'b-code',
            'seo_ga_id' => 'G-TEST123', 'seo_gtm_id' => 'GTM-TEST1', 'seo_fb_pixel_id' => '12345',
            'seo_org_schema' => '1', 'seo_sitemap_enabled' => '0', 'seo_robots_txt' => "User-agent: *\nDisallow: /secret",
        ])->assertRedirect();

        $html = $this->get('/portfolio')->getContent();
        foreach (['G-TEST123', 'GTM-TEST1', "fbq('init','12345')", 'name="google-site-verification" content="g-code"', 'name="msvalidate.01" content="b-code"', 'name="twitter:site" content="@adnan"', '– আদনান</title>'] as $needle) {
            $this->assertStringContainsString($needle, $html, $needle);
        }
        $this->get('/sitemap.xml')->assertNotFound();
        $this->get('/robots.txt')->assertSee('Disallow: /secret');
    }

    public function test_content_crud_for_projects_and_insights_with_seo(): void
    {
        $this->admin();
        $this->post('/admin/c/insights', [
            'title' => ['bn' => 'নতুন লেখা', 'en' => 'A New Article'], 'slug' => '',
            'category' => ['bn' => 'ডিজাইন', 'en' => 'Design'], 'excerpt' => ['bn' => 'সারাংশ', 'en' => 'Summary'],
            'body' => ['bn' => '<p>বাংলা</p>', 'en' => '<h2>Heading</h2><p>English body</p>'],
            'read_time' => ['bn' => '২ মিনিট', 'en' => '2 min'], 'cover_fit' => 'cover', 'published_at' => '2026-11-01', 'sort' => '9', 'active' => '1',
            'seo' => ['meta_title' => ['bn' => '', 'en' => 'Custom SEO title'], 'robots_index' => '1', 'robots_follow' => '1', 'og_type' => 'article', 'twitter_card' => 'summary_large_image'],
        ])->assertRedirect();

        $insight = Insight::where('slug', 'a-new-article')->firstOrFail();
        $this->assertSame('Custom SEO title', $insight->seo->meta_title['en']);

        $en = $this->withUnencryptedCookie('lang', 'en')->get('/insight/a-new-article')->getContent();
        $this->assertStringContainsString('<h2>Heading</h2>', $en);
        $this->assertStringContainsString('Custom SEO title', $en);
        $this->assertStringContainsString('"@type":"Article"', $en);

        $this->put("/admin/c/insights/{$insight->id}", [
            'title' => ['bn' => 'নতুন লেখা', 'en' => 'A New Article'], 'slug' => 'a-new-article', 'sort' => '9', 'active' => '0',
            'cover_fit' => 'cover', 'seo' => ['robots_index' => '1', 'robots_follow' => '1', 'og_type' => 'article', 'twitter_card' => 'summary'],
        ])->assertRedirect();
        $this->get('/insight/a-new-article')->assertNotFound();             // hidden now
        $this->delete("/admin/c/insights/{$insight->id}")->assertRedirect();
        $this->assertNull(Insight::find($insight->id));

        $this->post('/admin/c/projects', [
            'title' => ['bn' => 'প্রজেক্ট', 'en' => 'Test Project'], 'slug' => 'test-project',
            'category' => ['bn' => 'ক', 'en' => 'Cat'], 'summary' => ['bn' => 'সারাংশ', 'en' => 'Sum'],
            'cover' => UploadedFile::fake()->image('cover.jpg', 800, 600),
            'meta' => ['bn' => "সেবা | ড্রইং", 'en' => "Service | Drawing\nType | House"],
            'description' => ['bn' => "প্যারা ১\nপ্যারা ২", 'en' => "Para 1\nPara 2"], 'highlights' => ['bn' => '', 'en' => "One\nTwo"],
            'images' => [1 => ['file' => UploadedFile::fake()->image('plan.png', 400, 300), 'title' => ['bn' => 'প্ল্যান', 'en' => 'Plan'], 'size' => ['bn' => '', 'en' => '10x10'], 'note' => ['bn' => '', 'en' => 'Note']]],
            'sort' => '5', 'active' => '1', 'seo' => ['robots_index' => '1', 'robots_follow' => '1', 'og_type' => 'website', 'twitter_card' => 'summary_large_image'],
        ])->assertRedirect();

        $project = Project::where('slug', 'test-project')->firstOrFail();
        $this->assertFileExists(public_path($project->cover));
        $this->assertCount(1, $project->images);
        $this->assertSame([['label' => 'Service', 'value' => 'Drawing'], ['label' => 'Type', 'value' => 'House']], $project->meta['en']);

        $page = $this->withUnencryptedCookie('lang', 'en')->get('/project/test-project');
        $page->assertOk()->assertSee('Test Project')->assertSee('Para 2')->assertSee('10x10');

        @unlink(public_path($project->cover));
        @unlink(public_path($project->images[0]['src']));
        $project->delete();
    }

    public function test_generic_resources_can_be_created(): void
    {
        $this->admin();
        $samples = [
            'services' => ['title' => ['bn' => 'সেবা', 'en' => 'Svc'], 'description' => ['bn' => '', 'en' => ''], 'icon' => 'cube', 'sort' => '', 'active' => '1'],
            'steps' => ['title' => ['bn' => 'ধাপ', 'en' => 'Step'], 'description' => ['bn' => '', 'en' => ''], 'active' => '1'],
            'why-us' => ['title' => ['bn' => 'কেন', 'en' => 'Why'], 'description' => ['bn' => '', 'en' => ''], 'active' => '1'],
            'faqs' => ['question' => ['bn' => 'প্রশ্ন', 'en' => 'Q'], 'answer' => ['bn' => 'উত্তর', 'en' => 'A'], 'active' => '1'],
            'hero-slides' => ['title1' => ['bn' => 'এক', 'en' => 'One'], 'image_fit' => 'cover', 'cta_url' => '/portfolio', 'active' => '1'],
            'testimonials' => ['type' => 'text', 'name' => ['bn' => 'নাম', 'en' => 'Name'], 'text' => ['bn' => 'ক', 'en' => 'x'], 'rating' => '4', 'is_sample' => '1', 'active' => '1'],
            'activities' => ['title' => ['bn' => 'ছবি', 'en' => 'Photo'], 'image' => UploadedFile::fake()->image('a.jpg'), 'active' => '1'],
        ];
        foreach ($samples as $resource => $payload) {
            $this->post("/admin/c/$resource", $payload)->assertRedirect();
            $model = config("admin.resources.$resource.model");
            $this->assertGreaterThan(0, $model::count(), $resource);
        }
        // the new service is visible to the website
        $this->assertStringContainsString('Svc', $this->withUnencryptedCookie('lang', 'en')->get('/')->getContent());
    }

    public function test_validation_blocks_bad_input(): void
    {
        $this->admin();
        $this->post('/admin/c/services', ['title' => ['bn' => '', 'en' => '']])->assertSessionHasErrors('title.bn');
        $this->put('/admin/settings/theme', ['theme_light_primary' => 'red'])->assertSessionHasErrors('theme_light_primary');
        $this->put('/admin/settings/general', ['site_name' => ['bn' => 'x'], 'logo' => UploadedFile::fake()->create('evil.php', 10)])->assertSessionHasErrors('logo');
    }

    public function test_clear_cache_makes_changes_visible(): void
    {
        $this->get('/'); // warms the cache
        Setting::put('greeting', 'Marker-Hello-123');               // changed behind the cache's back
        $this->assertStringNotContainsString('Marker-Hello-123', $this->get('/')->getContent());
        $this->admin()->post('/admin/cache/clear')->assertRedirect();
        $this->assertStringContainsString('Marker-Hello-123', $this->get('/')->getContent());
    }

    public function test_messages_screen_and_actions(): void
    {
        $m = ContactMessage::create(['name' => 'A', 'phone' => '1', 'message' => 'hi']);
        $this->admin()->get('/admin/messages')->assertOk()->assertSee('hi');
        $this->post("/admin/messages/{$m->id}/read")->assertRedirect();
        $this->assertTrue($m->fresh()->is_read);
        $this->delete("/admin/messages/{$m->id}")->assertRedirect();
        $this->assertNull(ContactMessage::find($m->id));
    }

    public function test_sidebar_has_website_management_menu_and_home_tabs(): void
    {
        $html = $this->admin()->get('/admin')->getContent();
        foreach (['Website Management', 'Home Page', 'Our Work', 'Insights', 'Header', 'Footer', 'Global settings', 'Administration'] as $label) {
            $this->assertStringContainsString($label, $html, $label);
        }
        // a Home Page screen shows the breadcrumb and one tab per section; the group opens by itself
        $services = $this->get('/admin/c/services')->getContent();
        $this->assertMatchesRegularExpression('/x-data="\{ open: true \}"[\s\S]*?Home Page/', $services);
        foreach (['Hero slider', 'Section headings', 'Intro video', 'Services', 'Process', 'Why us', 'Testimonials', 'Activities', 'FAQs', 'SEO &amp; code'] as $tab) {
            $this->assertStringContainsString('>'.$tab.'</a>', $services, $tab);
        }
        $this->assertStringContainsString('aria-current="page"', $services);
        // other pages carry their own tab strip; the Clients tab stays hidden while the module is off
        $this->get('/admin/c/projects')->assertSee('>Projects</a>', false);
        $this->assertStringNotContainsString('Clients strip', $services);
    }

    public function test_drag_and_drop_order_is_saved_and_shown_on_the_site(): void
    {
        $this->admin();
        $ids = \App\Models\Service::orderBy('sort')->pluck('id')->all();
        $reversed = array_reverse($ids);

        $this->postJson('/admin/c/services/reorder', ['ids' => $reversed])->assertOk()->assertJson(['ok' => true]);

        $this->assertSame($reversed, \App\Models\Service::orderBy('sort')->pluck('id')->all());
        $this->assertSame(1, \App\Models\Service::find($reversed[0])->sort);
        // the website shows the new order (first service is now the former last one)
        $first = \App\Models\Service::find($reversed[0])->tr('title', 'en');
        $payload = \App\Support\SiteData::payload('en');
        $this->assertSame($first, $payload['SERVICES'][0]['title']);

        $this->postJson('/admin/c/services/reorder', ['ids' => 'nope'])->assertStatus(422);
        $this->postJson('/admin/c/nothing/reorder', ['ids' => [1]])->assertNotFound();
    }
}
