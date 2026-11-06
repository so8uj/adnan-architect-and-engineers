<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Support\SiteData;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkCategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        SiteData::flush();
    }

    private function make(string $slug, string $status, ?int $progress = null): Project
    {
        return Project::create([
            'slug' => $slug, 'status' => $status, 'progress' => $progress,
            'title' => ['bn' => "প্রজেক্ট $slug", 'en' => "Project $slug"], 'category' => ['bn' => 'ক', 'en' => 'A'],
            'summary' => ['bn' => 'সারসংক্ষেপ', 'en' => 'Summary'], 'cover' => 'assets/img/logo.png', 'images' => [],
            'sort' => 99, 'active' => true,
        ]);
    }

    public function test_category_pages_and_counts(): void
    {
        $this->make('done-1', 'completed');
        $this->make('going-1', 'running', 40);
        SiteData::flush();

        $this->get('/portfolio')->assertOk();
        $this->get('/portfolio/completed')->assertOk()->assertSee('PORTFOLIO_STATUS = "completed"', false);
        $this->get('/portfolio/running')->assertOk()->assertSee('PORTFOLIO_STATUS = "running"', false);
        $this->get('/portfolio/finished')->assertNotFound();

        $work = SiteData::work('en');
        $this->assertSame('Running Projects', $work['running']['label']);
        $this->assertSame('/portfolio/running', $work['running']['url']);
        $this->assertSame(1, $work['running']['count']);

        $projects = SiteData::payload('en')['PROJECTS'];
        $running = collect($projects)->firstWhere('slug', 'going-1');
        $this->assertSame('running', $running['status']);
        $this->assertSame(40, $running['progress']);
    }

    public function test_sitemap_lists_the_category_pages_and_admin_can_set_the_status(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee('/portfolio/running')->assertSee('/portfolio/completed');

        $this->actingAs(User::where('is_developer', true)->firstOrFail());
        $this->get('/admin/c/projects/create')->assertOk()->assertSee('Running project (work in progress)');
    }
}
