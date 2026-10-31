<?php

namespace App\Support;

class Assets
{
    /** "/assets/js/app.js?v=<file time>": the version changes by itself whenever the file changes, so no hard refresh is ever needed. */
    public static function v(string $path): string
    {
        $path = ltrim($path, '/');
        $file = public_path($path);

        return '/'.$path.(is_file($file) ? '?v='.filemtime($file) : '');
    }
}
