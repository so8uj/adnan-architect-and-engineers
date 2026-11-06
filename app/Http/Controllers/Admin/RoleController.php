<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Roles and what each may do. Super Admin is a protected system role. */
class RoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', ['roles' => Role::withCount('users')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.roles.form', ['role' => new Role(['permissions' => []]), 'catalogue' => Permissions::catalogue()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $role = new Role($data);
        $role->slug = $this->slug($data['name']);
        $role->save();

        return redirect()->route('admin.roles.index')->with('status', 'Role created.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.form', ['role' => $role, 'catalogue' => Permissions::catalogue()]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403, 'The Super Admin role can not be changed.');
        $role->update($this->validated($request));

        return redirect()->route('admin.roles.index')->with('status', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->withErrors(['role' => 'The Super Admin role can not be deleted.']);
        }
        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'This role is still given to users. Move them to another role first.']);
        }
        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::all())],
        ]);
        $data['permissions'] = array_values(array_unique($data['permissions'] ?? []));

        // you can not hand out more than you have yourself
        $me = $request->user();
        abort_unless(collect($data['permissions'])->every(fn ($p) => $me->hasPermission($p)), 403, 'You can only give permissions you have yourself.');

        return $data;
    }

    private function slug(string $name): string
    {
        $base = str($name)->slug()->toString() ?: 'role';
        $slug = $base;
        for ($i = 2; Role::where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
