<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DEMO ONLY - sample departments, roles and people to try the permission system with.
 *   php artisan db:seed --class=DemoStaffSeeder      (safe to run again; password for everybody: password123)
 * Do not run on the live site. Roles/users it creates can be edited or deleted in the admin like any other.
 */
class DemoStaffSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [];
        foreach (['Management', 'Design & Architecture', 'Accounts', 'Content & Marketing', 'Human Resources'] as $name) {
            $departments[$name] = Department::firstOrCreate(['name' => $name]);
        }

        // slug => [name, description, permissions]
        $all = fn (string $prefix, string $action = 'manage') => collect(\App\Support\Permissions::all())
            ->filter(fn ($p) => str_starts_with($p, $prefix) && str_ends_with($p, '.'.$action))->values()->all();

        $roles = [
            'admin' => ['Admin', 'Runs the whole office. Everything except changing roles.',
                array_merge($all('website.'), $all('crm.'), $all('hr.'), ['messages.manage', 'users.manage', 'departments.manage', 'roles.view'])],
            'hr' => ['HR', 'People: shifts, attendance, leave, holidays, users.',
                ['hr.shifts.manage', 'hr.attendance.manage', 'hr.leave.manage', 'hr.holidays.manage', 'hr.tasks.view', 'hr.payroll.view', 'users.manage', 'departments.manage']],
            'manager' => ['Manager', 'Leads a team: shifts, attendance, leave approval, tasks.',
                ['hr.shifts.manage', 'hr.attendance.manage', 'hr.leave.manage', 'hr.tasks.manage', 'hr.holidays.view', 'website.works.view', 'crm.clients.view', 'users.view']],
            'accountant' => ['Accountant', 'Salary and money.',
                ['hr.payroll.manage', 'hr.attendance.view', 'hr.leave.view', 'hr.holidays.view', 'crm.clients.view']],
            'content-manager' => ['Content Manager', 'Writes and updates the website.',
                array_merge($all('website.'), ['messages.manage'])],
            'architect' => ['Architect', 'Design staff: manages the Our Work projects, sees own tasks.',
                ['website.works.manage', 'hr.tasks.view', 'hr.holidays.view']],
        ];

        $made = [];
        foreach ($roles as $slug => [$name, $desc, $perms]) {
            $role = Role::firstOrNew(['slug' => $slug]);
            $role->fill(['name' => $name, 'description' => $desc, 'permissions' => array_values(array_intersect(array_unique($perms), \App\Support\Permissions::all()))]);
            $role->slug = $slug;
            $role->save();
            $made[$slug] = $role;
        }

        // Saturday to Thursday, Friday off
        $day = Shift::firstOrCreate(['name' => 'Day shift'], ['starts_at' => '10:00', 'ends_at' => '18:00', 'grace_minutes' => 10, 'work_days' => [1, 2, 3, 4, 6, 7]]);

        // email => [name, role, department]
        $people = [
            'admin@adnan.test' => ['Karim Admin', 'admin', 'Management'],
            'hr@adnan.test' => ['Nusrat HR', 'hr', 'Human Resources'],
            'manager@adnan.test' => ['Rafiq Manager', 'manager', 'Management'],
            'accountant@adnan.test' => ['Salma Accountant', 'accountant', 'Accounts'],
            'content@adnan.test' => ['Tania Content', 'content-manager', 'Content & Marketing'],
            'architect1@adnan.test' => ['Anik Architect', 'architect', 'Design & Architecture'],
            'architect2@adnan.test' => ['Mitu Architect', 'architect', 'Design & Architecture'],
        ];
        foreach ($people as $email => [$name, $role, $dept]) {
            $u = User::firstOrNew(['email' => $email]);
            $u->name = $name;
            if (! $u->exists) {
                $u->password = 'password123';
            }
            $u->save();
            // the Admin does not clock in; everybody else works the day shift from today
            $u->forceFill(['role_id' => $made[$role]->id, 'department_id' => $departments[$dept]->id, 'attendance_required' => $role !== 'admin'])->save();
            if ($role !== 'admin' && ! $u->shiftAssignments()->exists()) {
                ShiftAssignment::create(['user_id' => $u->id, 'shift_id' => $day->id, 'starts_on' => today()->subDays(30)]);
            }
        }
    }
}
