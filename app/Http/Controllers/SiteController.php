<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use App\Support\SeoBuilder;
use App\Support\SiteData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SiteController extends Controller
{
    public function home()
    {
        return $this->render('home', 'home', Page::where('key', 'home')->first()?->seo, [
            'title' => Setting::tr('site_name').' | '.Setting::tr('tagline'),
        ]);
    }

    /** Our Work: all projects, or one of the two main categories (completed / running). */
    public function portfolio(?string $status = null)
    {
        $work = $status ? SiteData::work(app()->getLocale())[$status] : null;

        return $this->render('portfolio', 'portfolio', Page::where('key', 'portfolio')->first()?->seo, [
            'title' => $work['title'] ?? __('site.portfolio'), 'description' => $work['text'] ?? __('site.portfolio_desc'),
        ], ['status' => $status]);
    }

    public function project(string $slug)
    {
        $project = Project::where('slug', $slug)->where('active', true)->firstOrFail();

        return $this->render('project', 'portfolio', $project->seo, [
            'title' => $project->tr('title'), 'description' => $project->tr('summary'),
            'image' => $project->cover, 'type' => 'article',
            'schema' => [
                '@context' => 'https://schema.org', '@type' => 'CreativeWork',
                'name' => $project->tr('title'), 'description' => $project->tr('summary'),
                'image' => $project->cover ? url(SiteData::url($project->cover)) : null,
            ],
        ], ['slug' => $slug, 'project' => $project]);
    }

    public function insights()
    {
        return $this->render('insights', 'insights', Page::where('key', 'insights')->first()?->seo, [
            'title' => __('site.insights'), 'description' => __('site.insights_desc'),
        ]);
    }

    public function insight(string $slug)
    {
        $insight = Insight::where('slug', $slug)->where('active', true)->firstOrFail();

        return $this->render('insight', 'insights', $insight->seo, [
            'title' => $insight->tr('title'), 'description' => $insight->tr('excerpt'),
            'image' => $insight->cover, 'type' => 'article',
            'schema' => [
                '@context' => 'https://schema.org', '@type' => 'Article',
                'headline' => $insight->tr('title'), 'description' => $insight->tr('excerpt'),
                'image' => $insight->cover ? url(SiteData::url($insight->cover)) : null,
                'datePublished' => $insight->published_at?->toDateString(),
                'dateModified' => $insight->updated_at?->toDateString(),
                'author' => ['@type' => 'Organization', 'name' => Setting::tr('site_name')],
                'publisher' => ['@type' => 'Organization', 'name' => Setting::tr('site_name'), 'logo' => ['@type' => 'ImageObject', 'url' => url(SiteData::url(Setting::get('logo', 'assets/img/logo.png')))]],
            ],
        ], ['slug' => $slug, 'insight' => $insight]);
    }

    public function contact(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'service' => ['nullable', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        ContactMessage::create($data + ['ip' => $request->ip()]);

        return response()->json(['ok' => true]);
    }

    public function sitemap(): Response
    {
        abort_unless(Setting::get('seo_sitemap_enabled', true), 404);

        $urls = [['loc' => url('/'), 'mod' => null, 'prio' => '1.0'], ['loc' => url('/portfolio'), 'mod' => null, 'prio' => '0.8'], ['loc' => url('/portfolio/completed'), 'mod' => null, 'prio' => '0.8'], ['loc' => url('/portfolio/running'), 'mod' => null, 'prio' => '0.7'], ['loc' => url('/insights'), 'mod' => null, 'prio' => '0.8']];
        foreach (Project::where('active', true)->with('seo')->get() as $p) {
            if ($p->seo?->robots_index ?? true) {
                $urls[] = ['loc' => url('/project/'.$p->slug), 'mod' => $p->updated_at, 'prio' => '0.7'];
            }
        }
        foreach (Insight::where('active', true)->with('seo')->get() as $i) {
            if ($i->seo?->robots_index ?? true) {
                $urls[] = ['loc' => url('/insight/'.$i->slug), 'mod' => $i->updated_at, 'prio' => '0.6'];
            }
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $txt = trim((string) Setting::get('seo_robots_txt', "User-agent: *\nAllow: /"));
        if (Setting::get('seo_sitemap_enabled', true)) {
            $txt .= "\n\nSitemap: ".url('/sitemap.xml');
        }

        return response($txt."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function render(string $view, string $nav, $seoModel, array $fallback, array $extra = [])
    {
        return view($view, $extra + [
            'nav' => $nav,
            'seo' => SeoBuilder::make($seoModel, $fallback),
            'payload' => SiteData::payload(),
        ]);
    }
}
