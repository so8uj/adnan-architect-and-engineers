<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Key/value site settings. Arrays are stored as JSON. All settings are cached as one array. */
class Setting extends Model
{
    protected $guarded = [];

    public static function all_(): array
    {
        return Cache::rememberForever('settings.all', function () {
            return static::query()->pluck('value', 'key')->map(function ($v) {
                $d = json_decode((string) $v, true);

                return (json_last_error() === JSON_ERROR_NONE && (is_array($d) || is_bool($d))) ? $d : $v;
            })->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all_();

        return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '' ? $all[$key] : $default;
    }

    /** Translated text of a bilingual setting. */
    public static function tr(string $key, ?string $locale = null, mixed $default = ''): mixed
    {
        $locale ??= app()->getLocale();
        $v = static::get($key);
        if (! is_array($v)) {
            return $v ?? $default;
        }
        $t = $v[$locale] ?? null;

        return ($t === null || $t === '') ? ($v['bn'] ?? $default) : $t;
    }

    public static function put(string $key, mixed $value): void
    {
        $stored = is_array($value) || is_bool($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        static::updateOrCreate(['key' => $key], ['value' => $stored]);
        Cache::forget('settings.all');
    }
}
