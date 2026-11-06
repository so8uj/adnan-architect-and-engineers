<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\FieldProcessor;
use App\Support\Features;
use App\Support\SiteData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** One controller for every content list (services, projects, insights ...). What each list contains is described in config/admin.php. */
class ContentController extends Controller
{
    private function def(string $resource): array
    {
        $all = config('admin.resources');
        abort_unless(isset($all[$resource]), 404);
        // future features answer "not found" unless they are switched on AND the user may see them
        abort_if(! empty($all[$resource]['feature']) && ! Features::adminCan($all[$resource]['feature']), 404);

        $def = $all[$resource] + ['slug' => $resource];

        // lists with a "style_key" (hero slider v1 / v2) only show the fields that belong to the chosen style
        if (! empty($def['style_key'])) {
            $style = Setting::get($def['style_key'], 'v2') === 'v1' ? 'v1' : 'v2';
            $def['style'] = $style;
            $def['fields'] = $this->forStyle($def['fields'], $style);
        }

        return $def;
    }

    private function forStyle(array $fields, string $style): array
    {
        $out = [];
        foreach ($fields as $f) {
            if (! empty($f['styles']) && ! in_array($style, $f['styles'], true)) {
                continue;
            }
            $f['label'] = $f['labels'][$style] ?? $f['label'];
            $f['help'] = $f['helps'][$style] ?? ($f['help'] ?? null);
            $out[] = $f;
        }

        return $out;
    }

    /** Saves the slider-wide options shown above a list (config/admin.php -> "config"). */
    public function saveConfig(Request $request, string $resource): RedirectResponse
    {
        $all = config('admin.resources');
        abort_unless(! empty($all[$resource]['config']), 404);
        $fields = $all[$resource]['config'];
        $request->validate(FieldProcessor::rules($fields));

        $current = [];
        foreach ($fields as $f) {
            $current[$f['key']] = Setting::get($f['key']);
        }
        foreach (FieldProcessor::collect($fields, $request->input(), $request->allFiles(), $current) as $key => $value) {
            Setting::put($key, $value ?? '');
        }
        SiteData::flush();

        return redirect()->route('admin.content.index', $resource)->with('status', 'Slider settings saved.');
    }

    public function index(string $resource)
    {
        $def = $this->def($resource);
        $items = $def['model']::query()->orderBy('sort')->orderBy('id')->get();

        $config = [];
        foreach ($def['config'] ?? [] as $f) {
            $config[$f['key']] = Setting::get($f['key']);
        }

        return view('admin.content.index', compact('def', 'items', 'config'));
    }

    public function create(string $resource)
    {
        $def = $this->def($resource);
        $item = new $def['model'](['active' => true, 'sort' => ($def['model']::max('sort') ?? 0) + 1]);

        return view('admin.content.form', ['def' => $def, 'item' => $item, 'seo' => in_array('seo', array_keys($def)) ? (new $def['model'])->seoOrNew() : null]);
    }

    public function edit(string $resource, int $id)
    {
        $def = $this->def($resource);
        $item = $def['model']::findOrFail($id);

        return view('admin.content.form', ['def' => $def, 'item' => $item, 'seo' => ! empty($def['seo']) ? $item->seoOrNew() : null]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $def = $this->def($resource);
        $item = new $def['model'];

        return $this->save($request, $def, $item);
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $def = $this->def($resource);

        return $this->save($request, $def, $def['model']::findOrFail($id));
    }

    /** Drag & drop sorting: receives the ids in their new order and stores 1..n in the "sort" column. */
    public function reorder(Request $request, string $resource): \Illuminate\Http\JsonResponse
    {
        $def = $this->def($resource);
        $ids = $request->validate(['ids' => ['required', 'array', 'max:500'], 'ids.*' => ['integer']])['ids'];

        foreach (array_values($ids) as $position => $id) {
            $def['model']::whereKey($id)->update(['sort' => $position + 1]);
        }
        SiteData::flush();

        return response()->json(['ok' => true]);
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $def = $this->def($resource);
        $item = $def['model']::findOrFail($id);
        if (! empty($def['seo'])) {
            $item->seo()?->delete();
        }
        $item->delete();
        SiteData::flush();

        return redirect()->route('admin.content.index', $resource)->with('status', 'Deleted.');
    }

    private function save(Request $request, array $def, Model $item): RedirectResponse
    {
        $rules = FieldProcessor::rules($def['fields']);
        if (! empty($def['seo'])) {
            $rules += FieldProcessor::rules(config('admin.seo_fields'), 'seo.');
        }
        $request->validate($rules);

        $data = FieldProcessor::collect($def['fields'], $request->input(), $request->allFiles(), $item->exists ? $item->getAttributes() : []);

        // toggles / numbers need a sensible value even when left empty
        if (array_key_exists('sort', $data) && $data['sort'] === null) {
            $data['sort'] = ($def['model']::max('sort') ?? 0) + 1;
        }
        if (array_key_exists('rating', $data)) {
            $data['rating'] = max(1, min(5, (int) ($data['rating'] ?: 5)));
        }
        if (array_key_exists('slug', $data)) {
            $data['slug'] = $this->uniqueSlug($def['model'], $data['slug'], $data['title'] ?? [], $item);
        }
        foreach (['cover_fit', 'image_fit'] as $k) {
            if (array_key_exists($k, $data) && ! $data[$k]) {
                $data[$k] = 'cover';
            }
        }
        // Our Work main category: completed unless explicitly "running"; progress is a 0-100 percentage
        if (array_key_exists('status', $data) && $def['model'] === \App\Models\Project::class) {
            $data['status'] = $data['status'] === 'running' ? 'running' : 'completed';
        }
        if (array_key_exists('progress', $data) && $data['progress'] !== null) {
            $data['progress'] = max(0, min(100, (int) $data['progress']));
        }
        if (array_key_exists('type', $data) && ! $data['type']) {
            $data['type'] = 'text';
        }

        $item->fill($data)->save();

        if (! empty($def['seo'])) {
            $seoFields = config('admin.seo_fields');
            $seoData = FieldProcessor::collect($seoFields, (array) $request->input('seo', []), (array) $request->file('seo', []), $item->seoOrNew()->getAttributes());
            $item->seo()->updateOrCreate([], $seoData);
        }

        SiteData::flush();

        return redirect()->route('admin.content.edit', [$def['slug'], $item->id])->with('status', 'Saved.');
    }

    private function uniqueSlug(string $model, ?string $given, array $title, Model $self): string
    {
        $base = Str::slug($given ?: ($title['en'] ?? '')) ?: Str::slug($given ?: '') ?: 'item';
        $slug = $base;
        $i = 2;
        while ($model::where('slug', $slug)->when($self->exists, fn ($q) => $q->where('id', '!=', $self->id))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
