<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Departments + roles (with their permissions) and the two columns that attach them to a user.
 * "Super Admin" is a protected system role with every permission; everybody who exists today becomes a Super Admin so
 * nobody is locked out when this is deployed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('description')->nullable();
            $t->timestamps();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('slug', 120)->unique();
            $t->string('description')->nullable();
            $t->boolean('is_system')->default(false);
            $t->json('permissions')->nullable();
            $t->timestamps();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('department_id')->nullable()->after('is_developer')->constrained('departments')->nullOnDelete();
            $t->foreignId('role_id')->nullable()->after('department_id')->constrained('roles')->nullOnDelete();
        });

        $id = DB::table('roles')->insertGetId([
            'name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access to everything. Protected - can not be edited or deleted.',
            'is_system' => true, 'permissions' => json_encode([]), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('users')->update(['role_id' => $id]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('role_id');
            $t->dropConstrainedForeignId('department_id');
        });
        Schema::dropIfExists('roles');
        Schema::dropIfExists('departments');
    }
};
