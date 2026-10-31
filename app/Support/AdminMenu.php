<?php

namespace App\Support;

use App\Models\Page;

/**
 * The admin sidebar: main menus with sub menus. Add or move an entry here and the sidebar follows.
 * A group opens by itself when one of its pages is the current page.
 */
class AdminMenu
{
    public static function groups(): array
    {
        $pages = Page::pluck('id', 'key');

        $content = fn (string $key, ?string $label = null) => [
            'label' => $label ?? config("admin.resources.$key.title"),
            'url' => route('admin.content.index', $key),
            'active' => request()->is("admin/c/$key", "admin/c/$key/*"),
        ];
        $setting = fn (string $key, ?string $label = null) => [
            'label' => $label ?? config("admin.settings.$key.title"),
            'url' => route('admin.settings.edit', $key),
            'active' => request()->is("admin/settings/$key"),
        ];
        $seo = fn (string $key, string $label) => isset($pages[$key]) ? [
            'label' => $label,
            'url' => route('admin.pages.edit', $pages[$key]),
            'active' => request()->is('admin/pages/'.$pages[$key]),
        ] : null;

        $groups = [
            ['label' => 'Home Page', 'icon' => 'home', 'children' => [
                $content('hero-slides'),
                $setting('home', 'Stats, intro video & credit'),
                $content('services'),
                $content('steps'),
                $content('why-us'),
                $content('testimonials'),
                $content('activities'),
                $content('faqs'),
                $seo('home', 'Home page SEO & code'),
            ]],
            ['label' => 'Portfolio', 'icon' => 'building', 'children' => [
                $content('projects'),
                $seo('portfolio', 'Portfolio page SEO & code'),
            ]],
            ['label' => 'Insights', 'icon' => 'book', 'children' => [
                $content('insights', 'Insights (blog posts)'),
                $seo('insights', 'Insights page SEO & code'),
            ]],
            ['label' => 'Header & Footer', 'icon' => 'layout', 'children' => [
                $setting('header'),
                $setting('footer'),
            ]],
            ['label' => 'Settings', 'icon' => 'cog', 'children' => [
                $setting('general'),
                $setting('theme'),
                $setting('motion'),
                $setting('code'),
                $setting('seo'),
                ['label' => 'User management', 'url' => route('admin.users.index'), 'active' => request()->is('admin/users', 'admin/users/*')],
            ]],
        ];

        // future features: only listed when switched on in .env and visible to this user (see config/features.php)
        if (Features::adminCan('clients')) {
            array_splice($groups, 3, 0, [['label' => 'Clients', 'icon' => 'users', 'children' => [
                $content('clients', 'Client list'),
                $setting('clients', 'Homepage section'),
            ]]]);
        }

        foreach ($groups as &$g) {
            $g['children'] = array_values(array_filter($g['children']));
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
        ][$name] ?? '';
    }
}
