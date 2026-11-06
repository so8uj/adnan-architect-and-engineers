<?php

/**
 * Describes every admin form. The admin screens are generated from this file: add a field here and it shows up in the
 * form, is validated, saved and (for settings) available as Setting::get('key').
 *
 * Field types: text, url, number, color, textarea, code, select, toggle, image, date,
 *              bi_text, bi_textarea, bi_html, bi_lines, bi_pairs   (bi_* = one box for Bangla + one for English),
 *              repeater (rows of sub fields), gallery (project images).
 */

// where admin uploads are saved, relative to public/ (tests switch this to a throw-away folder)
$uploadDir = env('UPLOAD_DIR', 'uploads');

$icons = ['soil' => 'Soil', 'arch' => 'Architecture', 'struct' => 'Structure', 'elec' => 'Electric', 'fire' => 'Fire', 'design' => 'Design', 'cube' => '3D model', 'rajuk' => 'RAJUK', 'city' => 'City / Municipality'];

return [

    'upload_dir' => $uploadDir,

    'icons' => $icons,

    /* ------------------------------------------------------------ settings pages */

    /*
     * ADMIN MENU (normalised). The sidebar and the tab bars are built from these two lists + App\Support\AdminMenu.
     *
     *   'website' = Website Management. Each entry is one frontend page / area (a sidebar item). Its 'tabs' are the screens that
     *               control it, shown as a tab strip across the top of every one of those screens. A tab points at ONE existing
     *               screen:  'content' => <key in 'resources'>   |   'setting' => <key in 'settings'>   |   'seo' => <Page key>
     *               Optional per tab: 'label', 'feature' (shown only when that CRM/future module is on).
     *   'crm'     = CRM menu. One line per module; it appears only when its 'feature' is switched on (config/features.php).
     *               Same pointer keys as a tab ('content' | 'setting'), or 'route' => '<named route>' for a custom screen.
     */
    'website' => [
        'home' => ['label' => 'Home Page', 'icon' => 'home', 'tabs' => [
            ['label' => 'Hero slider', 'content' => 'hero-slides'],
            ['label' => 'Section headings', 'setting' => 'sections'],
            ['label' => 'Intro video', 'setting' => 'home'],
            ['label' => 'Services', 'content' => 'services'],
            ['label' => 'Process', 'content' => 'steps'],
            ['label' => 'Why us', 'content' => 'why-us'],
            ['label' => 'Testimonials', 'content' => 'testimonials'],
            ['label' => 'Activities', 'content' => 'activities'],
            ['label' => 'FAQs', 'content' => 'faqs'],
            ['label' => 'Clients strip', 'setting' => 'clients', 'feature' => 'clients'],
            ['label' => 'SEO & code', 'seo' => 'home'],
        ]],
        'works' => ['label' => 'Our Work', 'icon' => 'building', 'tabs' => [
            ['label' => 'Projects', 'content' => 'projects'],
            ['label' => 'SEO & code', 'seo' => 'portfolio'],
        ]],
        'insights' => ['label' => 'Insights', 'icon' => 'book', 'tabs' => [
            ['label' => 'Posts', 'content' => 'insights'],
            ['label' => 'SEO & code', 'seo' => 'insights'],
        ]],
        'header' => ['label' => 'Header', 'icon' => 'layout', 'tabs' => [
            ['label' => 'Header', 'setting' => 'header'],
        ]],
        'footer' => ['label' => 'Footer', 'icon' => 'layout', 'tabs' => [
            ['label' => 'Footer', 'setting' => 'footer'],
        ]],
        'global' => ['label' => 'Global settings', 'icon' => 'cog', 'tabs' => [
            ['label' => 'General & contact', 'setting' => 'general'],
            ['label' => 'Theme colours', 'setting' => 'theme'],
            ['label' => 'Animation & preloader', 'setting' => 'motion'],
            ['label' => 'Custom code', 'setting' => 'code'],
            ['label' => 'SEO (global)', 'setting' => 'seo'],
        ]],
    ],

    'crm' => [
        ['label' => 'Client list', 'feature' => 'clients', 'content' => 'clients'],
        // next modules: one line each, e.g.
        // ['label' => 'Leads', 'feature' => 'leads', 'route' => 'admin.leads.index'],
        // ['label' => 'Attendance', 'feature' => 'attendance', 'route' => 'admin.attendance.index'],
        // ['label' => 'Payroll', 'feature' => 'payroll', 'route' => 'admin.payroll.index'],
    ],

    /*
     * HR & ATTENDANCE menu. Every line needs its 'feature' switched on (config/features.php) AND its named 'route' to exist (so planned
     * screens stay hidden until they are built).
     *   'self'   => true      everybody sees it (own attendance, own leave, own tasks, own payslips) - no permission needed
     *   'module' => 'hr.x'    needs hr.x.view; saving needs hr.x.manage. Also builds the "HR & Attendance" block of the role form.
     */
    'hr' => [
        ['label' => 'My attendance', 'feature' => 'attendance', 'route' => 'admin.attendance.me', 'self' => true],
        ['label' => 'Attendance sheet', 'feature' => 'attendance', 'route' => 'admin.attendance.index', 'module' => 'hr.attendance'],
        ['label' => 'Shifts', 'feature' => 'attendance', 'route' => 'admin.shifts.index', 'module' => 'hr.shifts'],
        ['label' => 'My leave', 'feature' => 'leave', 'route' => 'admin.leave.me', 'self' => true],
        ['label' => 'Leave requests', 'feature' => 'leave', 'route' => 'admin.leave.index', 'module' => 'hr.leave'],
        ['label' => 'Holidays', 'feature' => 'leave', 'route' => 'admin.holidays.index', 'module' => 'hr.holidays'],
        ['label' => 'My tasks', 'feature' => 'tasks', 'route' => 'admin.tasks.me', 'self' => true],
        ['label' => 'Team tasks', 'feature' => 'tasks', 'route' => 'admin.tasks.index', 'module' => 'hr.tasks'],
        ['label' => 'My payslips', 'feature' => 'payroll', 'route' => 'admin.payroll.me', 'self' => true],
        ['label' => 'Payroll', 'feature' => 'payroll', 'route' => 'admin.payroll.index', 'module' => 'hr.payroll'],
    ],

    'settings' => [
        'general' => [
            'title' => 'General & Contact', 'icon' => 'cog',
            'fields' => [
                ['key' => 'site_name', 'type' => 'bi_text', 'label' => 'Site name', 'required' => true],
                ['key' => 'tagline', 'type' => 'bi_text', 'label' => 'Tagline'],
                ['key' => 'logo', 'type' => 'image', 'label' => 'Logo'],
                ['key' => 'favicon', 'type' => 'image', 'label' => 'Favicon'],
                ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
                ['key' => 'email', 'type' => 'text', 'label' => 'Email'],
                ['key' => 'address', 'type' => 'bi_text', 'label' => 'Address'],
                ['key' => 'map_embed', 'type' => 'textarea', 'label' => 'Google Maps embed (map above the footer)', 'rows' => 3, 'help' => 'Google Maps → Share → Embed a map → copy the HTML and paste it here (the whole <iframe> code or just the src link). Leave empty to hide the map.'],
                ['key' => 'facebook', 'type' => 'url', 'label' => 'Facebook page URL'],
                ['key' => 'whatsapp', 'type' => 'text', 'label' => 'WhatsApp number', 'help' => 'With country code, digits only. Example: 8801511092580'],
                ['key' => 'messenger', 'type' => 'text', 'label' => 'Messenger username', 'help' => 'The Facebook page username (m.me/<username>).'],
                ['key' => 'greeting', 'type' => 'text', 'label' => 'Default chat message', 'help' => 'Pre-filled when someone opens WhatsApp / Messenger.'],
            ],
        ],
        'sections' => [
            'title' => 'Section headings', 'icon' => 'home',
            'intro' => 'The label, heading, paragraph and (where a section has one) the button above each home page section. Empty a field to hide that line.',
            'fields' => [
                ['key' => 'sec_services_eyebrow', 'type' => 'bi_text', 'label' => 'Small label above the heading', 'group' => 'Services'],
                ['key' => 'sec_services_title', 'type' => 'bi_text', 'label' => 'Heading', 'group' => 'Services'],
                ['key' => 'sec_services_text', 'type' => 'bi_textarea', 'label' => 'Paragraph', 'rows' => 2, 'group' => 'Services'],
                ['key' => 'sec_portfolio_eyebrow', 'type' => 'bi_text', 'label' => 'Small label above the heading', 'group' => 'Our Work (projects)'],
                ['key' => 'sec_portfolio_title', 'type' => 'bi_text', 'label' => 'Heading', 'group' => 'Our Work (projects)'],
                ['key' => 'sec_portfolio_text', 'type' => 'bi_textarea', 'label' => 'Paragraph', 'rows' => 2, 'group' => 'Our Work (projects)'],
                ['key' => 'sec_portfolio_btn_show', 'type' => 'toggle', 'label' => 'Show the button', 'group' => 'Our Work (projects)'],
                ['key' => 'sec_portfolio_btn_label', 'type' => 'bi_text', 'label' => 'Button text', 'group' => 'Our Work (projects)'],
                ['key' => 'sec_portfolio_btn_url', 'type' => 'text', 'label' => 'Button link', 'help' => 'Example: /portfolio or /#contact', 'group' => 'Our Work (projects)'],
                ['key' => 'sec_activities_eyebrow', 'type' => 'bi_text', 'label' => 'Small label above the heading', 'group' => 'Activities (gallery)'],
                ['key' => 'sec_activities_title', 'type' => 'bi_text', 'label' => 'Heading', 'group' => 'Activities (gallery)'],
                ['key' => 'sec_activities_text', 'type' => 'bi_textarea', 'label' => 'Paragraph', 'rows' => 2, 'group' => 'Activities (gallery)'],
                ['key' => 'sec_process_eyebrow', 'type' => 'bi_text', 'label' => 'Small label above the heading', 'group' => 'Process ("How we work")'],
                ['key' => 'sec_process_title', 'type' => 'bi_text', 'label' => 'Heading', 'group' => 'Process ("How we work")'],
                ['key' => 'sec_process_text', 'type' => 'bi_textarea', 'label' => 'Paragraph', 'rows' => 2, 'group' => 'Process ("How we work")'],
                ['key' => 'sec_about_eyebrow', 'type' => 'bi_text', 'label' => 'Small label above the heading', 'group' => 'About / Why us'],
                ['key' => 'sec_about_title', 'type' => 'bi_text', 'label' => 'Heading', 'group' => 'About / Why us'],
                ['key' => 'sec_about_text', 'type' => 'bi_textarea', 'label' => 'Paragraph', 'rows' => 2, 'group' => 'About / Why us'],
                ['key' => 'sec_testimonials_eyebrow', 'type' => 'bi_text', 'label' => 'Small label above the heading', 'group' => 'Testimonials'],
                ['key' => 'sec_testimonials_title', 'type' => 'bi_text', 'label' => 'Heading', 'group' => 'Testimonials'],
                ['key' => 'sec_testimonials_text', 'type' => 'bi_textarea', 'label' => 'Paragraph', 'rows' => 2, 'group' => 'Testimonials'],
                ['key' => 'sec_insights_eyebrow', 'type' => 'bi_text', 'label' => 'Small label above the heading', 'group' => 'Insights'],
                ['key' => 'sec_insights_title', 'type' => 'bi_text', 'label' => 'Heading', 'group' => 'Insights'],
                ['key' => 'sec_insights_text', 'type' => 'bi_textarea', 'label' => 'Paragraph', 'rows' => 2, 'group' => 'Insights'],
                ['key' => 'sec_insights_btn_show', 'type' => 'toggle', 'label' => 'Show the button', 'group' => 'Insights'],
                ['key' => 'sec_insights_btn_label', 'type' => 'bi_text', 'label' => 'Button text', 'group' => 'Insights'],
                ['key' => 'sec_insights_btn_url', 'type' => 'text', 'label' => 'Button link', 'help' => 'Example: /portfolio or /#contact', 'group' => 'Insights'],
                ['key' => 'sec_faq_title', 'type' => 'bi_text', 'label' => 'Heading', 'group' => 'FAQ'],
                ['key' => 'sec_faq_text', 'type' => 'bi_textarea', 'label' => 'Paragraph', 'rows' => 2, 'group' => 'FAQ'],
                ['key' => 'work_completed_label', 'type' => 'bi_text', 'label' => 'Menu / tab name', 'group' => 'Our Work page – Completed Projects'],
                ['key' => 'work_completed_title', 'type' => 'bi_text', 'label' => 'Page heading', 'group' => 'Our Work page – Completed Projects'],
                ['key' => 'work_completed_text', 'type' => 'bi_textarea', 'label' => 'Page paragraph', 'rows' => 2, 'group' => 'Our Work page – Completed Projects'],
                ['key' => 'work_running_label', 'type' => 'bi_text', 'label' => 'Menu / tab name', 'group' => 'Our Work page – Running Projects'],
                ['key' => 'work_running_title', 'type' => 'bi_text', 'label' => 'Page heading', 'group' => 'Our Work page – Running Projects'],
                ['key' => 'work_running_text', 'type' => 'bi_textarea', 'label' => 'Page paragraph', 'rows' => 2, 'group' => 'Our Work page – Running Projects'],
                ['key' => 'sec_contact_title', 'type' => 'bi_text', 'label' => 'Heading', 'group' => 'Contact'],
                ['key' => 'sec_contact_text', 'type' => 'bi_textarea', 'label' => 'Paragraph', 'rows' => 2, 'group' => 'Contact'],
            ],
        ],
        'home' => [
            'title' => 'Home sections', 'icon' => 'home',
            'fields' => [
                ['key' => 'around_items', 'type' => 'repeater', 'label' => 'Texts around the intro video', 'help' => 'The plain lines that appear around the video when it shrinks. Pick the side for each one (top / left / right / bottom — one per side), type the text, and add a number only if it should count up (e.g. 500 + "+" + "Projects completed"). Remove a row to remove that text. Drag to reorder (order does not matter).', 'sub' => [
                    ['key' => 'pos', 'type' => 'select', 'label' => 'Side', 'options' => ['top' => 'Top', 'left' => 'Left', 'right' => 'Right', 'bottom' => 'Bottom']],
                    ['key' => 'value', 'type' => 'number', 'label' => 'Number (optional)'],
                    ['key' => 'suffix', 'type' => 'text', 'label' => 'After the number (+, %)'],
                    ['key' => 'text', 'type' => 'bi_text', 'label' => 'Text'],
                ]],
                ['key' => 'intro_video', 'type' => 'text', 'label' => 'Intro video link', 'help' => 'A YouTube or Facebook video link, or a file such as uploads/intro.mp4.'],
                ['key' => 'intro_poster', 'type' => 'image', 'label' => 'Intro video poster image'],
            ],
        ],
        'motion' => [
            'title' => 'Animation & preloader', 'icon' => 'bolt',
            'fields' => [
                ['key' => 'preloader_show', 'type' => 'toggle', 'label' => 'Show the logo preloader'],
                ['key' => 'preloader_min_ms', 'type' => 'number', 'label' => 'Preloader minimum time (milliseconds)'],
                ['key' => 'preloader_first_visit_only', 'type' => 'toggle', 'label' => 'Show the preloader only on the first visit of a session'],
                ['key' => 'respect_reduced_motion', 'type' => 'toggle', 'label' => 'Follow the visitor\'s "reduce motion" setting', 'help' => 'ON turns all animation off for visitors who disabled animations on their computer.'],
            ],
        ],
        'header' => [
            'title' => 'Header', 'icon' => 'cog',
            'fields' => [
                ['key' => 'header_topbar_show', 'type' => 'toggle', 'label' => 'Show the dark top bar (address, phone, Facebook)'],
                ['key' => 'header_cta_show', 'type' => 'toggle', 'label' => 'Show the call-to-action button'],
                ['key' => 'header_cta_label', 'type' => 'bi_text', 'label' => 'Button text'],
                ['key' => 'header_cta_url', 'type' => 'text', 'label' => 'Button link', 'help' => 'Example: /#contact or /portfolio'],
                ['key' => 'header_work_dropdown', 'type' => 'toggle', 'label' => 'Show the "Our Work" dropdown (Completed / Running projects)', 'help' => 'Opens when a visitor hovers the Our Work menu item.'],
                ['key' => 'header_show_lang', 'type' => 'toggle', 'label' => 'Show the language button (EN / বাং)'],
                ['key' => 'header_show_theme', 'type' => 'toggle', 'label' => 'Show the light / dark button'],
                ['key' => 'header_menu', 'type' => 'repeater', 'label' => 'Main menu (also used by the footer "Quick links")', 'help' => 'Drag the ⠿ handle to reorder. Link examples: /  /#services  /portfolio  /insights', 'sub' => [
                    ['key' => 'label', 'type' => 'bi_text', 'label' => 'Menu text'],
                    ['key' => 'url', 'type' => 'text', 'label' => 'Link'],
                ]],
            ],
        ],
        'footer' => [
            'title' => 'Footer', 'icon' => 'cog',
            'fields' => [
                ['key' => 'footer_cta_show', 'type' => 'toggle', 'label' => 'Show the top call-to-action band'],
                ['key' => 'footer_cta_title', 'type' => 'bi_text', 'label' => 'Band – heading'],
                ['key' => 'footer_cta_text', 'type' => 'bi_text', 'label' => 'Band – text'],
                ['key' => 'footer_about', 'type' => 'bi_textarea', 'label' => 'Short description under the logo', 'rows' => 3],
                ['key' => 'footer_show_social', 'type' => 'toggle', 'label' => 'Show the social icons'],
                ['key' => 'footer_show_links', 'type' => 'toggle', 'label' => 'Show the "Quick links" column'],
                ['key' => 'footer_show_services', 'type' => 'toggle', 'label' => 'Show the "Our services" column'],
                ['key' => 'footer_copyright', 'type' => 'bi_text', 'label' => 'Copyright line', 'help' => '{year} is replaced by the current year.'],
                ['key' => 'credit_text', 'type' => 'bi_text', 'label' => 'Credit – text', 'group' => 'Developer credit (always shown on every page)', 'developer_only' => true],
                ['key' => 'credit_name', 'type' => 'bi_text', 'label' => 'Credit – name', 'group' => 'Developer credit (always shown on every page)', 'developer_only' => true],
                ['key' => 'credit_url', 'type' => 'url', 'label' => 'Credit – link', 'group' => 'Developer credit (always shown on every page)', 'developer_only' => true],
            ],
        ],
        'clients' => [
            'title' => 'Homepage clients section', 'icon' => 'cog', 'feature' => 'clients',
            'fields' => [
                ['key' => 'clients_title', 'type' => 'bi_text', 'label' => 'Section heading'],
                ['key' => 'clients_subtitle', 'type' => 'bi_text', 'label' => 'Section subheading'],
                ['key' => 'clients_home_limit', 'type' => 'number', 'label' => 'Most clients shown on the homepage', 'help' => 'Only clients marked "Show on homepage" appear, up to this number (1–24). Default 10.'],
            ],
        ],
        'theme' => [
            'title' => 'Theme colours', 'icon' => 'palette',
            'fields' => [
                ['key' => 'theme_default', 'type' => 'select', 'label' => 'Default theme', 'options' => ['light' => 'Light', 'dark' => 'Dark'], 'help' => 'What a new visitor sees first. Visitors can still switch with the button in the header.'],
                ['key' => 'theme_light_primary', 'type' => 'color', 'label' => 'Light theme – brand colour', 'group' => 'Light theme'],
                ['key' => 'theme_light_bg', 'type' => 'color', 'label' => 'Light theme – page background', 'group' => 'Light theme'],
                ['key' => 'theme_light_surface', 'type' => 'color', 'label' => 'Light theme – cards / header', 'group' => 'Light theme'],
                ['key' => 'theme_light_text', 'type' => 'color', 'label' => 'Light theme – text', 'group' => 'Light theme'],
                ['key' => 'theme_dark_primary', 'type' => 'color', 'label' => 'Dark theme – brand colour', 'group' => 'Dark theme'],
                ['key' => 'theme_dark_bg', 'type' => 'color', 'label' => 'Dark theme – page background', 'group' => 'Dark theme'],
                ['key' => 'theme_dark_surface', 'type' => 'color', 'label' => 'Dark theme – cards / header', 'group' => 'Dark theme'],
                ['key' => 'theme_dark_text', 'type' => 'color', 'label' => 'Dark theme – text', 'group' => 'Dark theme'],
            ],
        ],
        'code' => [
            'title' => 'Custom code (all pages)', 'icon' => 'code',
            'intro' => 'Added to every page. For one page only, use Pages & SEO or the SEO tab of a project / insight. Code is printed exactly as typed, so only paste code you trust.',
            'fields' => [
                ['key' => 'code_head', 'type' => 'code', 'label' => 'Inside <head>', 'help' => 'Meta tags, fonts, tracking scripts, verification tags …'],
                ['key' => 'code_css', 'type' => 'code', 'label' => 'Custom CSS', 'help' => 'Plain CSS, no <style> tag needed. Loaded last, so it overrides the theme.'],
                ['key' => 'code_body_start', 'type' => 'code', 'label' => 'Right after <body>', 'help' => 'For example the Google Tag Manager <noscript> block.'],
                ['key' => 'code_body_end', 'type' => 'code', 'label' => 'Before </body>', 'help' => 'Chat widgets, pixels …'],
                ['key' => 'code_js', 'type' => 'code', 'label' => 'Custom JavaScript', 'help' => 'Plain JavaScript, no <script> tag needed. Runs at the end of the page.'],
            ],
        ],
        'seo' => [
            'title' => 'SEO (global)', 'icon' => 'search',
            'fields' => [
                ['key' => 'seo_title_suffix', 'type' => 'bi_text', 'label' => 'Page title suffix', 'help' => 'Added after every page title, e.g. "Home | Adnan Architects & Engineers".'],
                ['key' => 'seo_title_separator', 'type' => 'text', 'label' => 'Title separator'],
                ['key' => 'seo_default_description', 'type' => 'bi_textarea', 'label' => 'Default meta description', 'help' => 'Used when a page has none of its own.'],
                ['key' => 'seo_default_og_image', 'type' => 'image', 'label' => 'Default social share image', 'help' => 'About 1200 × 630 px.'],
                ['key' => 'seo_twitter_handle', 'type' => 'text', 'label' => 'X / Twitter handle', 'help' => 'Example: @adnanarch'],
                ['key' => 'seo_google_verification', 'type' => 'text', 'label' => 'Google Search Console verification code'],
                ['key' => 'seo_bing_verification', 'type' => 'text', 'label' => 'Bing Webmaster verification code'],
                ['key' => 'seo_ga_id', 'type' => 'text', 'label' => 'Google Analytics 4 ID', 'help' => 'G-XXXXXXXXXX'],
                ['key' => 'seo_gtm_id', 'type' => 'text', 'label' => 'Google Tag Manager ID', 'help' => 'GTM-XXXXXXX'],
                ['key' => 'seo_fb_pixel_id', 'type' => 'text', 'label' => 'Facebook Pixel ID'],
                ['key' => 'seo_org_schema', 'type' => 'toggle', 'label' => 'Add business structured data (schema.org) to every page'],
                ['key' => 'seo_sitemap_enabled', 'type' => 'toggle', 'label' => 'Serve /sitemap.xml'],
                ['key' => 'seo_robots_txt', 'type' => 'code', 'label' => 'robots.txt', 'help' => 'The Sitemap line is added automatically.'],
            ],
        ],
    ],

    /* ------------------------------------------------------------ SEO block shared by pages, projects, insights */
    'seo_fields' => [
        ['key' => 'meta_title', 'type' => 'bi_text', 'label' => 'Meta title', 'help' => 'Best under 60 characters. The site suffix is added automatically.'],
        ['key' => 'meta_description', 'type' => 'bi_textarea', 'label' => 'Meta description', 'help' => 'Best 120–160 characters.', 'rows' => 3],
        ['key' => 'meta_keywords', 'type' => 'bi_text', 'label' => 'Meta keywords', 'help' => 'Comma separated (optional).'],
        ['key' => 'canonical_url', 'type' => 'url', 'label' => 'Canonical URL', 'help' => 'Leave empty to use the page address.'],
        ['key' => 'robots_index', 'type' => 'toggle', 'label' => 'Allow search engines to index this page'],
        ['key' => 'robots_follow', 'type' => 'toggle', 'label' => 'Allow search engines to follow links'],
        ['key' => 'robots_extra', 'type' => 'text', 'label' => 'Extra robots directives', 'help' => 'Example: noarchive, nosnippet'],
        ['key' => 'og_title', 'type' => 'bi_text', 'label' => 'Social share title', 'help' => 'Empty = meta title.'],
        ['key' => 'og_description', 'type' => 'bi_textarea', 'label' => 'Social share description', 'rows' => 2],
        ['key' => 'og_image', 'type' => 'image', 'label' => 'Social share image'],
        ['key' => 'og_type', 'type' => 'select', 'label' => 'Open Graph type', 'options' => ['website' => 'website', 'article' => 'article']],
        ['key' => 'twitter_card', 'type' => 'select', 'label' => 'Twitter card', 'options' => ['summary_large_image' => 'Large image', 'summary' => 'Summary']],
        ['key' => 'schema_json', 'type' => 'code', 'label' => 'Extra structured data (JSON-LD)', 'help' => 'Paste a JSON object only, without <script> tags.'],
        ['key' => 'head_code', 'type' => 'code', 'label' => 'Custom code inside <head> (this page only)'],
        ['key' => 'custom_css', 'type' => 'code', 'label' => 'Custom CSS (this page only)'],
        ['key' => 'body_start_code', 'type' => 'code', 'label' => 'Custom code right after <body> (this page only)'],
        ['key' => 'body_end_code', 'type' => 'code', 'label' => 'Custom code before </body> (this page only)'],
        ['key' => 'custom_js', 'type' => 'code', 'label' => 'Custom JavaScript (this page only)'],
    ],

    /* ------------------------------------------------------------ content lists */
    'resources' => [

        'clients' => [
            'model' => \App\Models\Client::class, 'title' => 'Clients', 'single' => 'client', 'icon' => 'users', 'feature' => 'clients', 'searchable' => true,
            'columns' => ['logo' => 'image', 'name' => 'bi', 'phone' => 'text', 'show_on_home' => 'bool', 'active' => 'bool'],
            'fields' => [
                ['key' => 'name', 'type' => 'bi_text', 'label' => 'Client name', 'required' => true],
                ['key' => 'address', 'type' => 'textarea', 'label' => 'Address (private – never shown on the website)', 'rows' => 3],
                ['key' => 'notes', 'type' => 'textarea', 'label' => 'Notes (private – never shown on the website)', 'rows' => 4],
                ['key' => 'logo', 'type' => 'image', 'label' => 'Logo', 'help' => 'Shown on the homepage. A transparent PNG works best.'],
                ['key' => 'show_on_home', 'type' => 'toggle', 'label' => 'Show on homepage'],
                ['key' => 'website', 'type' => 'url', 'label' => 'Website (the homepage logo links to it)'],
                ['key' => 'contact_person', 'type' => 'text', 'label' => 'Contact person (private)'],
                ['key' => 'phone', 'type' => 'text', 'label' => 'Phone (private)'],
                ['key' => 'email', 'type' => 'text', 'label' => 'Email (private)'],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order', 'help' => 'Smaller shows first. You can also drag rows in the list.'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Active client'],
            ],
        ],

        'hero-slides' => [
            'model' => \App\Models\HeroSlide::class, 'title' => 'Hero sliders', 'single' => 'hero slide', 'icon' => 'image',
            'columns' => ['title1' => 'bi', 'image' => 'image', 'sort' => 'text', 'active' => 'bool'],
            // slider-wide options, shown above the slide list. "styles" = which slider style an option belongs to.
            'style_key' => 'hero_style',
            'config' => [
                ['key' => 'hero_style', 'type' => 'select', 'label' => 'Slider style', 'options' => ['v1' => 'Slider v1 – full-page sliding background image', 'v2' => 'Slider v2 – text + image card with statistics'], 'help' => 'Save, then edit the slides: their fields change with the style.'],
                ['key' => 'hero_marquee', 'type' => 'toggle', 'label' => 'Show the red services band under the hero', 'help' => 'The scrolling list of services, between the hero and the intro video. On by default.'],
                ['key' => 'hero_autoplay', 'type' => 'toggle', 'label' => 'Autoplay'],
                ['key' => 'hero_interval', 'type' => 'number', 'label' => 'Slide time (milliseconds)', 'help' => '6000 = 6 seconds.'],
                ['key' => 'hero_pause_on_hover', 'type' => 'toggle', 'label' => 'Pause on hover'],
                ['key' => 'hero_columns', 'type' => 'number', 'label' => 'Reveal columns (2–8)', 'help' => 'Vertical strips the image slides in with. Default 4.', 'styles' => ['v1']],
                ['key' => 'hero_overlay', 'type' => 'number', 'label' => 'Dark overlay (0–90 %)', 'help' => 'Makes the white text readable on photos. Default 45.', 'styles' => ['v1']],
                ['key' => 'hero_arrows', 'type' => 'toggle', 'label' => 'Show prev / next arrows', 'styles' => ['v1']],
                ['key' => 'hero_dots', 'type' => 'toggle', 'label' => 'Show dots', 'styles' => ['v1']],
            ],
            'fields' => [
                ['key' => 'badge', 'type' => 'bi_text', 'label' => 'Small badge text', 'styles' => ['v2']],
                ['key' => 'title1', 'type' => 'bi_text', 'label' => 'Heading – line 1', 'required' => true],
                ['key' => 'title2', 'type' => 'bi_text', 'label' => 'Heading – line 2', 'help' => 'v2 shows it coloured; v1 joins it to line 1.'],
                ['key' => 'text', 'type' => 'bi_textarea', 'label' => 'Paragraph'],
                ['key' => 'image', 'type' => 'image', 'label' => 'Slide image', 'labels' => ['v1' => 'Background image (full page)', 'v2' => 'Image (right side)'], 'helps' => ['v1' => 'Use a WIDE landscape photo, 16:9 – best size 1920 × 1080 px (minimum 1600 × 900). Portrait or square photos get zoomed and cropped on a wide screen.', 'v2' => 'Use a 4:3 photo – best size 1200 × 900 px (minimum 800 × 600). The picture box is 4:3, so other shapes are cropped (use "Focus point" to choose what stays visible).']],
                ['key' => 'image_fit', 'type' => 'select', 'label' => 'Image fit', 'options' => ['cover' => 'Cover (photo, fills the frame)', 'contain' => 'Contain (drawing, never cropped)'], 'styles' => ['v2']],
                ['key' => 'image_pos', 'type' => 'text', 'label' => 'Focus point (crop position)', 'help' => 'Which part of the picture stays visible when it is cropped. Examples: center, "center top", "center 30%", "left center". Default: center.'],
                ['key' => 'cta_label', 'type' => 'bi_text', 'label' => 'Button text'],
                ['key' => 'cta_url', 'type' => 'text', 'label' => 'Button link', 'help' => 'Example: /portfolio or /#contact'],
                ['key' => 'chip_icon', 'type' => 'text', 'label' => 'Floating card – emoji', 'styles' => ['v2']],
                ['key' => 'chip_title', 'type' => 'bi_text', 'label' => 'Floating card – title', 'styles' => ['v2']],
                ['key' => 'chip_sub', 'type' => 'bi_text', 'label' => 'Floating card – subtitle', 'styles' => ['v2']],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order', 'help' => 'Smaller shows first.'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Visible on the website'],
            ],
        ],

        'services' => [
            'model' => \App\Models\Service::class, 'title' => 'Services', 'single' => 'service', 'icon' => 'wrench',
            'columns' => ['title' => 'bi', 'icon' => 'text', 'sort' => 'text', 'active' => 'bool'],
            'fields' => [
                ['key' => 'title', 'type' => 'bi_text', 'label' => 'Title', 'required' => true],
                ['key' => 'description', 'type' => 'bi_textarea', 'label' => 'Short description'],
                ['key' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => $icons],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Visible on the website'],
            ],
        ],

        'steps' => [
            'model' => \App\Models\ProcessStep::class, 'title' => 'Process steps', 'single' => 'step', 'icon' => 'list',
            'columns' => ['title' => 'bi', 'sort' => 'text', 'active' => 'bool'],
            'fields' => [
                ['key' => 'title', 'type' => 'bi_text', 'label' => 'Title', 'required' => true],
                ['key' => 'description', 'type' => 'bi_textarea', 'label' => 'Description'],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Visible on the website'],
            ],
        ],

        'why-us' => [
            'model' => \App\Models\WhyItem::class, 'title' => 'Why choose us', 'single' => 'point', 'icon' => 'check',
            'columns' => ['title' => 'bi', 'sort' => 'text', 'active' => 'bool'],
            'fields' => [
                ['key' => 'title', 'type' => 'bi_text', 'label' => 'Title', 'required' => true],
                ['key' => 'description', 'type' => 'bi_textarea', 'label' => 'Description'],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Visible on the website'],
            ],
        ],

        'faqs' => [
            'model' => \App\Models\Faq::class, 'title' => 'FAQs', 'single' => 'FAQ', 'icon' => 'question',
            'columns' => ['question' => 'bi', 'sort' => 'text', 'active' => 'bool'],
            'fields' => [
                ['key' => 'question', 'type' => 'bi_text', 'label' => 'Question', 'required' => true],
                ['key' => 'answer', 'type' => 'bi_textarea', 'label' => 'Answer'],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Visible on the website'],
            ],
        ],

        'testimonials' => [
            'model' => \App\Models\Testimonial::class, 'title' => 'Testimonials', 'single' => 'testimonial', 'icon' => 'chat',
            'columns' => ['name' => 'bi', 'type' => 'text', 'sort' => 'text', 'active' => 'bool'],
            'fields' => [
                ['key' => 'type', 'type' => 'select', 'label' => 'Type', 'options' => ['text' => 'Text only', 'image' => 'With a photo / screenshot', 'video' => 'With a video']],
                ['key' => 'name', 'type' => 'bi_text', 'label' => 'Client name', 'required' => true],
                ['key' => 'role', 'type' => 'bi_text', 'label' => 'Project / area'],
                ['key' => 'text', 'type' => 'bi_textarea', 'label' => 'Feedback'],
                ['key' => 'rating', 'type' => 'number', 'label' => 'Stars (1–5)'],
                ['key' => 'avatar', 'type' => 'image', 'label' => 'Client photo (small, optional)'],
                ['key' => 'image', 'type' => 'image', 'label' => 'Photo / message screenshot', 'help' => 'For type "photo / screenshot".'],
                ['key' => 'video_url', 'type' => 'text', 'label' => 'Video link', 'help' => 'YouTube or Facebook link. For type "video".'],
                ['key' => 'poster', 'type' => 'image', 'label' => 'Video cover image'],
                ['key' => 'is_sample', 'type' => 'toggle', 'label' => 'Mark as "Sample" (turn OFF once it is a real client)'],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Visible on the website'],
            ],
        ],

        'activities' => [
            'model' => \App\Models\Activity::class, 'title' => 'Activities gallery', 'single' => 'photo', 'icon' => 'camera',
            'columns' => ['image' => 'image', 'title' => 'bi', 'sort' => 'text', 'active' => 'bool'],
            'fields' => [
                ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'required' => true],
                ['key' => 'title', 'type' => 'bi_text', 'label' => 'Caption'],
                ['key' => 'note', 'type' => 'bi_text', 'label' => 'Note (shown under the enlarged photo)'],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Visible on the website'],
            ],
        ],

        'projects' => [
            'model' => \App\Models\Project::class, 'title' => 'Projects', 'single' => 'project', 'icon' => 'building', 'seo' => true, 'view_url' => '/project/{slug}',
            'columns' => ['cover' => 'image', 'title' => 'bi', 'category' => 'bi', 'status' => 'text', 'sort' => 'text', 'active' => 'bool'],
            'fields' => [
                ['key' => 'title', 'type' => 'bi_text', 'label' => 'Title', 'required' => true],
                ['key' => 'slug', 'type' => 'text', 'label' => 'URL name (slug)', 'help' => 'Letters, numbers and dashes. Becomes /project/<slug>. Leave empty to create it from the English title.'],
                ['key' => 'status', 'type' => 'select', 'label' => 'Main category', 'options' => ['completed' => 'Completed project', 'running' => 'Running project (work in progress)'], 'help' => 'Decides which Our Work page the project appears on: Completed Projects or Running Projects.'],
                ['key' => 'progress', 'type' => 'number', 'label' => 'Progress % (running projects)', 'help' => 'Optional, 0–100. Shown as a progress bar on running projects.'],
                ['key' => 'category', 'type' => 'bi_text', 'label' => 'Category (used by the filter)'],
                ['key' => 'tagline', 'type' => 'bi_text', 'label' => 'Tagline (on the card)'],
                ['key' => 'summary', 'type' => 'bi_textarea', 'label' => 'Summary (under the page heading)'],
                ['key' => 'cover', 'type' => 'image', 'label' => 'Cover image', 'required' => true],
                ['key' => 'cover_pos', 'type' => 'text', 'label' => 'Cover position', 'help' => 'Optional, e.g. "center 38%".'],
                ['key' => 'meta', 'type' => 'bi_pairs', 'label' => 'Project info (sidebar)', 'help' => 'One per line: Label | Value'],
                ['key' => 'description', 'type' => 'bi_lines', 'label' => 'Description paragraphs', 'help' => 'One paragraph per line.'],
                ['key' => 'highlights', 'type' => 'bi_lines', 'label' => 'Highlights', 'help' => 'One per line.'],
                ['key' => 'images', 'type' => 'gallery', 'label' => 'Drawings / images'],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Visible on the website'],
            ],
        ],

        'insights' => [
            'model' => \App\Models\Insight::class, 'title' => 'Insights (blog)', 'single' => 'insight', 'icon' => 'book', 'seo' => true, 'view_url' => '/insight/{slug}',
            'columns' => ['cover' => 'image', 'title' => 'bi', 'category' => 'bi', 'published_at' => 'text', 'active' => 'bool'],
            'fields' => [
                ['key' => 'title', 'type' => 'bi_text', 'label' => 'Title', 'required' => true],
                ['key' => 'slug', 'type' => 'text', 'label' => 'URL name (slug)', 'help' => 'Becomes /insight/<slug>. Leave empty to create it from the English title.'],
                ['key' => 'category', 'type' => 'bi_text', 'label' => 'Category'],
                ['key' => 'excerpt', 'type' => 'bi_textarea', 'label' => 'Short summary'],
                ['key' => 'body', 'type' => 'bi_html', 'label' => 'Article (HTML)', 'help' => 'Use <h2>, <p>, <ul><li>, <strong>, <a href>, <img src>.', 'rows' => 14],
                ['key' => 'read_time', 'type' => 'bi_text', 'label' => 'Reading time', 'help' => 'Example: 3 min / ৩ মিনিট'],
                ['key' => 'cover', 'type' => 'image', 'label' => 'Cover image'],
                ['key' => 'cover_fit', 'type' => 'select', 'label' => 'Cover fit', 'options' => ['cover' => 'Cover (photo)', 'contain' => 'Contain (drawing)']],
                ['key' => 'published_at', 'type' => 'date', 'label' => 'Publish date'],
                ['key' => 'sort', 'type' => 'number', 'label' => 'Order'],
                ['key' => 'active', 'type' => 'toggle', 'label' => 'Visible on the website'],
            ],
        ],
    ],
];
