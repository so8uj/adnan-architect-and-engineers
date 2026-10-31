<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\Project;
use App\Models\Testimonial;
use App\Support\SiteData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'counts' => [
                'Projects' => Project::count(),
                'Insights' => Insight::count(),
                'Testimonials' => Testimonial::count(),
                'New messages' => ContactMessage::where('is_read', false)->count(),
            ],
            'latest' => ContactMessage::latest()->take(5)->get(),
        ]);
    }

    /** Clears the content cache plus Laravel's config / route / view caches. */
    public function clearCache(): RedirectResponse
    {
        SiteData::flush();
        Cache::flush();
        Artisan::call('optimize:clear');

        return back()->with('status', 'Cache cleared. The website now shows the latest content.');
    }
}
