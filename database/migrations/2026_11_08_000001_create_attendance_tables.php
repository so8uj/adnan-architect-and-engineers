<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Phase 1 of docs/hr-plan.md: shifts, who works which shift, daily attendance, and the per-user "needs attendance" switch. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->time('starts_at');
            $t->time('ends_at');
            $t->unsignedSmallInteger('grace_minutes')->default(10);   // minutes after start that still count as on time
            $t->json('work_days');                                    // ISO weekdays 1 (Mon) .. 7 (Sun)
            $t->timestamps();
        });

        Schema::create('shift_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $t->date('starts_on');
            $t->timestamps();
            $t->index(['user_id', 'starts_on']);
        });

        Schema::create('attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->dateTime('clock_in')->nullable();
            $t->dateTime('clock_out')->nullable();
            $t->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedSmallInteger('late_minutes')->default(0);
            $t->unsignedSmallInteger('worked_minutes')->default(0);
            $t->string('note')->nullable();
            $t->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'date']);
        });

        Schema::table('users', function (Blueprint $t) {
            $t->boolean('attendance_required')->default(true)->after('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('attendance_required'));
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('shifts');
    }
};
