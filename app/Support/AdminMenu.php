<?php

namespace App\Support;

use App\Models\Page;

/**
 * The admin sidebar: main menus with sub menus. Add or move an entry here and the sidebar follows.
 * A group opens by itself when one of its pages is the current page.
 */
class AdminMenu
{
    /** URL + "is this the current screen?" for one pointer ('content' | 'setting' | 'seo' | 'route'). Null = cannot be resolved / not allowed. */
    private static function resolve(array $t): ?array
    {
        if (! empty($t['feature']) && ! Features::adminCan($t['feature'])) {
            return null;
        }
        foreach (['content', 'setting', 'seo'] as $type) {
            if (isset($t[$type])) {
                $m = Permissions::moduleFor($type, $t[$type]);
                if (! Permissions::allows(auth()->user(), $m ? $m.'.view' : '!') && ! auth()->user()?->isSuper()) {
                    return null;
                }
            }
        }
        if (isset($t['module']) && ! Permissions::allows(auth()->user(), $t['module'].'.view')) {
            return null;
        }
        if (isset($t['content'])) {
            $k = $t['content'];
            if (! empty(config("admin.resources.$k.feature")) && ! Features::adminCan(config("admin.resources.$k.feature"))) {
                return null;
            }

            return ['label' => $t['label'] ?? config("admin.resources.$k.title"), 'url' => route('admin.content.index', $k),
                'active' => request()->is("admin/c/$k", "admin/c/$k/*")];
        }
        if (isset($t['setting'])) {
            $k = $t['setting'];

            return ['label' => $t['label'] ?? config("admin.settings.$k.title"), 'url' => route('admin.settings.edit', $k),
                'active' => request()->is("admin/settings/$k")];
        }
        if (isset($t['seo'])) {
            $id = Page::where('key', $t['seo'])->value('id');

            return $id ? ['label' => $t['label'] ?? 'SEO & code', 'url' => route('admin.pages.edit', $id), 'active' => request()->is("admin/pages/$id")] : null;
        }
        if (isset($t['route']) && \Illuminate\Support\Facades\Route::has($t['route'])) {
            $r = $t['route'];

            return ['label' => $t['label'], 'url' => route($r), 'active' => request()->routeIs($r, str_replace('.index', '.*', $r))];
        }

        return null;
    }

    /** Website Management pages with their tabs resolved (hidden / unknown tabs dropped, "active" flags set). */
    public static function websitePages(): array
    {
        $out = [];
        foreach (config('admin.website', []) as $key => $page) {
            $tabs = array_values(array_filter(array_map(fn ($t) => static::resolve($t), $page['tabs'] ?? [])));
            if (! $tabs) {
                continue;
            }
            $out[$key] = ['key' => $key, 'label' => $page['label'], 'icon' => $page['icon'] ?? 'globe', 'tabs' => $tabs,
                'url' => $tabs[0]['url'], 'active' => (bool) array_filter($tabs, fn ($t) => $t['active'])];
        }

        return $out;
    }

    /** The Website Management page the admin is on right now (for the tab bar), or null on any other screen. */
    public static function currentWebsitePage(): ?array
    {
        foreach (static::websitePages() as $page) {
            if ($page['active']) {
                return $page;
            }
        }

        return null;
    }

    /**
     * The sidebar: main menus with sub menus. Dashboard and Messages sit above these in the layout.
     *   Website Management  -> one item per frontend page (config 'website'); the screens inside a page are tabs
     *   CRM                 -> modules that are switched on (config 'crm')
     *   Administration      -> users (roles & permissions will join them), my profile
     */
    public static function groups(): array
    {
        $groups = [];

        $pages = static::websitePages();
        if ($pages) {
            $groups[] = ['label' => 'Website Management', 'icon' => 'globe', 'children' => array_map(fn ($p) => ['label' => $p['label'], 'url' => $p['url'], 'active' => $p['active']], array_values($pages))];
        }

        $crm = array_values(array_filter(array_map(fn ($m) => static::resolve($m), config('admin.crm', []))));
        if ($crm) {
            $groups[] = ['label' => 'CRM', 'icon' => 'users', 'children' => $crm];
        }

        $hr = array_values(array_filter(array_map(fn ($m) => static::resolve($m), config('admin.hr', []))));
        if ($hr) {
            $groups[] = ['label' => 'HR & Attendance', 'icon' => 'clock', 'children' => $hr];
        }

        $groups[] = ['label' => 'Administration', 'icon' => 'shield', 'children' => array_values(array_filter([
            Permissions::allows(auth()->user(), 'users.view') ? ['label' => 'User management', 'url' => route('admin.users.index'), 'active' => request()->is('admin/users', 'admin/users/*')] : null,
            Permissions::allows(auth()->user(), 'departments.view') ? ['label' => 'Departments', 'url' => route('admin.departments.index'), 'active' => request()->is('admin/departments', 'admin/departments/*')] : null,
            Permissions::allows(auth()->user(), 'roles.view') ? ['label' => 'Roles & permissions', 'url' => route('admin.roles.index'), 'active' => request()->is('admin/roles', 'admin/roles/*')] : null,
            ['label' => 'My profile', 'url' => route('admin.profile'), 'active' => request()->is('admin/profile')],
        ]))];

        foreach ($groups as &$g) {
            $g['open'] = (bool) array_filter($g['children'], fn ($c) => $c['active']);
        }

        return $groups;
    }

    /** Small inline icons (24x24 stroke paths). */
    public static function icon(string $name): string
    {
        return [
            'home' => '<path d="M3 11l9-8 9 8M5 10v10h5v-6h4v6h5V10"/>',
            'building' => '<path d="M4 21V5l8-3 8 3v16M9 21v-5h6v5M8 9h.01M12 9h.01M16 9h.01M8 13h.01M12 13h.01M16 13h.01"/>',
            'book' => '<path d="M4 5a2 2 0 012-2h13v16H6a2 2 0 00-2 2V5zM4 19a2 2 0 012-2h13"/>',
            'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M16 5a3 3 0 010 6M21 20c0-2.5-1.6-4.6-4-5.5"/>',
            'layout' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M3 16h18"/>',
            'cog' => '<circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 00-.1-1.2l2-1.5-2-3.4-2.3.9a7 7 0 00-2-1.2L14 3h-4l-.6 2.6a7 7 0 00-2 1.2l-2.3-.9-2 3.4 2 1.5A7 7 0 005 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.4 2.3-.9c.6.5 1.3.9 2 1.2L10 21h4l.6-2.6c.7-.3 1.4-.7 2-1.2l2.3.9 2-3.4-2-1.5c.1-.4.1-.8.1-1.2z"/>',
            'dash' => '<path d="M4 13h7V4H4v9zm9 7h7v-9h-7v9zM4 20h7v-5H4v5zm9-11h7V4h-7v5z"/>',
            'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z"/>',
            'shield' => '<path d="M12 3l8 3v6c0 4.5-3.2 7.8-8 9-4.8-1.2-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
        ][$name] ?? '';
    }
}
