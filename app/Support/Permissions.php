<?php

namespace App\Support;

use App\Models\Page;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * The permission catalogue and the "which permission does this request need?" rules.
 *
 * A permission is "<module>.view" or "<module>.manage" (manage includes view). Modules:
 *   website.<page>   one per Website Management page in config('admin.website')
 *   crm.<module>     one per CRM line in config('admin.crm')
 *   messages, users, departments, roles
 * Super Admin (role slug "super-admin") and the developer account pass every check.
 */
class Permissions
{
    /** [group label => [module key => label]] */
    public static function catalogue(): array
    {
        $website = [];
        foreach (config('admin.website', []) as $key => $page) {
            $website["website.$key"] = $page['label'];
        }
        $crm = [];
        foreach (config('admin.crm', []) as $m) {
            $crm['crm.'.static::crmKey($m)] = $m['label'];
        }

        $hr = [];
        foreach (config('admin.hr', []) as $m) {
            if (isset($m['module'])) {
                $hr[$m['module']] = $m['label'];
            }
        }

        return array_filter([
            'Website Management' => $website,
            'CRM' => $crm,
            'HR & Attendance' => $hr,
            'Administration' => [
                'messages' => 'Contact messages',
                'users' => 'Users',
                'departments' => 'Departments',
                'roles' => 'Roles & permissions',
            ],
        ]);
    }

    /** Every valid permission string. */
    public static function all(): array
    {
        $out = [];
        foreach (static::catalogue() as $modules) {
            foreach (array_keys($modules) as $m) {
                $out[] = "$m.view";
                $out[] = "$m.manage";
            }
        }

        return $out;
    }

    public static function crmKey(array $m): string
    {
        return $m['content'] ?? $m['setting'] ?? $m['key'] ?? str($m['label'])->slug()->toString();
    }

    /** Module that owns a menu pointer (content / setting / seo) - null when it is not part of the menu. */
    public static function moduleFor(string $type, string $key): ?string
    {
        foreach (config('admin.website', []) as $pk => $page) {
            foreach ($page['tabs'] ?? [] as $t) {
                if (($t[$type] ?? null) === $key) {
                    return "website.$pk";
                }
            }
        }
        foreach (config('admin.crm', []) as $m) {
            if (($m[$type] ?? null) === $key) {
                return 'crm.'.static::crmKey($m);
            }
        }

        return null;
    }

    /** Null permission = open to every signed-in admin. */
    public static function allows(?User $user, ?string $permission): bool
    {
        return $user !== null && ($permission === null || $user->hasPermission($permission));
    }

    /** The permission a request needs. null = any signed-in admin (dashboard, own profile ...). '!' = Super Admin only. */
    public static function required(Request $request): ?string
    {
        $name = (string) $request->route()?->getName();
        $get = $request->isMethodSafe();

        if (str_starts_with($name, 'admin.content.')) {
            $module = static::moduleFor('content', (string) $request->route('resource'));

            return $module ? $module.($name === 'admin.content.index' ? '.view' : '.manage') : '!';
        }
        if (str_starts_with($name, 'admin.settings.')) {
            $module = static::moduleFor('setting', (string) $request->route('group'));

            return $module ? $module.($get ? '.view' : '.manage') : '!';
        }
        if ($name === 'admin.pages.index') {
            return 'website.any';
        }
        if (str_starts_with($name, 'admin.pages.')) {
            $page = $request->route('page');
            $key = $page instanceof Page ? $page->key : Page::whereKey($page)->value('key');
            $module = $key ? static::moduleFor('seo', $key) : null;

            return $module ? $module.($get ? '.view' : '.manage') : '!';
        }
        $views = ['admin.messages', 'admin.users.index', 'admin.roles.index', 'admin.departments.index'];
        foreach (['admin.messages' => 'messages', 'admin.users.' => 'users', 'admin.roles.' => 'roles', 'admin.departments.' => 'departments'] as $prefix => $module) {
            if (str_starts_with($name, $prefix)) {
                return $module.(in_array($name, $views, true) ? '.view' : '.manage');
            }
        }

        return null;
    }
}
