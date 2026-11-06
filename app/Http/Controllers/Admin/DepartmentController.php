<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    private function rules(): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:255']];
    }

    public function index()
    {
        return view('admin.departments.index', ['departments' => Department::withCount('users')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.departments.form', ['department' => new Department]);
    }

    public function store(Request $request): RedirectResponse
    {
        Department::create($request->validate($this->rules()));

        return redirect()->route('admin.departments.index')->with('status', 'Department created.');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.form', ['department' => $department]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($request->validate($this->rules()));

        return redirect()->route('admin.departments.index')->with('status', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();   // its people stay, just without a department (foreign key sets null)

        return redirect()->route('admin.departments.index')->with('status', 'Department deleted.');
    }
}
