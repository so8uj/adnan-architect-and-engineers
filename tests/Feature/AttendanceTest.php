<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
    }

    private function on(): void
    {
        config(['features.enabled' => true, 'features.modules.attendance' => true]);
    }

    private function person(array $perms = [], string $email = 'p@adnan.test'): User
    {
        $role = new Role(['name' => 'R '.$email, 'permissions' => $perms]);
        $role->slug = str($email)->slug()->toString();
        $role->save();
        $u = User::create(['name' => 'P '.$email, 'email' => $email, 'password' => 'password123']);
        $u->forceFill(['role_id' => $role->id])->save();

        return $u;
    }

    private function shift(array $over = []): Shift
    {
        return Shift::create($over + ['name' => 'Day', 'starts_at' => '10:00', 'ends_at' => '18:00', 'grace_minutes' => 10, 'work_days' => [1, 2, 3, 4, 5, 6, 7]]);
    }

    public function test_screens_are_hidden_while_the_feature_is_off(): void
    {
        $this->actingAs($this->person())->get('/admin/attendance/me')->assertNotFound();
    }

    public function test_everybody_can_clock_in_and_out_with_a_shift(): void
    {
        $this->on();
        $u = $this->person();
        $this->actingAs($u)->get('/admin/attendance/me')->assertOk()->assertSee('no shift yet');
        $this->post('/admin/attendance/clock-in')->assertSessionHasErrors('clock');

        ShiftAssignment::create(['user_id' => $u->id, 'shift_id' => $this->shift()->id, 'starts_on' => today()->subDay()]);
        Carbon::setTestNow(today()->setTime(10, 5));
        $this->post('/admin/attendance/clock-in')->assertSessionHas('status');
        $a = Attendance::firstOrFail();
        $this->assertSame(0, $a->late_minutes);                       // inside the 10 minute grace
        $this->post('/admin/attendance/clock-in')->assertSessionHasErrors('clock');   // only once a day

        Carbon::setTestNow(today()->setTime(18, 30));
        $this->post('/admin/attendance/clock-out')->assertSessionHas('status');
        $this->assertSame(505, $a->fresh()->worked_minutes);
        $this->get('/admin/attendance/me')->assertOk()->assertSee('Done for today');
        Carbon::setTestNow();
    }

    public function test_late_minutes_follow_the_shift_and_grace(): void
    {
        $this->on();
        $u = $this->person();
        ShiftAssignment::create(['user_id' => $u->id, 'shift_id' => $this->shift()->id, 'starts_on' => today()->subDay()]);
        Carbon::setTestNow(today()->setTime(10, 40));
        $this->actingAs($u)->post('/admin/attendance/clock-in');
        $this->assertSame(40, Attendance::firstOrFail()->late_minutes);
        Carbon::setTestNow();
    }

    public function test_people_who_do_not_need_attendance_cannot_clock_in_and_are_not_on_the_sheet(): void
    {
        $this->on();
        $u = $this->person(['hr.attendance.view']);
        $u->forceFill(['attendance_required' => false])->save();
        ShiftAssignment::create(['user_id' => $u->id, 'shift_id' => $this->shift()->id, 'starts_on' => today()->subDay()]);
        $this->actingAs($u)->post('/admin/attendance/clock-in')->assertSessionHasErrors('clock');
        $this->get('/admin/attendance')->assertOk()->assertViewHas('rows', fn ($rows) => $rows->isEmpty());
    }

    public function test_sheet_needs_permission_and_shows_absent_people(): void
    {
        $this->on();
        $absent = $this->person([], 'a@adnan.test');
        ShiftAssignment::create(['user_id' => $absent->id, 'shift_id' => $this->shift()->id, 'starts_on' => today()->subDays(5)]);
        $yesterday = today()->subDay()->toDateString();

        $this->actingAs($absent)->get('/admin/attendance')->assertForbidden();
        $this->actingAs($this->person(['hr.attendance.view'], 'v@adnan.test'));
        $this->get("/admin/attendance?date=$yesterday")->assertOk()->assertSee('P a@adnan.test')->assertSee('Absent');
        $this->get('/admin/attendance/entry?user='.$absent->id.'&date='.$yesterday)->assertForbidden();   // view is not manage
    }

    public function test_manager_can_correct_an_entry_and_late_is_recalculated(): void
    {
        $this->on();
        $u = $this->person([], 'w@adnan.test');
        ShiftAssignment::create(['user_id' => $u->id, 'shift_id' => $this->shift()->id, 'starts_on' => today()->subDays(5)]);
        $yesterday = today()->subDay()->toDateString();

        $this->actingAs($this->person(['hr.attendance.manage'], 'm@adnan.test'));
        $this->put('/admin/attendance/entry', ['user_id' => $u->id, 'date' => $yesterday, 'clock_in' => '10:30', 'clock_out' => '18:00', 'note' => 'forgot'])->assertRedirect();
        $a = Attendance::firstOrFail();
        $this->assertSame(30, $a->late_minutes);
        $this->assertSame(450, $a->worked_minutes);

        $this->put('/admin/attendance/entry', ['user_id' => $u->id, 'date' => $yesterday, 'remove' => 1])->assertRedirect();
        $this->assertSame(0, Attendance::count());
    }

    public function test_shifts_are_managed_by_permission_and_assignment_keeps_history(): void
    {
        $this->on();
        $worker = $this->person([], 'w@adnan.test');
        $this->actingAs($worker)->get('/admin/shifts')->assertForbidden();

        $this->actingAs($this->person(['hr.shifts.view'], 'v@adnan.test'))->get('/admin/shifts')->assertOk();
        $this->post('/admin/shifts', [])->assertForbidden();

        $this->actingAs($this->person(['hr.shifts.manage'], 'm@adnan.test'));
        $this->post('/admin/shifts', ['name' => 'Early', 'starts_at' => '08:00', 'ends_at' => '16:00', 'grace_minutes' => 5, 'work_days' => [1, 2, 3]])->assertRedirect();
        $this->post('/admin/shifts', ['name' => 'Bad', 'starts_at' => '18:00', 'ends_at' => '08:00', 'grace_minutes' => 5, 'work_days' => [1]])->assertSessionHasErrors('ends_at');
        $early = Shift::where('name', 'Early')->firstOrFail();
        $late = $this->shift(['name' => 'Late', 'starts_at' => '12:00', 'ends_at' => '20:00']);

        $this->post('/admin/shifts/assign', ['shift_id' => $early->id, 'starts_on' => today()->subDays(10)->toDateString(), 'users' => [$worker->id]])->assertRedirect();
        $this->post('/admin/shifts/assign', ['shift_id' => $late->id, 'starts_on' => today()->toDateString(), 'users' => [$worker->id]])->assertRedirect();
        $this->assertSame('Late', $worker->shiftOn()->name);
        $this->assertSame('Early', $worker->shiftOn(today()->subDays(3))->name);   // history kept
        $this->delete("/admin/shifts/{$early->id}")->assertSessionHasErrors('shift');   // still assigned
    }

    public function test_the_menu_shows_attendance_lines_by_permission(): void
    {
        $this->on();
        $html = $this->actingAs($this->person([], 'a@adnan.test'))->get('/admin')->getContent();
        $this->assertStringContainsString('HR &amp; Attendance', $html);
        $this->assertStringContainsString('My attendance', $html);
        $this->assertStringNotContainsString('Attendance sheet', $html);
        $this->assertStringNotContainsString('>Shifts<', $html);
        $html = $this->actingAs($this->person(['hr.attendance.view', 'hr.shifts.manage'], 'b@adnan.test'))->get('/admin')->getContent();
        $this->assertStringContainsString('Attendance sheet', $html);
        $this->assertStringContainsString('Shifts', $html);
    }
}
