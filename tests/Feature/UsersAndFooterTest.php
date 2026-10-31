<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\SiteData;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersAndFooterTest extends TestCase
{
    use RefreshDatabase;

    private User $developer;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->developer = User::where('email', 'developer@sobuj.com')->firstOrFail();
        $this->client = User::create(['name' => 'Client Admin', 'email' => 'client@adnan.test', 'password' => 'password123']);
    }

    public function test_the_developer_account_exists_and_cannot_be_made_by_mass_assignment(): void
    {
        $this->assertTrue($this->developer->is_developer);
        $this->assertSame('Sobuj', $this->developer->name);
        $this->assertFalse($this->client->fresh()->is_developer);

        $sneaky = User::create(['name' => 'X', 'email' => 'x@x.test', 'password' => 'password123', 'is_developer' => true]);
        $this->assertFalse($sneaky->fresh()->is_developer);
    }

    public function test_developer_can_sign_in(): void
    {
        $this->post('/admin/login', ['email' => 'developer@sobuj.com', 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->developer);
    }

    public function test_developer_is_hidden_from_everybody_elses_user_list(): void
    {
        $this->actingAs($this->client)->get('/admin/users')
            ->assertOk()->assertSee('Client Admin')->assertDontSee('developer@sobuj.com')->assertDontSee('Sobuj');

        // a direct link to the developer account does not exist for them
        $this->get("/admin/users/{$this->developer->id}/edit")->assertNotFound();
        $this->put("/admin/users/{$this->developer->id}", ['name' => 'Hacked', 'email' => 'h@h.test'])->assertNotFound();
        $this->delete("/admin/users/{$this->developer->id}")->assertNotFound();
        $this->assertSame('Sobuj', $this->developer->fresh()->name);

        // the developer sees everyone
        $this->actingAs($this->developer)->get('/admin/users')->assertOk()->assertSee('developer@sobuj.com')->assertSee('Client Admin');
    }

    public function test_user_management_create_update_delete(): void
    {
        $this->actingAs($this->client);

        $this->post('/admin/users', ['name' => 'Editor', 'email' => 'editor@adnan.test', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1'])->assertRedirect('/admin/users');
        $editor = User::where('email', 'editor@adnan.test')->firstOrFail();
        $this->assertFalse($editor->is_developer);

        $this->post('/admin/users', ['name' => 'Dup', 'email' => 'editor@adnan.test', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1'])->assertSessionHasErrors('email');
        $this->post('/admin/users', ['name' => 'Short', 'email' => 's@adnan.test', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');

        $this->put("/admin/users/{$editor->id}", ['name' => 'Editor Two', 'email' => 'editor@adnan.test', 'password' => ''])->assertRedirect('/admin/users');
        $this->assertSame('Editor Two', $editor->fresh()->name);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secret-pass-1', $editor->fresh()->password));   // empty password = unchanged

        $this->put("/admin/users/{$editor->id}", ['name' => 'Editor Two', 'email' => 'editor@adnan.test', 'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'])->assertRedirect();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('brand-new-pass', $editor->fresh()->password));

        // you can not delete yourself
        $this->delete("/admin/users/{$this->client->id}")->assertSessionHasErrors('user');
        $this->assertNotNull(User::find($this->client->id));

        $this->delete("/admin/users/{$editor->id}")->assertRedirect('/admin/users');
        $this->assertNull(User::find($editor->id));
    }

    public function test_my_profile(): void
    {
        $this->actingAs($this->client)->get('/admin/profile')->assertOk()->assertSee('My profile');
        $this->put('/admin/profile', ['name' => 'New Name', 'email' => 'client@adnan.test'])->assertRedirect();
        $this->assertSame('New Name', $this->client->fresh()->name);

        $this->put('/admin/profile', ['name' => 'N', 'email' => 'client@adnan.test', 'current_password' => 'wrong', 'password' => 'another-pass-9', 'password_confirmation' => 'another-pass-9'])
            ->assertSessionHasErrors('current_password');
        $this->put('/admin/profile', ['name' => 'N', 'email' => 'client@adnan.test', 'current_password' => 'password123', 'password' => 'another-pass-9', 'password_confirmation' => 'another-pass-9'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('another-pass-9', $this->client->fresh()->password));
    }

    public function test_footer_credit_is_read_only_for_everyone_but_the_developer(): void
    {
        $before = Setting::get('credit_name');

        // the footer form shows it locked for a normal admin
        $this->actingAs($this->client)->get('/admin/settings/footer')->assertOk()->assertDontSee('name="credit_name[bn]"', false)->assertSee('Credit – name');

        // ... and even a hand-made request can not change it
        $this->put('/admin/settings/footer', [
            'footer_copyright' => ['bn' => 'ক', 'en' => 'Copyright {year}'],
            'credit_name' => ['bn' => 'হ্যাকার', 'en' => 'Hacker'], 'credit_text' => ['bn' => 'x', 'en' => 'x'], 'credit_url' => 'https://evil.test',
        ])->assertRedirect();
        $this->assertSame($before, Setting::get('credit_name'));
        $this->assertSame('https://www.facebook.com/so8uj', Setting::get('credit_url'));
        $this->assertSame('Copyright {year}', Setting::get('footer_copyright')['en']);           // the rest of the form did save

        // the developer can edit it
        $this->actingAs($this->developer)->get('/admin/settings/footer')->assertOk()->assertSee('name="credit_name[bn]"', false);
        $this->put('/admin/settings/footer', [
            'credit_name' => ['bn' => 'সবুজ', 'en' => 'Sobuj M'], 'credit_text' => ['bn' => 'ডিজাইন', 'en' => 'Built by'], 'credit_url' => 'https://www.facebook.com/so8uj',
        ])->assertRedirect();
        $this->assertSame('Sobuj M', Setting::get('credit_name')['en']);
    }

    public function test_credit_cannot_be_switched_off_and_falls_back_when_empty(): void
    {
        $this->actingAs($this->developer)->put('/admin/settings/footer', [
            'credit_name' => ['bn' => '', 'en' => ''], 'credit_text' => ['bn' => '', 'en' => ''], 'credit_url' => '',
        ])->assertRedirect();

        $site = SiteData::payload('en')['SITE'];
        $this->assertTrue($site['credit']['show']);
        $this->assertSame('Sobuj', $site['credit']['name']);
        $this->assertSame('Design & Developed by', $site['credit']['text']);
        $this->assertSame('https://www.facebook.com/so8uj', $site['credit']['url']);
        $this->assertSame('সবুজ', SiteData::payload('bn')['SITE']['credit']['name']);
    }

    public function test_header_and_footer_settings_reach_the_site(): void
    {
        $this->actingAs($this->client)->put('/admin/settings/header', [
            'header_topbar_show' => '0', 'header_cta_show' => '1',
            'header_cta_label' => ['bn' => 'যোগাযোগ করুন', 'en' => 'Talk to us'], 'header_cta_url' => '/portfolio',
            'header_show_lang' => '1', 'header_show_theme' => '0',
            'header_menu' => [
                1 => ['label' => ['bn' => 'হোম', 'en' => 'Home'], 'url' => '/'],
                2 => ['label' => ['bn' => 'প্রজেক্ট', 'en' => 'Projects'], 'url' => '/portfolio'],
            ],
        ])->assertRedirect();

        $h = SiteData::payload('en')['SITE']['header'];
        $this->assertFalse($h['topbar']);
        $this->assertFalse($h['showTheme']);
        $this->assertSame('Talk to us', $h['ctaLabel']);
        $this->assertSame('/portfolio', $h['ctaUrl']);
        $this->assertSame([['label' => 'Home', 'href' => '/'], ['label' => 'Projects', 'href' => '/portfolio']], $h['menu']);

        $this->put('/admin/settings/footer', [
            'footer_cta_show' => '0', 'footer_show_services' => '0', 'footer_show_social' => '1', 'footer_show_links' => '1',
            'footer_cta_title' => ['bn' => 'ক', 'en' => 'Hello'], 'footer_cta_text' => ['bn' => 'খ', 'en' => 'World'],
            'footer_about' => ['bn' => 'গ', 'en' => 'About us text'], 'footer_copyright' => ['bn' => '© {year} ক', 'en' => '© {year} Adnan'],
        ])->assertRedirect();

        $f = SiteData::payload('en')['SITE']['footer'];
        $this->assertFalse($f['ctaShow']);
        $this->assertFalse($f['showServices']);
        $this->assertSame('About us text', $f['about']);
        $this->assertSame('© '.date('Y').' Adnan', $f['copyright']);

        // and the page carries it
        $this->withUnencryptedCookie('lang', 'en')->get('/')->assertOk()->assertSee('Talk to us');
    }

    public function test_sidebar_lists_header_footer_and_users(): void
    {
        $this->actingAs($this->client)->get('/admin')->assertOk()
            ->assertSee('Header &amp; Footer', false)->assertSee('User management')->assertSee('My profile');
    }
}
