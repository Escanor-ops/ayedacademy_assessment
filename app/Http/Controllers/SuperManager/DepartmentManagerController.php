<?php

namespace App\Http\Controllers\SuperManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class DepartmentManagerController extends Controller
{
    /**
     * Display a listing of department managers.
     */
    public function index(Request $request)
    {
        $departments = Department::all();
        $assigners = User::whereIn('role', ['manager', 'department_manager'])->get();

        // Get the selected department from the request, default to 'all'
        $selectedDepartment = $request->get('department', 'all');

        // Base query for managers and employees
        $managersQuery = User::with(['department', 'assignedBy'])
            ->whereIn('role', ['department_manager', 'employee'])
            ->orderBy('department_id')
            ->orderBy('role')  // Order by role to group managers and employees
            ->orderBy('name');

        // If a specific department is selected, filter by that department
        if ($selectedDepartment !== 'all' && is_numeric($selectedDepartment)) {
            $managersQuery->where('department_id', $selectedDepartment);
        }

        // Get paginated results with more items per page
        $managers = $managersQuery->paginate(25)->appends(['department' => $selectedDepartment]);

        // Get counts for each department (both managers and employees)
        $departmentCounts = User::whereIn('role', ['department_manager', 'employee'])
            ->selectRaw('department_id, count(*) as count')
            ->groupBy('department_id')
            ->pluck('count', 'department_id')
            ->toArray();

        return view('super.departments.managers', compact(
            'managers', 
            'departments', 
            'assigners', 
            'selectedDepartment',
            'departmentCounts'
        ));
    }

    /**
     * Show the form for creating a new department manager.
     */
    public function create()
    {
        return view('super.department_managers.create');
    }

    /**
     * Store a newly created department manager in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'position' => 'required|string|max:255',
            'role' => 'required|in:department_manager,employee',
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|boolean',
            'assigned_by' => 'nullable|exists:users,id'
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->position = $request->position;
        $user->password = bcrypt('password'); // Set a default password
        $user->department_id = $request->department_id;
        $user->role = $request->role;
        $user->status = $request->status;
        $user->assigned_by = $request->assigned_by;
        $user->save();

        $roleText = $request->role === 'department_manager' ? 'مدير القسم' : 'الموظف';
        return redirect()->route('admin.departments-managers.index')
            ->with('success', "تم إضافة {$roleText} بنجاح");
    }

    /**
     * Show the form for editing a department manager.
     */
    public function edit($id)
    {
        $departmentManager = User::findOrFail($id);
        return view('super.department_managers.edit', compact('departmentManager'));
    }

    /**
     * Update the specified department manager in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:department_manager,employee',
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|boolean',
            'assigned_by' => 'nullable|exists:users,id'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->department_id = $request->department_id;
        $user->status = $request->status;
        $user->assigned_by = $request->assigned_by;
        $user->save();

        $roleText = $request->role === 'department_manager' ? 'مدير القسم' : 'الموظف';
        return redirect()->route('admin.departments-managers.index')
            ->with('success', "تم تحديث بيانات {$roleText} بنجاح");
    }

    /**
     * Remove the specified department manager from storage.
     */
    public function destroy($id)
    {
        $manager = User::findOrFail($id);
        $manager->delete();

        return redirect()->route('admin.departments-managers.index')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }

    /**
     * Transfer a department manager to another department.
     */
    public function transfer(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'position' => 'required|string|max:255',
            'new_department_id' => 'required|exists:departments,id',
            'new_role' => 'required|in:department_manager,employee',
            'status' => 'required|boolean',
            'assigned_by' => 'nullable|exists:users,id'
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'position' => $request->position,
            'department_id' => $request->new_department_id,
            'role' => $request->new_role,
            'status' => $request->status,
            'assigned_by' => $request->assigned_by
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'تم تحديث بيانات المستخدم بنجاح');
    }
}
