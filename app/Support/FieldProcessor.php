<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Turns a submitted admin form into clean values, driven by the field lists in config/admin.php.
 * Input conventions (so one processor serves settings, content lists and the SEO block):
 *   text/…           name="key"
 *   bi_*             name="key[bn]" and name="key[en]"
 *   image            file input name="key", current path in name="key__current", name="key__remove" = 1 to delete
 *   repeater/gallery name="key[0][sub]" ...
 */
class FieldProcessor
{
    /** Validation rules for a list of fields. $prefix is "" or e.g. "seo.". */
    public static function rules(array $fields, string $prefix = ''): array
    {
        $rules = [];
        foreach ($fields as $f) {
            $k = $prefix.$f['key'];
            $req = ! empty($f['required']);
            switch ($f['type']) {
                case 'bi_text':
                    $rules[$k.'.bn'] = [$req ? 'required' : 'nullable', 'string', 'max:500'];
                    $rules[$k.'.en'] = ['nullable', 'string', 'max:500'];
                    break;
                case 'bi_textarea':
                case 'bi_lines':
                case 'bi_pairs':
                case 'bi_html':
                    $rules[$k.'.bn'] = [$req ? 'required' : 'nullable', 'string', 'max:100000'];
                    $rules[$k.'.en'] = ['nullable', 'string', 'max:100000'];
                    break;
                case 'number':
                    $rules[$k] = ['nullable', 'numeric', 'min:0'];
                    break;
                case 'color':
                    $rules[$k] = ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'];
                    break;
                case 'url':
                    $rules[$k] = ['nullable', 'url', 'max:500'];
                    break;
                case 'date':
                    $rules[$k] = ['nullable', 'date'];
                    break;
                case 'select':
                    $rules[$k] = ['nullable', 'in:'.implode(',', array_keys($f['options']))];
                    break;
                case 'image':
                    $rules[$k] = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:8192'];
                    break;
                case 'text':
                    $rules[$k] = [$req ? 'required' : 'nullable', 'string', 'max:500'];
                    break;
                case 'textarea':
                case 'code':
                    $rules[$k] = ['nullable', 'string', 'max:200000'];
                    break;
                case 'repeater':
                    $rules[$k] = ['nullable', 'array', 'max:50'];
                    break;
                case 'gallery':
                    $rules[$k] = ['nullable', 'array', 'max:60'];
                    $rules[$k.'.*.file'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:8192'];
                    break;
            }
        }

        return $rules;
    }

    /**
     * @param  array  $input  request input (or a nested part of it)
     * @param  array  $files  uploaded files (same nesting)
     * @param  array  $current  existing values keyed by field key (used to keep images / untouched fields)
     * @return array key => processed value
     */
    public static function collect(array $fields, array $input, array $files = [], array $current = []): array
    {
        $out = [];
        foreach ($fields as $f) {
            $k = $f['key'];
            $out[$k] = static::one($f, $input, $files, $current[$k] ?? null);
        }

        return $out;
    }

    private static function one(array $f, array $input, array $files, mixed $current): mixed
    {
        $k = $f['key'];
        $in = $input[$k] ?? null;

        switch ($f['type']) {
            case 'toggle':
                return (bool) ($in ?? false);

            case 'number':
                return ($in === null || $in === '') ? null : (str_contains((string) $in, '.') ? (float) $in : (int) $in);

            case 'bi_text':
            case 'bi_textarea':
            case 'bi_html':
                return ['bn' => trim((string) ($in['bn'] ?? '')), 'en' => trim((string) ($in['en'] ?? ''))];

            case 'bi_lines':
                return ['bn' => static::lines($in['bn'] ?? ''), 'en' => static::lines($in['en'] ?? '')];

            case 'bi_pairs':
                return ['bn' => static::pairs($in['bn'] ?? ''), 'en' => static::pairs($in['en'] ?? '')];

            case 'image':
                $file = $files[$k] ?? null;
                if ($file instanceof UploadedFile) {
                    return static::store($file);
                }
                if (! empty($input[$k.'__remove'])) {
                    return null;
                }

                return $input[$k.'__current'] ?? $current;

            case 'repeater':
                $rows = [];
                foreach ((array) $in as $row) {
                    $clean = static::collect($f['sub'], (array) $row);
                    if (array_filter($clean, fn ($v) => $v !== null && $v !== '' && $v !== ['bn' => '', 'en' => ''])) {
                        $rows[] = $clean;
                    }
                }

                return $rows;

            case 'gallery':
                $rows = [];
                foreach ((array) $in as $i => $row) {
                    $row = (array) $row;
                    $file = $files[$k][$i]['file'] ?? null;
                    $src = $file instanceof UploadedFile ? static::store($file) : ($row['src'] ?? null);
                    if (! $src) {
                        continue;
                    }
                    $rows[] = [
                        'src' => $src,
                        'title' => ['bn' => trim((string) ($row['title']['bn'] ?? '')), 'en' => trim((string) ($row['title']['en'] ?? ''))],
                        'size' => ['bn' => trim((string) ($row['size']['bn'] ?? '')), 'en' => trim((string) ($row['size']['en'] ?? ''))],
                        'note' => ['bn' => trim((string) ($row['note']['bn'] ?? '')), 'en' => trim((string) ($row['note']['en'] ?? ''))],
                    ];
                }

                return $rows;

            default: // text, url, color, select, date, textarea, code
                $v = $in === null ? null : trim((string) $in);

                return ($v === '' || $v === null) ? null : $v;
        }
    }

    private static function lines(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $text)), fn ($l) => $l !== ''));
    }

    private static function pairs(string $text): array
    {
        $out = [];
        foreach (static::lines($text) as $line) {
            [$label, $value] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
            $out[] = ['label' => $label, 'value' => $value];
        }

        return $out;
    }

    /** Saves an upload under public/uploads/YYYY/MM and returns the path relative to public/. */
    public static function store(UploadedFile $file): string
    {
        $dir = trim(config('admin.upload_dir', 'uploads'), '/').'/'.date('Y/m');
        $name = Str::random(14).'.'.strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $file->move(public_path($dir), $name);

        return $dir.'/'.$name;
    }

    // ---- helpers for the form views -------------------------------------------------------------

    /** Text for a bi_lines / bi_pairs textarea. */
    public static function linesText(mixed $v): string
    {
        return is_array($v) ? implode("\n", $v) : '';
    }

    public static function pairsText(mixed $v): string
    {
        return is_array($v) ? implode("\n", array_map(fn ($p) => ($p['label'] ?? '').' | '.($p['value'] ?? ''), $v)) : '';
    }
}
