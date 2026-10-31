<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\FieldProcessor;
use App\Support\Features;
use App\Support\SiteData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private function group(string $group): array
    {
        $groups = config('admin.settings');
        abort_unless(isset($groups[$group]), 404);
        abort_if(! empty($groups[$group]['feature']) && ! Features::adminCan($groups[$group]['feature']), 404);

        return $groups[$group] + ['slug' => $group];
    }

    public function edit(string $group)
    {
        $def = $this->group($group);
        $values = [];
        foreach ($def['fields'] as $f) {
            $values[$f['key']] = Setting::get($f['key']);
        }

        return view('admin.settings', ['def' => $def, 'values' => $values]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        $def = $this->group($group);

        // fields marked developer_only are read-only for everybody except the developer account: they are not even read from the request
        $isDeveloper = (bool) $request->user()?->is_developer;
        $fields = array_values(array_filter($def['fields'], fn ($f) => empty($f['developer_only']) || $isDeveloper));
        $request->validate(FieldProcessor::rules($fields));

        $current = [];
        foreach ($fields as $f) {
            $current[$f['key']] = Setting::get($f['key']);
        }

        $values = FieldProcessor::collect($fields, $request->input(), $request->allFiles(), $current);
        foreach ($values as $key => $value) {
            Setting::put($key, $value ?? '');
        }
        SiteData::flush();

        return redirect()->route('admin.settings.edit', $group)->with('status', 'Saved.');
    }
}
