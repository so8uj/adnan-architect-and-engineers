<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client list (future feature "clients"). Only name, logo and website can ever reach the public website;
 * phone, email, address and notes are private and will be reused by the quote / invoice features later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->json('name');
            $t->string('logo')->nullable();
            $t->string('website')->nullable();
            $t->string('contact_person')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->text('address')->nullable();
            $t->text('notes')->nullable();
            $t->boolean('show_on_home')->default(false);
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
