# HR & Attendance — plan (built one phase at a time)

Goal: a simple office system — **attendance, shifts, late, holidays, leave, daily tasks, salary** — where *everyone* gets the self-service basics
and only people whose **role** has the permission can manage them. All of it sits in the sidebar block **HR & Attendance** (`config/admin.php` → `hr`),
each phase switched on with its own `.env` flag (`FEATURE_*`, plus `FUTURE_OPTIONS_ENABLED=true`).

## Who can do what
| | Everybody (default, no permission) | Needs a role permission |
|---|---|---|
| Attendance | clock in / out, see own month | `hr.attendance` view = see everyone's sheet, manage = correct entries |
| Shifts | see own shift | `hr.shifts` manage = create shifts + give each user a shift |
| Leave | request leave, see own balance | `hr.leave` view/manage = see + approve/reject requests |
| Holidays | see the calendar | `hr.holidays` manage = add / edit holidays |
| Daily tasks | see + update own tasks | `hr.tasks` manage = assign tasks to people |
| Salary | see own payslips | `hr.payroll` manage = set salaries, run the month, publish payslips |

Per-user switch **"Needs attendance"** (user form): off for people who do not clock in (e.g. the owner/admin). They get no clock button, never show as absent
and are paid in full.

Demo data: `php artisan db:seed --class=DemoStaffSeeder` (Admin, HR, Manager, Accountant, Content Manager, 2 Architects — password `password123`).

## Phases
1. **Shifts + attendance** (`FEATURE_ATTENDANCE`) — shifts (start, end, grace minutes, work days), assign a shift per user (history kept), clock in / clock out,
   late minutes computed from the shift, "Needs attendance" switch, daily sheet for HR/managers with manual correction. *(built)*
2. **Holidays + leave** (`FEATURE_LEAVE`) — holiday calendar (public + weekend overrides), leave types with yearly allowance, request → approve/reject,
   balance; holidays/approved leave stop counting as absent.
3. **Daily tasks** (`FEATURE_TASKS`) — assign task (title, due date, priority) to a person, they mark todo / doing / done with a note, manager sees the team board.
4. **Payroll** (`FEATURE_PAYROLL`) — base salary + allowances/deductions per user, monthly run from attendance (absent days, late rule e.g. 3 lates = 1 day,
   unpaid leave), review, publish payslips (print / PDF). Currency BDT.
5. Reports — monthly attendance summary, late list, leave summary, CSV export.

## Technical rules
- A screen = a named route in `routes/admin.php`; self-service routes carry no permission, management routes carry `->middleware('perm:hr.xxx.manage')`
  (see `RequirePermission`) and the sidebar line needs its `route` to exist (unbuilt phases stay hidden automatically).
- Permissions are `<module>.view` / `<module>.manage`; modules come from `config/admin.php` → `hr` (`module` key) — nothing else to register.
- Times use the app timezone (Asia/Dhaka). One attendance row per user per day (`attendances`, unique user+date).
- Tests per phase in `tests/Feature/`.
