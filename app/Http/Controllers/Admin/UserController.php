<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin user management. The developer account is invisible here for everybody except the developer:
 * it is left out of the list and a direct link to it answers "not found".
 */
class UserController extends Controller
{
    private function visible(Request $request)
    {
        return User::query()->visibleTo($request->user());
    }

    public function index(Request $request)
    {
        return view('admin.users.index', ['users' => $this->visible($request)->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(Request $request, int $user)
    {
        return view('admin.users.form', ['user' => $this->visible($request)->findOrFail($user)]);
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

        $model->update($data);

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

        $model->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }
}
