<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GoLiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
    }

    public function test_security_headers_are_sent(): void
    {
        $r = $this->get('/');
        $r->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('camera=()', $r->headers->get('Permissions-Policy'));
    }

    public function test_the_admin_is_never_cached_and_hsts_only_in_production_over_https(): void
    {
        $this->actingAs(User::first())->get('/admin')->assertHeader('Cache-Control', 'no-store, private');

        $this->get('/')->assertHeaderMissing('Strict-Transport-Security');
        $this->app['env'] = 'production';
        $this->get('https://example.test/')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_branded_error_pages_in_both_languages_without_laravel_default(): void
    {
        $bn = $this->get('/no-such-page')->assertNotFound();
        $bn->assertSee('পাতাটি খুঁজে পাওয়া যায়নি')->assertSee('হোমে ফিরুন')->assertDontSee('normalize.css');

        $this->withUnencryptedCookie('lang', 'en')->get('/no-such-page')->assertNotFound()->assertSee('Page not found')->assertSee('Back to home');
        $this->get('/project/missing-project')->assertNotFound()->assertSee('Page not found');
    }

    public function test_every_error_view_renders(): void
    {
        foreach (['403', '404', '419', '429', '500', '503'] as $code) {
            $html = view("errors.$code")->render();
            $this->assertStringContainsString($code, $html);
            $this->assertStringContainsString('Adnan Architects', $html);
        }
    }

    public function test_robots_txt_comes_from_the_admin_not_from_a_static_file(): void
    {
        // a real file in public/ would be served by the web server before Laravel and silently ignore the admin setting
        $this->assertFileDoesNotExist(public_path('robots.txt'));
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap:');
    }

    public function test_uploads_in_tests_never_touch_the_real_uploads_folder(): void
    {
        $before = glob(public_path('uploads/*')) ?: [];

        $this->actingAs(User::first())->post('/admin/c/activities', [
            'title' => ['bn' => 'ছবি', 'en' => 'Photo'], 'image' => UploadedFile::fake()->image('a.jpg'), 'active' => '1',
        ])->assertRedirect();

        $this->assertSame($before, glob(public_path('uploads/*')) ?: []);
        $this->assertNotEmpty(glob(public_path('uploads_test/*')));
    }

    public function test_uploads_folder_blocks_script_execution_on_apache(): void
    {
        $this->assertFileExists(public_path('uploads/.htaccess'));
        $this->assertStringContainsString('Require all denied', file_get_contents(public_path('uploads/.htaccess')));
    }

    public function test_only_image_files_can_be_uploaded(): void
    {
        $this->actingAs(User::first());
        foreach (['shell.php', 'page.html', 'x.svg', 'doc.pdf'] as $name) {
            $this->post('/admin/c/activities', ['title' => ['bn' => 'x', 'en' => 'x'], 'image' => UploadedFile::fake()->create($name, 10)])
                ->assertSessionHasErrors('image');
        }
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/admin/login', ['email' => 'nobody@x.test', 'password' => 'wrong']);
        }
        $this->post('/admin/login', ['email' => 'nobody@x.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_the_contact_form_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/contact', ['name' => 'A', 'phone' => '1'])->assertOk();
        }
        $this->postJson('/contact', ['name' => 'A', 'phone' => '1'])->assertStatus(429);
    }

    public function test_admin_and_user_data_are_protected_from_guests(): void
    {
        foreach (['/admin', '/admin/users', '/admin/messages', '/admin/settings/footer', '/admin/c/projects', '/admin/profile'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
        $this->post('/admin/cache/clear')->assertRedirect('/admin/login');
        $this->post('/admin/c/services/reorder', ['ids' => [1]])->assertRedirect('/admin/login');
    }

    public function test_pages_do_not_leak_the_framework_or_secrets(): void
    {
        $html = $this->get('/')->getContent();
        foreach (['APP_KEY', 'DB_PASSWORD', 'base64:', 'phpinfo', 'Whoops', 'Stack trace'] as $needle) {
            $this->assertStringNotContainsString($needle, $html, $needle);
        }
    }
}
