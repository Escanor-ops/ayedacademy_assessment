<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\EmployeeEvaluation;
use App\Models\User;
use Carbon\Carbon;

class DepartmentController extends Controller
{
//     public function index(Request $request)
// {
//     $currentMonth = Carbon::now()->format('Y-m');
//     $selectedMonth = $request->get('month', $currentMonth);

//     $months = EmployeeEvaluation::select('month')
//         ->distinct()
//         ->orderByDesc('month')
//         ->pluck('month');

//     if (!$months->contains($selectedMonth)) {
//         $selectedMonth = $months->first();
//     }

//     // Get all departments with their active department managers
//     $departments = Department::with(['users' => function ($query) {
//         $query->where('role', 'department_manager')->where('status', 1);
//     }])->get();

//     $filteredDepartments = $departments->filter(function ($department) use ($selectedMonth) {
//         // Check if department has evaluation in this month
//         $hasEvaluation = EmployeeEvaluation::whereHas('employee', function ($query) use ($department) {
//                 $query->where('department_id', $department->id)->where('status', 1);
//             })
//             ->where('month', $selectedMonth)
//             ->exists();

//         if ($hasEvaluation) {
//             return true;
//         }

//         // Else, only include if department was created before or during selected month
//         return $department->created_at->lte(Carbon::parse($selectedMonth)->endOfMonth());
//     })->values();

//     // Attach evaluation status and current manager
//     foreach ($filteredDepartments as $department) {
//         $evaluations = EmployeeEvaluation::whereHas('employee', function ($query) use ($department) {
//                 $query->where('department_id', $department->id)->where('status', 1);
//             })
//             ->where('month', $selectedMonth)
//             ->get();

//         if ($evaluations->isEmpty()) {
//             $department->evaluation_status = null;
//         } elseif ($evaluations->contains(fn($e) => $e->status == 0)) {
//             $department->evaluation_status = 0;
//         } elseif ($evaluations->every(fn($e) => $e->status == 1)) {
//             $department->evaluation_status = 1;
//         } elseif ($evaluations->every(fn($e) => $e->status == 2)) {
//             $department->evaluation_status = 2;
//         } else {
//             $department->evaluation_status = 'Partially reviewed';
//         }

//         $department->current_manager = $department->users->first();
//     }

//     return view('manager.departments.index', [
//         'departments' => $filteredDepartments,
//         'months' => $months,
//         'selectedMonth' => $selectedMonth,
//     ]);
// }
public function index(Request $request)
{
    $currentMonth = Carbon::now()->format('Y-m');
    $selectedMonth = $request->get('month', $currentMonth);
    $isCurrentMonth = $selectedMonth === $currentMonth;
    
    // Get months where evaluations exist
    $months = EmployeeEvaluation::select('month')
        ->distinct()
        ->orderByDesc('month')
        ->pluck('month');
    
    if (!$months->contains($selectedMonth)) {
        $selectedMonth = $months->first();
    }
    
    // Get all departments with their employees and evaluations
    $departments = Department::with([
        'users' => function ($query) {
            $query->where('status', 1); // Only active users
        },
        'users.employeeEvaluations' => function ($query) use ($selectedMonth) {
            $query->where('month', $selectedMonth);
        }
    ])->get();
    
    foreach ($departments as $department) {
        // Get active employees in this department
        $departmentEmployees = User::where('status', 1)
            ->where('department_id', $department->id)
            ->with(['employeeEvaluations' => function($query) use ($selectedMonth) {
                $query->where('month', $selectedMonth);
            }])
            ->get();

        // Count evaluations by status
        $totalEmployees = $departmentEmployees->count();
        $evaluatedCount = $departmentEmployees->filter(function($employee) {
            return $employee->employeeEvaluations->isNotEmpty();
        })->count();
        
        $statusCounts = [
            0 => 0, // In Progress
            1 => 0, // Waiting Executive
            2 => 0  // Confirmed
        ];
        
        foreach ($departmentEmployees as $employee) {
            if ($evaluation = $employee->employeeEvaluations->first()) {
                $statusCounts[$evaluation->status]++;
            }
        }
        
        // Attach counts to department
        $department->total_employees = $totalEmployees;
        $department->evaluated_count = $evaluatedCount;
        $department->pending_count = $totalEmployees - $evaluatedCount;
        $department->status_counts = $statusCounts;
        
        // Determine overall status
        if ($evaluatedCount === 0) {
            $department->evaluation_status = null; // Not started
        } elseif (!$isCurrentMonth) {
            // For past months, if there are any evaluations, show as confirmed
            $department->evaluation_status = 2;
        } else {
            // Current month logic
            if ($statusCounts[2] === $totalEmployees) {
                $department->evaluation_status = 2; // All confirmed
            } elseif ($statusCounts[1] === $totalEmployees) {
                $department->evaluation_status = 1; // All waiting for executive
            } elseif ($statusCounts[0] === $totalEmployees) {
                $department->evaluation_status = 0; // All waiting for manager
            } else {
                $department->evaluation_status = 'mixed'; // Mixed statuses
            }
        }

        // Attach employees list for detailed view
        $department->employees_list = $departmentEmployees;
    }
    
    return view('manager.departments.index', compact('departments', 'months', 'selectedMonth', 'currentMonth', 'isCurrentMonth'));
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

    public function confirm(Request $request, Department $department)
    {
        $month = $request->input('month');

        // Get all evaluations for this department, for active employees, with status = 1
        $evaluations = EmployeeEvaluation::whereHas('employee', function ($query) use ($department) {
                $query->where('department_id', $department->id)
                    ->where('status', 1);
            })
            ->where('month', $month)
            ->where('status', 1)  // Only update evaluations with status = 1
            ->get();

        if ($evaluations->isEmpty()) {
            return redirect()->back()->with('error', 'لا توجد تقييمات للتأكيد في هذا القسم.');
        }

        foreach ($evaluations as $evaluation) {
            $evaluation->status = 2; // Set as confirmed by manager
            $evaluation->save();
        }

        return redirect()->back()->with('success', 'تم تأكيد التقييمات بنجاح.');
    }
}
