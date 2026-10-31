<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Turns the colours chosen in Admin -> Theme into CSS variables. The compiled Tailwind classes read these variables,
 * so a colour change needs no rebuild. Output is one small <style> block.
 */
class Theme
{
    /** The original hand-picked palette, kept exactly when the primary colour is still the default red. */
    private const DEFAULT_PRIMARY = '#e8322c';

    private const DEFAULT_SHADES = [
        50 => '#fff3ef', 100 => '#ffe1d8', 400 => '#ff7a4d', 500 => '#ff5a36', 600 => '#e8322c', 700 => '#c4201f', 900 => '#5a0f12',
    ];

    public static function css(): string
    {
        $light = static::block('light');
        $dark = static::block('dark');

        return ":root{{$light}}\nhtml.dark{{$dark}}";
    }

    public static function defaultTheme(): string
    {
        return Setting::get('theme_default', 'light') === 'dark' ? 'dark' : 'light';
    }

    private static function block(string $mode): string
    {
        $primary = static::hex(Setting::get("theme_{$mode}_primary"), self::DEFAULT_PRIMARY);
        $shades = strtolower($primary) === self::DEFAULT_PRIMARY ? self::DEFAULT_SHADES : static::derive($primary);

        $css = '';
        foreach ($shades as $k => $hex) {
            $css .= "--brand-$k:".static::rgb($hex).';';
        }

        $defaults = ['light' => ['#ffffff', '#ffffff', '#1c1c1f'], 'dark' => ['#0d0d10', '#17171b', '#ececf0']][$mode];
        $css .= '--bg:'.static::rgb(static::hex(Setting::get("theme_{$mode}_bg"), $defaults[0])).';';
        $css .= '--surface:'.static::rgb(static::hex(Setting::get("theme_{$mode}_surface"), $defaults[1])).';';
        $css .= '--text:'.static::rgb(static::hex(Setting::get("theme_{$mode}_text"), $defaults[2])).';';

        return $css;
    }

    private static function derive(string $hex): array
    {
        return [
            50 => static::mix($hex, '#ffffff', .94), 100 => static::mix($hex, '#ffffff', .86),
            400 => static::mix($hex, '#ffffff', .25), 500 => static::mix($hex, '#ffffff', .12),
            600 => $hex,
            700 => static::mix($hex, '#000000', .16), 900 => static::mix($hex, '#000000', .68),
        ];
    }

    private static function mix(string $a, string $b, float $w): string
    {
        [$r1, $g1, $b1] = static::parts($a);
        [$r2, $g2, $b2] = static::parts($b);

        return sprintf('#%02x%02x%02x', round($r1 + ($r2 - $r1) * $w), round($g1 + ($g2 - $g1) * $w), round($b1 + ($b2 - $b1) * $w));
    }

    private static function parts(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private static function rgb(string $hex): string
    {
        return implode(' ', static::parts($hex));
    }

    private static function hex(mixed $v, string $fallback): string
    {
        return is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', $v) ? $v : $fallback;
    }
}
