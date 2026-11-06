<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSliderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->actingAs(User::where('is_developer', true)->firstOrFail());
    }

    public function test_slider_options_are_saved_from_the_slides_page(): void
    {
        $this->put('/admin/c/hero-slides/config', [
            'hero_style' => 'v1', 'hero_autoplay' => 1, 'hero_interval' => 4000, 'hero_columns' => 6,
            'hero_overlay' => 30, 'hero_arrows' => 1, 'hero_dots' => 0,
        ])->assertRedirect('/admin/c/hero-slides');

        $this->assertSame('v1', Setting::get('hero_style'));
        $this->assertSame(4000, (int) Setting::get('hero_interval'));
        $this->assertSame('v1', \App\Support\SiteData::payload('bn')['HERO_CONFIG']['style']);
    }

    public function test_texts_around_the_video_are_configurable(): void
    {
        $this->get('/admin/settings/home')->assertOk()->assertSee('Texts around the intro video');

        $this->put('/admin/settings/home', [
            'around_items' => [
                1 => ['pos' => 'left', 'value' => '12', 'suffix' => '+', 'text' => ['bn' => 'বছর', 'en' => 'Years']],
                2 => ['pos' => 'top', 'value' => '', 'suffix' => '', 'text' => ['bn' => 'শিরোনাম', 'en' => 'Heading']],
                3 => ['pos' => 'left', 'value' => '99', 'suffix' => '', 'text' => ['bn' => 'ডুপ্লিকেট', 'en' => 'Duplicate']],
                4 => ['pos' => 'oops', 'value' => '1', 'suffix' => '', 'text' => ['bn' => 'ভুল', 'en' => 'Bad']],
            ],
        ])->assertRedirect();

        $items = \App\Support\SiteData::payload('en')['AROUND'];
        $this->assertSame(['top', 'left'], array_column($items, 'pos'));   // fixed order, one per side, bad side dropped
        $this->assertNull($items[0]['value']);
        $this->assertSame(['value' => 12, 'suffix' => '+', 'text' => 'Years'], array_intersect_key($items[1], array_flip(['value', 'suffix', 'text'])));
    }

    public function test_section_headings_and_buttons_are_configurable(): void
    {
        $this->get('/admin/settings/sections')->assertOk()->assertSee('Section headings')->assertSee('Button link');

        $this->put('/admin/settings/sections', [
            'sec_services_title' => ['bn' => 'নতুন শিরোনাম', 'en' => 'New heading'],
            'sec_services_text' => ['bn' => '', 'en' => ''],
            'sec_portfolio_btn_show' => 0,
            'sec_insights_btn_label' => ['bn' => 'সব লেখা', 'en' => 'All posts'],
            'sec_insights_btn_url' => '/insights',
            'sec_insights_btn_show' => 1,
        ])->assertRedirect();

        $s = \App\Support\SiteData::payload('en')['SECTIONS'];
        $this->assertSame('New heading', $s['services']['title']);
        $this->assertSame('', $s['services']['text']);                      // emptied = hidden on the page
        $this->assertFalse($s['portfolio']['btn']['show']);
        $this->assertSame('All posts', $s['insights']['btn']['label']);
    }

    public function test_slide_form_follows_the_chosen_style(): void
    {
        Setting::put('hero_style', 'v1');
        $this->get('/admin/c/hero-slides/create')->assertSee('Background image (full page)')->assertDontSee('Floating card');

        Setting::put('hero_style', 'v2');
        $this->get('/admin/c/hero-slides/create')->assertSee('Image (right side)')->assertSee('Floating card');
    }
}
