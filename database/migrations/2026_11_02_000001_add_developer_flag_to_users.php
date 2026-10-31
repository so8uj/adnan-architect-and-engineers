<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The developer account is a normal user with is_developer = true. It is hidden from every other user's list and is the
 * only one that can edit the footer credit. The account that was seeded earlier (admin@adnan.test) becomes the developer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_developer')->default(false)->after('password');
        });

        DB::table('users')->where('email', 'admin@adnan.test')->update([
            'name' => 'Sobuj',
            'email' => 'developer@sobuj.com',
            'is_developer' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn('is_developer');
        });
    }
};
