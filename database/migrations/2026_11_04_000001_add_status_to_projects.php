<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Our Work has two main categories: completed projects and running (work in progress) projects. */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('status', 20)->default('completed')->after('slug')->index();
            $table->unsignedTinyInteger('progress')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['status', 'progress']);
        });
    }
};
