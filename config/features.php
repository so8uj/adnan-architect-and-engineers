<?php

/**
 * Planned future features (CRM modules). Everything here is OFF until you switch it on in .env. When a module is on, EVERY admin sees it:
 *
 *   FUTURE_OPTIONS_ENABLED=true        master switch. false = none of these features appear anywhere (website or admin)
 *   FEATURE_CLIENTS=true               switch one feature off again even when the master switch is on
 *
 * After changing .env run:  php artisan config:clear   (or use "Clear cache" in the admin)
 *
 * Features are built one by one. Only "clients" exists so far; the others are reserved names for the next steps.
 */
return [
    'enabled' => (bool) env('FUTURE_OPTIONS_ENABLED', false),

    'modules' => [
        'clients' => (bool) env('FEATURE_CLIENTS', true),            // client list + "show on homepage"
        'quotes' => (bool) env('FEATURE_QUOTES', false),             // not built yet
        'invoices' => (bool) env('FEATURE_INVOICES', false),         // not built yet
        'attendance' => (bool) env('FEATURE_ATTENDANCE', false),     // shifts + clock in / out + late
        'leave' => (bool) env('FEATURE_LEAVE', false),               // holidays + leave requests, not built yet
        'tasks' => (bool) env('FEATURE_TASKS', false),               // daily tasks, not built yet
        'payroll' => (bool) env('FEATURE_PAYROLL', false),           // salary calculation, not built yet
        'accounting' => (bool) env('FEATURE_ACCOUNTING', false),     // sales and profit, not built yet
    ],
];
