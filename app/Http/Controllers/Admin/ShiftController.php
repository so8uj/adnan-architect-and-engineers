<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Phase 1 - shifts, and which shift each person works. */
class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $users = User::visibleTo($request->user())->where('attendance_required', true)->with('department')->orderBy('name')->get();

        return view('admin.shifts.index', [
            'shifts' => Shift::withCount('assignments')->orderBy('starts_at')->get(),
            'people' => $users->map(fn (User $u) => ['user' => $u, 'shift' => $u->shiftOn()]),
            'canManage' => $request->user()->hasPermission('hr.shifts.manage'),
        ]);
    }

    public function create()
    {
        return view('admin.shifts.form', ['shift' => new Shift(['starts_at' => '09:00', 'ends_at' => '17:00', 'grace_minutes' => 10, 'work_days' => [1, 2, 3, 4, 5, 6]])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Shift::create($this->data($request));

        return redirect()->route('admin.shifts.index')->with('status', 'Shift created.');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.form', ['shift' => $shift]);
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $shift->update($this->data($request));

        return redirect()->route('admin.shifts.index')->with('status', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        if ($shift->assignments()->exists()) {
            return back()->withErrors(['shift' => 'People are still on this shift. Give them another shift first.']);
        }
        $shift->delete();

        return redirect()->route('admin.shifts.index')->with('status', 'Shift deleted.');
    }

    /** Give people a shift from a date on (their earlier shifts stay as history). */
    public function assign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shift_id' => ['required', 'exists:shifts,id'],
            'starts_on' => ['required', 'date'],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['integer', 'exists:users,id'],
        ]);
        $ids = User::visibleTo($request->user())->whereIn('id', $data['users'])->pluck('id');
        foreach ($ids as $id) {
            ShiftAssignment::updateOrCreate(['user_id' => $id, 'starts_on' => $data['starts_on']], ['shift_id' => $data['shift_id']]);
        }

        return back()->with('status', $ids->count().' '.str('person')->plural($ids->count()).' moved to the shift.');
    }

    private function data(Request $request): array
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'work_days' => ['required', 'array', 'min:1'],
            'work_days.*' => ['integer', 'between:1,7'],
        ]);
        $d['work_days'] = array_values(array_unique(array_map('intval', $d['work_days'])));
        sort($d['work_days']);

        return $d;
    }
}
