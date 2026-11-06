<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Setting;
use App\Models\Role;
use App\Models\User;
use App\Support\SiteData;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FutureFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $developer;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->developer = User::where('is_developer', true)->firstOrFail();
        $this->client = User::create(['name' => 'Client Admin', 'email' => 'client@adnan.test', 'password' => 'password123']);
        $this->client->forceFill(['role_id' => Role::superAdmin()->id])->save();   // a Super Admin, like every admin before roles existed
    }

    private function switchOn(): void
    {
        config(['features.enabled' => true, 'features.modules.clients' => true]);
        SiteData::flush();
    }

    private function makeClient(array $over = []): Client
    {
        return Client::create($over + [
            'name' => ['bn' => 'ক্লায়েন্ট', 'en' => 'Acme Ltd'], 'phone' => '01711111111', 'email' => 'secret@acme.test',
            'address' => 'Private street 5', 'notes' => 'Pays late', 'contact_person' => 'Mr Private',
            'show_on_home' => true, 'sort' => 1, 'active' => true,
        ]);
    }

    public function test_everything_is_off_by_default(): void
    {
        $this->assertFalse((bool) config('features.enabled'));
        $this->makeClient();

        // not even the developer can open it, and it is not in the menu
        $this->actingAs($this->developer);
        $this->get('/admin/c/clients')->assertNotFound();
        $this->get('/admin/c/clients/create')->assertNotFound();
        $this->get('/admin/settings/clients')->assertNotFound();
        $this->get('/admin')->assertOk()->assertDontSee('Client list');

        // nothing on the website: no data, no section
        $payload = SiteData::payload('en');
        $this->assertSame([], $payload['CLIENTS']);
        $this->assertNull($payload['CLIENTS_SECTION']);
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('id="clients"', $html);
        $this->assertStringNotContainsString('Acme Ltd', $html);
    }

    public function test_master_switch_on_but_the_module_switch_off_stays_hidden(): void
    {
        config(['features.enabled' => true, 'features.modules.clients' => false]);
        SiteData::flush();

        $this->actingAs($this->developer)->get('/admin/c/clients')->assertNotFound();
        $this->assertStringNotContainsString('id="clients"', $this->get('/')->getContent());
    }

    public function test_when_on_every_admin_sees_the_admin_screens(): void
    {
        $this->switchOn();

        foreach ([$this->client, $this->developer] as $admin) {
            $this->actingAs($admin);
            $this->get('/admin/c/clients')->assertOk()->assertSee('Search clients');
            $this->get('/admin/settings/clients')->assertOk();
            $this->get('/admin')->assertSee('Client list');
        }
    }

    public function test_nobody_is_let_in_without_logging_in(): void
    {
        $this->switchOn();
        $this->get('/admin/c/clients')->assertRedirect('/admin/login');
    }

    public function test_homepage_shows_only_marked_clients_in_order_and_never_private_data(): void
    {
        $this->makeClient(['name' => ['bn' => 'এক', 'en' => 'Third'], 'sort' => 3]);
        $this->makeClient(['name' => ['bn' => 'দুই', 'en' => 'First'], 'sort' => 1, 'website' => 'https://first.test']);
        $this->makeClient(['name' => ['bn' => 'তিন', 'en' => 'Second'], 'sort' => 2]);
        $this->makeClient(['name' => ['bn' => 'চার', 'en' => 'Not on home'], 'sort' => 4, 'show_on_home' => false]);
        $this->makeClient(['name' => ['bn' => 'পাঁচ', 'en' => 'Inactive'], 'sort' => 5, 'active' => false]);
        $this->switchOn();

        $payload = SiteData::payload('en');
        $this->assertSame(['First', 'Second', 'Third'], array_column($payload['CLIENTS'], 'name'));
        $this->assertSame('https://first.test', $payload['CLIENTS'][0]['url']);
        $this->assertSame(['name', 'logo', 'url'], array_keys($payload['CLIENTS'][0]));        // nothing else leaves the server

        $html = $this->withUnencryptedCookie('lang', 'en')->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('id="clients"', $html);
        $this->assertStringContainsString('First', $html);
        foreach (['secret@acme.test', '01711111111', 'Private street 5', 'Pays late', 'Mr Private', 'Not on home', 'Inactive'] as $private) {
            $this->assertStringNotContainsString($private, $html, $private);
        }
    }

    public function test_the_homepage_limit_setting_is_respected(): void
    {
        foreach (range(1, 15) as $i) {
            $this->makeClient(['name' => ['bn' => "ক্লায়েন্ট $i", 'en' => "Client $i"], 'sort' => $i]);
        }
        $this->switchOn();

        $this->assertCount(10, SiteData::payload('en')['CLIENTS']);                          // default 10

        $this->actingAs($this->developer)->put('/admin/settings/clients', [
            'clients_title' => ['bn' => 'আমাদের গ্রাহক', 'en' => 'Our Customers'], 'clients_subtitle' => ['bn' => '', 'en' => ''], 'clients_home_limit' => '5',
        ])->assertRedirect();
        $this->assertCount(5, SiteData::payload('en')['CLIENTS']);
        $this->assertSame('Our Customers', SiteData::payload('en')['CLIENTS_SECTION']['title']);

        Setting::put('clients_home_limit', 99);                                              // the cap is 24
        SiteData::flush();
        $this->assertCount(15, SiteData::payload('en')['CLIENTS']);
    }

    public function test_client_crud_search_and_reorder_for_the_developer(): void
    {
        $this->switchOn();
        $this->actingAs($this->developer);

        $this->post('/admin/c/clients', [
            'name' => ['bn' => 'নতুন', 'en' => 'New Client'], 'logo' => UploadedFile::fake()->image('logo.png', 200, 100),
            'website' => 'https://newclient.test', 'show_on_home' => '1', 'phone' => '01800000000', 'email' => 'n@n.test', 'address' => 'Somewhere', 'notes' => 'VIP',
            'sort' => '', 'active' => '1',
        ])->assertRedirect();

        $c = Client::where('phone', '01800000000')->firstOrFail();
        $this->assertTrue($c->show_on_home);
        $this->assertFileExists(public_path($c->logo));
        $this->assertSame('New Client', SiteData::payload('en')['CLIENTS'][0]['name']);

        $this->get('/admin/c/clients')->assertSee('New Client')->assertSee('01800000000');

        // turn it off the homepage; it stays in the list
        $this->put("/admin/c/clients/{$c->id}", [
            'name' => ['bn' => 'নতুন', 'en' => 'New Client'], 'show_on_home' => '0', 'active' => '1', 'sort' => '1',
        ])->assertRedirect();
        $this->assertSame([], SiteData::payload('en')['CLIENTS']);
        $this->assertNotNull(Client::find($c->id));

        // drag and drop order
        $a = $this->makeClient(['sort' => 1]);
        $b = $this->makeClient(['sort' => 2]);
        $this->postJson('/admin/c/clients/reorder', ['ids' => [$b->id, $a->id, $c->id]])->assertOk();
        $this->assertSame([$b->id, $a->id, $c->id], Client::orderBy('sort')->pluck('id')->all());

        @unlink(public_path($c->logo));
        $this->delete("/admin/c/clients/{$c->id}")->assertRedirect();
        $this->assertNull(Client::find($c->id));
    }
}
