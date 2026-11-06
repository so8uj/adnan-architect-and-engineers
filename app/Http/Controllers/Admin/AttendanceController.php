<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Phase 1 - clock in / out for everybody, the daily sheet + corrections for people with hr.attendance. */
class AttendanceController extends Controller
{
    /** Own page: clock button + this month's days. */
    public function me(Request $request)
    {
        $user = $request->user();
        $month = $this->month($request->query('month'));
        $rows = Attendance::where('user_id', $user->id)->whereBetween('date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->orderByDesc('date')->get();

        return view('admin.attendance.me', [
            'user' => $user, 'shift' => $user->shiftOn(), 'today' => Attendance::where('user_id', $user->id)->whereDate('date', today())->first(),
            'rows' => $rows, 'month' => $month,
            'totals' => ['days' => $rows->whereNotNull('clock_in')->count(), 'late' => $rows->where('late_minutes', '>', 0)->count(),
                'lateMinutes' => (int) $rows->sum('late_minutes'), 'hours' => round($rows->sum('worked_minutes') / 60, 1)],
        ]);
    }

    public function clockIn(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->attendance_required) {
            return back()->withErrors(['clock' => 'Attendance is not required for your account.']);
        }
        if (! $user->shiftOn()) {
            return back()->withErrors(['clock' => 'You have no shift yet. Ask HR or your manager to give you one.']);
        }
        if (Attendance::where('user_id', $user->id)->whereDate('date', today())->exists()) {
            return back()->withErrors(['clock' => 'You already clocked in today.']);
        }
        $a = Attendance::clockIn($user);

        return back()->with('status', 'Clocked in at '.$a->clock_in->format('h:i A').($a->late_minutes ? " - {$a->late_minutes} minutes late." : '.'));
    }

    public function clockOut(Request $request): RedirectResponse
    {
        $a = Attendance::where('user_id', $request->user()->id)->whereDate('date', today())->first();
        if (! $a || $a->clock_out) {
            return back()->withErrors(['clock' => $a ? 'You already clocked out today.' : 'Clock in first.']);
        }
        $a->clockOut();

        return back()->with('status', 'Clocked out at '.$a->clock_out->format('h:i A').'. Worked '.intdiv($a->worked_minutes, 60).'h '.($a->worked_minutes % 60).'m.');
    }

    /** One day for everybody (needs hr.attendance.view). */
    public function index(Request $request)
    {
        $date = $this->day($request->query('date'));
        $users = User::visibleTo($request->user())->where('attendance_required', true)->with(['department', 'role'])->orderBy('name')->get();
        $records = Attendance::whereDate('date', $date)->get()->keyBy('user_id');

        $rows = $users->map(function (User $u) use ($records, $date) {
            $rec = $records->get($u->id);
            $shift = $rec?->shift ?? $u->shiftOn($date);
            if ($rec && $rec->clock_in) {
                $status = $rec->late_minutes ? 'late' : 'present';
            } elseif (! $shift) {
                $status = 'noshift';
            } elseif (! $shift->worksOn($date)) {
                $status = 'off';
            } elseif ($date->isFuture()) {
                $status = 'upcoming';
            } elseif ($date->isToday() && now()->format('H:i') < $shift->end()) {
                $status = 'waiting';
            } else {
                $status = 'absent';
            }

            return ['user' => $u, 'rec' => $rec, 'shift' => $shift, 'status' => $status];
        });

        return view('admin.attendance.index', [
            'date' => $date, 'rows' => $rows,
            'sum' => ['present' => $rows->whereIn('status', ['present', 'late'])->count(), 'late' => $rows->where('status', 'late')->count(), 'absent' => $rows->where('status', 'absent')->count()],
            'canManage' => $request->user()->hasPermission('hr.attendance.manage'),
        ]);
    }

    /** Correction form for one person + day (needs hr.attendance.manage). */
    public function entry(Request $request)
    {
        $user = User::visibleTo($request->user())->findOrFail((int) $request->query('user'));
        $date = $this->day($request->query('date'));

        return view('admin.attendance.entry', ['target' => $user, 'date' => $date,
            'rec' => Attendance::where('user_id', $user->id)->whereDate('date', $date)->first(), 'shift' => $user->shiftOn($date)]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date'],
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after:clock_in'],
            'note' => ['nullable', 'string', 'max:255'],
            'remove' => ['nullable', 'boolean'],
        ]);
        $user = User::visibleTo($request->user())->findOrFail($data['user_id']);
        $date = Carbon::parse($data['date'])->startOfDay();
        $rec = Attendance::where('user_id', $user->id)->whereDate('date', $date)->first();
        $back = redirect()->route('admin.attendance.index', ['date' => $date->toDateString()]);

        if (! empty($data['remove'])) {
            $rec?->delete();

            return $back->with('status', 'Entry removed.');
        }
        $in = ! empty($data['clock_in']) ? $date->copy()->setTimeFromTimeString($data['clock_in']) : null;
        $out = ! empty($data['clock_out']) ? $date->copy()->setTimeFromTimeString($data['clock_out']) : null;
        $shift = $rec?->shift ?? $user->shiftOn($date);

        $rec ??= new Attendance(['user_id' => $user->id, 'date' => $date->toDateString()]);
        $rec->fill(['clock_in' => $in, 'clock_out' => $out, 'shift_id' => $shift?->id, 'note' => $data['note'] ?? null,
            'late_minutes' => $in ? Attendance::lateMinutes($shift, $in) : 0]);
        $rec->edited_by = $request->user()->id;
        $rec->recalc();

        return $back->with('status', 'Attendance saved.');
    }

    private function day(?string $v): Carbon
    {
        try {
            return $v ? Carbon::parse($v)->startOfDay() : today();
        } catch (\Throwable) {
            return today();
        }
    }

    private function month(?string $v): Carbon
    {
        try {
            return $v ? Carbon::createFromFormat('Y-m', $v)->startOfMonth() : now()->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }
}
