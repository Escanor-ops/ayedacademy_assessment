<?php

namespace App\Http\Controllers\SuperManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::get();
        return view('super.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        Department::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Department added successfully.');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $department->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->back()->with('success', 'Department deleted successfully.');
    }
}
