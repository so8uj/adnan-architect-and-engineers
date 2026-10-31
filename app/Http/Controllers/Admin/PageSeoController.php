<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\FieldProcessor;
use App\Support\SiteData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** SEO + custom code for the fixed pages (home, portfolio list, insights list). */
class PageSeoController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::with('seo')->orderBy('id')->get()]);
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', ['page' => $page, 'seo' => $page->seoOrNew()]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $fields = config('admin.seo_fields');
        $request->validate(FieldProcessor::rules($fields, 'seo.'));

        $seo = $page->seoOrNew();
        $data = FieldProcessor::collect($fields, (array) $request->input('seo', []), (array) $request->file('seo', []), $seo->getAttributes());
        $page->seo()->updateOrCreate([], $data);
        SiteData::flush();

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Saved.');
    }
}
