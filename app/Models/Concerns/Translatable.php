<?php

namespace App\Models\Concerns;

/**
 * Columns listed in $translatable hold {"bn": "...", "en": "..."}. tr('title') returns the current language,
 * falling back to Bangla so a missing English text never leaves a hole on the page.
 */
trait Translatable
{
    public function tr(string $field, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();
        $value = $this->getAttribute($field);

        if (! is_array($value)) {
            return $value;
        }

        $text = $value[$locale] ?? null;

        return ($text === null || $text === '' || $text === []) ? ($value['bn'] ?? '') : $text;
    }
}
