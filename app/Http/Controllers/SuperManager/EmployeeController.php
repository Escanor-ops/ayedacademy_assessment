<?php

namespace App\Http\Controllers\SuperManager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department; // Make sure to import the Department model
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        // Get all employees with 'role' as 'employee' and get departments
        $employees = User::where('role', 'employee')
            ->with(['department', 'assignedBy'])
            ->paginate(20);
        $departments = Department::all(); 
        $assigners = User::whereIn('role', ['manager', 'department_manager'])
            ->where('status', 1)
            ->get();
        return view('super.employees.index', compact('employees', 'departments', 'assigners'));
    }

    public function create()
    {
        $departments = Department::all(); // Get all departments for the 'create' view
        return view('super.employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'position'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'department_id' => 'required|exists:departments,id', 
            'assigned_by' => 'nullable|exists:users,id',
        ]);

        // Validate that the assigner is a manager or department manager if provided
        if ($request->filled('assigned_by')) {
            $assigner = User::findOrFail($request->assigned_by);
            if (!in_array($assigner->role, ['manager', 'department_manager'])) {
                return back()->with('error', 'يمكن تعيين الموظف فقط بواسطة مدير أو مدير قسم.');
            }
        }

        // Create new employee
        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => bcrypt($request->password),
            'role'          => 'employee',
            'position'      => $request->position,
            'status'        => 1,
            'department_id' => $request->department_id,
            'assigned_by'   => $request->assigned_by ?: null,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'تم إضافة الموظف بنجاح');
    }

    public function edit($id)
    {
        $employee = User::findOrFail($id);
        $departments = Department::all(); // Get all departments for editing
        return view('super.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'position'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'department_id' => 'nullable|exists:departments,id', 
            'assigned_by' => 'nullable|exists:users,id',
        ]);

        // Validate that the assigner is a manager or department manager
        if ($request->filled('assigned_by')) {
            $assigner = User::findOrFail($request->assigned_by);
            if (!in_array($assigner->role, ['manager', 'department_manager'])) {
                return back()->with('error', 'يمكن تعيين الموظف فقط بواسطة مدير أو مدير قسم.');
            }
        }

        $data = [
            'name'          => $request->name,
            'position'      => $request->position,
            'email'         => $request->email,
            'status'        => $request->status ?? 0,
            'department_id' => $request->department_id ?? $employee->department_id,
            'assigned_by'   => $request->assigned_by,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'تم تعديل بيانات الموظف بنجاح');
    }

    public function destroy($id)
    {
        $employee = User::findOrFail($id);
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'تم حذف الموظف بنجاح');
    }
}
