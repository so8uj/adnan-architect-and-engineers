<?php

namespace App\Support;

use App\Models\User;

/** One place that answers "is this future feature on, and may this user see it?". See config/features.php. */
class Features
{
    public static function master(): bool
    {
        return (bool) config('features.enabled');
    }

    /** Is the feature switched on for the public website (needs the master switch and its own switch)? */
    public static function on(string $module): bool
    {
        return static::master() && (bool) config("features.modules.$module", false);
    }

    /** May this admin user open the feature's admin screens? By default only the developer account may. */
    public static function adminCan(string $module, ?User $user = null): bool
    {
        $user ??= auth()->user();
        if (! $user || ! static::on($module)) {
            return false;
        }

        return config('features.admin_visible_to') === 'all' || (bool) $user->is_developer;
    }
}
