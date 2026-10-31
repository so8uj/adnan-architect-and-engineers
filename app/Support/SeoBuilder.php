<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\SeoMeta;

/**
 * Combines the page's own SEO record with the global defaults into everything the <head> needs.
 * $fallback may carry: title, description, image, type, schema (array|null), noindex (bool).
 */
class SeoBuilder
{
    public static function make(?SeoMeta $seo, array $fallback = []): array
    {
        $l = app()->getLocale();
        $sep = Setting::get('seo_title_separator', '|');
        $suffix = (string) Setting::tr('seo_title_suffix', $l, '');

        $title = trim((string) ($seo?->tr('meta_title', $l) ?: ($fallback['title'] ?? Setting::tr('site_name', $l))));
        if ($suffix !== '' && ! str_contains($title, $suffix)) {
            $title .= " $sep $suffix";
        }

        $description = trim((string) ($seo?->tr('meta_description', $l) ?: ($fallback['description'] ?? Setting::tr('seo_default_description', $l, ''))));
        $keywords = trim((string) ($seo?->tr('meta_keywords', $l) ?? ''));

        $image = $seo?->og_image ?: ($fallback['image'] ?? Setting::get('seo_default_og_image'));
        $image = $image ? url(SiteData::url($image)) : null;

        $noindex = ($fallback['noindex'] ?? false) || ($seo && ! $seo->robots_index);
        $robots = ($noindex ? 'noindex' : 'index').','.(($seo && ! $seo->robots_follow) ? 'nofollow' : 'follow');
        if ($seo?->robots_extra) {
            $robots .= ','.$seo->robots_extra;
        }

        $ogTitle = trim((string) ($seo?->tr('og_title', $l) ?: $title));
        $ogDescription = trim((string) ($seo?->tr('og_description', $l) ?: $description));

        $schemas = [];
        if (Setting::get('seo_org_schema', true)) {
            $schemas[] = json_encode(array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'ProfessionalService',
                'name' => Setting::tr('site_name', $l),
                'url' => url('/'),
                'logo' => url(SiteData::url(Setting::get('logo', 'assets/img/logo.png'))),
                'image' => $image,
                'telephone' => Setting::get('phone'),
                'email' => Setting::get('email'),
                'address' => ['@type' => 'PostalAddress', 'streetAddress' => Setting::tr('address', $l), 'addressCountry' => 'BD'],
                'sameAs' => array_values(array_filter([Setting::get('facebook')])),
            ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        if (! empty($fallback['schema'])) {
            $schemas[] = json_encode($fallback['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        if ($seo && trim((string) $seo->schema_json) !== '') {
            $custom = json_decode($seo->schema_json, true);
            // a valid JSON-LD object is re-encoded (safe); anything else is dropped rather than printed raw into the page
            if (json_last_error() === JSON_ERROR_NONE && is_array($custom)) {
                $schemas[] = json_encode($custom, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $seo?->canonical_url ?: url()->current(),
            'robots' => $robots,
            'og' => [
                'title' => $ogTitle, 'description' => $ogDescription, 'image' => $image,
                'type' => $seo?->og_type ?: ($fallback['type'] ?? 'website'),
                'locale' => $l === 'en' ? 'en_US' : 'bn_BD',
                'site_name' => Setting::tr('site_name', $l),
            ],
            'twitter' => ['card' => $seo?->twitter_card ?: 'summary_large_image', 'site' => Setting::get('seo_twitter_handle')],
            'schemas' => $schemas,
            // custom code: global first, then the page's own
            'head_code' => trim(Setting::get('code_head', '')."\n".($seo?->head_code ?? '')),
            'body_start_code' => trim(Setting::get('code_body_start', '')."\n".($seo?->body_start_code ?? '')),
            'body_end_code' => trim(Setting::get('code_body_end', '')."\n".($seo?->body_end_code ?? '')),
            'css' => trim(Setting::get('code_css', '')."\n".($seo?->custom_css ?? '')),
            'js' => trim(Setting::get('code_js', '')."\n".($seo?->custom_js ?? '')),
            'verify_google' => Setting::get('seo_google_verification'),
            'verify_bing' => Setting::get('seo_bing_verification'),
            'ga' => Setting::get('seo_ga_id'),
            'gtm' => Setting::get('seo_gtm_id'),
            'fbq' => Setting::get('seo_fb_pixel_id'),
            'favicon' => SiteData::url(Setting::get('favicon', 'assets/img/logo.png')),
        ];
    }
}
