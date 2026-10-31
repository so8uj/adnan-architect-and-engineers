<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Everything the public site shows. Text that exists in two languages is stored as JSON: {"bn": "...", "en": "..."}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->longText('value')->nullable();
            $t->timestamps();
        });

        // static pages that only carry SEO / custom code (home, portfolio list, insights list)
        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->string('name');
            $t->timestamps();
        });

        // SEO + custom code, attached to a Page, Project or Insight
        Schema::create('seo_metas', function (Blueprint $t) {
            $t->id();
            $t->morphs('seoable');
            $t->json('meta_title')->nullable();
            $t->json('meta_description')->nullable();
            $t->json('meta_keywords')->nullable();
            $t->string('canonical_url')->nullable();
            $t->boolean('robots_index')->default(true);
            $t->boolean('robots_follow')->default(true);
            $t->string('robots_extra')->nullable();
            $t->json('og_title')->nullable();
            $t->json('og_description')->nullable();
            $t->string('og_image')->nullable();
            $t->string('og_type')->default('website');
            $t->string('twitter_card')->default('summary_large_image');
            $t->longText('schema_json')->nullable();
            $t->longText('head_code')->nullable();
            $t->longText('body_start_code')->nullable();
            $t->longText('body_end_code')->nullable();
            $t->longText('custom_css')->nullable();
            $t->longText('custom_js')->nullable();
            $t->timestamps();
        });

        Schema::create('hero_slides', function (Blueprint $t) {
            $t->id();
            $t->json('badge')->nullable();
            $t->json('title1')->nullable();
            $t->json('title2')->nullable();
            $t->json('text')->nullable();
            $t->string('image')->nullable();
            $t->string('image_fit')->default('cover');
            $t->string('image_pos')->nullable();
            $t->json('cta_label')->nullable();
            $t->string('cta_url')->nullable();
            $t->string('chip_icon')->nullable();
            $t->json('chip_title')->nullable();
            $t->json('chip_sub')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->json('title');
            $t->json('description')->nullable();
            $t->string('icon')->default('arch');
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('process_steps', function (Blueprint $t) {
            $t->id();
            $t->json('title');
            $t->json('description')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('why_items', function (Blueprint $t) {
            $t->id();
            $t->json('title');
            $t->json('description')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->json('question');
            $t->json('answer')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->string('type')->default('text');          // text | image | video
            $t->json('name');
            $t->json('role')->nullable();
            $t->json('text')->nullable();
            $t->unsignedTinyInteger('rating')->default(5);
            $t->string('avatar')->nullable();
            $t->string('image')->nullable();
            $t->string('video_url')->nullable();
            $t->string('poster')->nullable();
            $t->boolean('is_sample')->default(false);     // shows the "sample" tag
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('activities', function (Blueprint $t) {
            $t->id();
            $t->json('title')->nullable();
            $t->json('note')->nullable();
            $t->string('image');
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->json('title');
            $t->json('category')->nullable();
            $t->json('tagline')->nullable();
            $t->json('summary')->nullable();
            $t->string('cover')->nullable();
            $t->string('cover_pos')->nullable();
            $t->json('meta')->nullable();          // {"bn": [{"label","value"}], "en": [...]}
            $t->json('description')->nullable();   // {"bn": ["para", ...], "en": [...]}
            $t->json('highlights')->nullable();    // {"bn": ["..."], "en": [...]}
            $t->json('images')->nullable();        // [{"src", "title": {bn,en}, "size": {bn,en}, "note": {bn,en}}]
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('insights', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->json('title');
            $t->json('category')->nullable();
            $t->json('excerpt')->nullable();
            $t->json('body')->nullable();           // {"bn": "<html>", "en": "<html>"}
            $t->json('read_time')->nullable();
            $t->string('cover')->nullable();
            $t->string('cover_fit')->default('cover');
            $t->date('published_at')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('phone');
            $t->string('service')->nullable();
            $t->text('message')->nullable();
            $t->string('ip', 45)->nullable();
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['contact_messages', 'insights', 'projects', 'activities', 'testimonials', 'faqs', 'why_items', 'process_steps', 'services', 'hero_slides', 'seo_metas', 'pages', 'settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
