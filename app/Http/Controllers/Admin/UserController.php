<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin user management (with department + role). The developer account is invisible here for everybody except the
 * developer: it is left out of the list and a direct link to it answers "not found".
 */
class UserController extends Controller
{
    private function visible(Request $request)
    {
        return User::query()->visibleTo($request->user());
    }

    /** Roles the signed-in admin may hand out: Super Admin any; everybody else only roles that grant nothing they lack. */
    private function assignableRoles(Request $request)
    {
        $me = $request->user();

        return Role::orderBy('name')->get()
            ->filter(fn (Role $r) => $me->isSuper() || (! $r->isSuper() && collect($r->permissions)->every(fn ($p) => $me->hasPermission($p))))
            ->values();
    }

    private function options(Request $request): array
    {
        return ['roles' => $this->assignableRoles($request), 'departments' => Department::orderBy('name')->get()];
    }

    /** Validated department / role. Nobody changes their own role (no self-promotion, no locking yourself out). */
    private function relations(Request $request, ?User $target = null): array
    {
        $data = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'role_id' => ['nullable', Rule::in($this->assignableRoles($request)->pluck('id')->all())],
        ]);
        if ($target && $target->id === $request->user()->id) {
            unset($data['role_id']);
        }
        // "Needs attendance" - the form always sends 0 or 1; other callers that leave it out change nothing
        if ($request->has('attendance_required')) {
            $data['attendance_required'] = $request->boolean('attendance_required');
        }

        return $data;
    }

    public function index(Request $request)
    {
        return view('admin.users.index', ['users' => $this->visible($request)->with(['role', 'department'])->orderBy('id')->get()]);
    }

    public function create(Request $request)
    {
        return view('admin.users.form', ['user' => new User] + $this->options($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $rel = $this->relations($request);

        User::create($data)->forceFill($rel)->save();

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(Request $request, int $user)
    {
        return view('admin.users.form', ['user' => $this->visible($request)->findOrFail($user)] + $this->options($request));
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $model = $this->visible($request)->findOrFail($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($model->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $rel = $this->relations($request, $model);

        // a Super Admin can only be edited (role-wise) by another Super Admin
        if (! $request->user()->isSuper() && $model->isSuper()) {
            unset($rel['role_id']);
        }

        $model->update($data);
        $model->forceFill($rel)->save();

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, int $user): RedirectResponse
    {
        $model = $this->visible($request)->findOrFail($user);

        if ($model->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You can not delete the account you are signed in with.']);
        }
        if ($model->is_developer) {
            return back()->withErrors(['user' => 'The developer account can not be deleted.']);
        }
        if ($model->isSuper() && ! $request->user()->isSuper()) {
            return back()->withErrors(['user' => 'Only a Super Admin can delete a Super Admin.']);
        }

        $model->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }
}
