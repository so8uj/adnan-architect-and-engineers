<?php

/**
 * Planned future features. Everything here is OFF until you switch it on in .env:
 *
 *   FUTURE_OPTIONS_ENABLED=true        master switch. false = none of these features appear anywhere (website or admin)
 *   FUTURE_OPTIONS_VISIBLE_TO=developer   who sees the admin screens: "developer" (only Sobuj) or "all" (every admin)
 *   FEATURE_CLIENTS=true               switch one feature off again even when the master switch is on
 *
 * After changing .env run:  php artisan config:clear   (or use "Clear cache" in the admin)
 *
 * Features are built one by one. Only "clients" exists so far; the others are reserved names for the next steps.
 */
return [
    'enabled' => (bool) env('FUTURE_OPTIONS_ENABLED', false),

    'admin_visible_to' => env('FUTURE_OPTIONS_VISIBLE_TO', 'developer'),

    'modules' => [
        'clients' => (bool) env('FEATURE_CLIENTS', true),            // client list + "show on homepage"
        'quotes' => (bool) env('FEATURE_QUOTES', false),             // not built yet
        'invoices' => (bool) env('FEATURE_INVOICES', false),         // not built yet
        'attendance' => (bool) env('FEATURE_ATTENDANCE', false),     // clock in / out, not built yet
        'attendance_rules' => (bool) env('FEATURE_ATTENDANCE_RULES', false), // late, holiday, leave ... not built yet
        'accounting' => (bool) env('FEATURE_ACCOUNTING', false),     // sales and profit, not built yet
    ],
];
