<?php

namespace App\Http\Controllers\DepartmentManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Standard;
use App\Models\EmployeeEvaluation;
use App\Models\EmployeeEvaluationDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        
        $departmentManager = auth()->user();
        $currentMonth = now()->format('Y-m');
        $selectedMonth = $request->get('month', $currentMonth);
        $isCurrentMonth = $selectedMonth === $currentMonth;

        // Get last 12 months that have evaluations
        $months = EmployeeEvaluation::whereHas('employee', function ($query) use ($departmentManager) {
            $query->where('status', 1)
                  ->where(function($q) use ($departmentManager) {
                      // Users in my department with no assigned evaluator
                      $q->where('department_id', $departmentManager->department_id)
                        ->whereNull('assigned_by');
                      // OR users specifically assigned to me (regardless of department)
                      $q->orWhere('assigned_by', $departmentManager->id);
                  });
        })
        ->select('month')
        ->distinct()
        ->orderByDesc('month')
        ->limit(12)
        ->pluck('month');

        // Ensure current month is in the list
        if (!$months->contains($currentMonth)) {
            $months->prepend($currentMonth);
        }

        // Get employees based on the rules
        $employees = User::where('status', 1)
            ->where('id', '!=', $departmentManager->id) // 🔥 exclude current user
            ->where(function($query) use ($departmentManager) {
                // Users in my department with no assigned evaluator
                $query->where('department_id', $departmentManager->department_id)
                      ->whereNull('assigned_by');
                // OR users specifically assigned to me (regardless of department)
                $query->orWhere('assigned_by', $departmentManager->id);
            })
            ->with(['department', 'employeeEvaluations' => function($query) use ($selectedMonth) {
                $query->where('month', $selectedMonth);
            }])
            // Order by whether they have an evaluation for the selected month
            ->orderByRaw("(SELECT COUNT(*) FROM employee_evaluations 
                         WHERE employee_evaluations.employee_id = users.id 
                         AND employee_evaluations.month = ?) DESC", [$selectedMonth])
            ->orderBy('name');

        // If it's a past month, only show employees who have been evaluated
        if ($selectedMonth !== $currentMonth) {
            $employees->whereHas('employeeEvaluations', function($query) use ($selectedMonth) {
                $query->where('month', $selectedMonth);
            });
        }

        $employees = $employees->paginate(50)
            ->appends(['month' => $selectedMonth]);

        // Attach evaluation to each employee
        foreach ($employees as $employee) {
            $employee->evaluation = $employee->employeeEvaluations->first();
        }

        // Check if all employees have been evaluated and all evaluations are in status 0
        $allEvaluated = $employees->isNotEmpty() && $employees->every(function($employee) {
            return $employee->evaluation && $employee->evaluation->status === 0;
        });

        return view('department_manager.evaluation.index', compact(
            'employees', 
            'months', 
            'selectedMonth', 
            'currentMonth', 
            'isCurrentMonth',
            'allEvaluated'
        ));
    }

    public function create(Request $request)
    {
        $employee = User::findOrFail($request->employee);
        $month = $request->month;
    
        // Global standards (for all employees)
        $globalStandards = Standard::where('type', 'global')->where('status',1)->get();
    
        // Employee-specific standards
        $employeeStandards = Standard::where('type', 'employee')
            ->where('employee_id', $employee->id)
            ->get();
    
        return view('department_manager.evaluation.create', compact(
            'employee', 'month', 'globalStandards', 'employeeStandards'
        ));
    }
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'month' => 'required|date_format:Y-m',
            'standards' => 'required|array',
            'standards.*' => 'nullable|numeric|min:0|max:100',
            'notices' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        $employeeId = $request->employee_id;
        $month = $request->month;
        $page = $request->get('page', 1); // Get the current page number

        // Check for existing evaluation
        $existing = EmployeeEvaluation::where('employee_id', $employeeId)
            ->where('month', $month)
            ->first();

        if ($existing) {
            return back()->with('error', 'تم تقييم هذا الموظف بالفعل لهذا الشهر.');
        }

        // Create the evaluation with a default overall_rating of 0
        $evaluation = EmployeeEvaluation::create([
            'employee_id' => $employeeId,
            'month' => $month,
            'overall_rating' => 0, // Initial rating set to 0
            'department_id' =>auth()->user()->department_id,
            'assigned_by' => auth()->user()->id,
            'status' => 0,
            'notices' => $request->notices,
            'recommendations' => $request->recommendations,
        ]);

        $total = 0;
        $count = 0;

        foreach ($request->standards as $standardId => $score) {
            if ($score === null) continue;

            EmployeeEvaluationDetail::create([
                'employee_evaluation_id' => $evaluation->id,
                'standard_id' => $standardId,
                'score' => $score,
            ]);

            $total += $score;
            $count++;
        }

        // Recalculate and update overall rating if we have valid scores
        if ($count > 0) {
            $evaluation->update([
                'overall_rating' => round($total / $count, 2),
            ]);
        }

        return redirect()
            ->route('department_manager.evaluation.index', ['month' => $month, 'page' => $page])
            ->with('success', 'تم حفظ التقييم بنجاح.');
    }
    public function edit($evaluationId)
    {
        // Fetch the evaluation based on evaluation ID
        $evaluation = EmployeeEvaluation::with('employeeEvaluationDetails')
            ->where('id', $evaluationId)
            ->where('status', 0)  // Only evaluations with status 0 (in progress)
            ->whereNotNull('overall_rating')  // Ensure it has an overall rating
            ->first();
        
        // Check if the evaluation exists
        if (!$evaluation) {
            return redirect()->route('department_manager.evaluation.index', ['month' => now()->format('Y-m')])
                ->with('error', 'Evaluation not found or cannot be edited.');
        }
    
        // Fetch the employee details from the evaluation
        $employee = $evaluation->employee;
    
        // Fetch global and employee-specific standards
        $globalStandards = Standard::where('type', 'global')->where('status',1)->get();
        $employeeStandards = Standard::where('type', 'employee')
            ->where('employee_id', $employee->id)
            ->where('status',1)
            ->get();
    
        // Fetch the month from the evaluation (it's part of the `EmployeeEvaluation` record)
        $month = $evaluation->month;
        
        // Pass all required data to the view
        return view('department_manager.evaluation.edit', compact('evaluation', 'employee', 'month', 'globalStandards', 'employeeStandards'));
    }
    
    public function update(Request $request, $evaluationId)
    {
        // Validate the input
        $validated = $request->validate([
            'standards.*' => 'required|numeric|min:0|max:100', // Ensure scores are numbers between 0 and 100
            'notices' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);
    
        // Fetch the evaluation to update
        $evaluation = EmployeeEvaluation::findOrFail($evaluationId);
        
        // Check if evaluation can be updated (status must be 0)
        if ($evaluation->status !== 0) {
            return redirect()->back()->with('error', 'لا يمكن تعديل هذا التقييم في حالته الحالية.');
        }
    
        // Submitted scores and standard IDs
        $submittedScores = $validated['standards'];
        $submittedStandardIds = array_keys($submittedScores);
    
        // Update the evaluation
        $evaluation->update([
            'overall_rating' => count($submittedScores) > 0 ? array_sum($submittedScores) / count($submittedScores) : null,
            'notices' => $request->notices,
            'recommendations' => $request->recommendations,
        ]);

        // Get existing standard IDs from DB
        $existingStandardIds = $evaluation->employeeEvaluationDetails()
            ->pluck('standard_id')
            ->toArray();

        // Delete old standards that are no longer submitted
        $toDelete = array_diff($existingStandardIds, $submittedStandardIds);
        if (!empty($toDelete)) {
            $evaluation->employeeEvaluationDetails()
                ->whereIn('standard_id', $toDelete)
                ->delete();
        }

        // Update or create submitted scores
        foreach ($submittedScores as $standardId => $score) {
            $evaluation->employeeEvaluationDetails()->updateOrCreate(
                ['standard_id' => $standardId],
                ['score' => $score]
            );
        }

        return redirect()->route('department_manager.evaluation.index', ['month' => $request->month])
            ->with('success', 'تم تعديل التقييم');
    }

public function changeStatus(Request $request)
{
    $departmentManager = auth()->user();
    $currentMonth = now()->format('Y-m');

    // Case 1: Users in my department with assigned_by = null
    $inDepartmentUpdated = User::where('status', 1)
        ->where('department_id', $departmentManager->department_id)
        ->whereNull('assigned_by')
        ->whereHas('employeeEvaluations', function ($query) use ($currentMonth) {
            $query->where('status', 0)
                  ->where('month', $currentMonth);
        })
        ->with(['employeeEvaluations' => function($query) use ($currentMonth) {
            $query->where('status', 0)
                  ->where('month', $currentMonth);
        }])
        ->get()
        ->each(function ($employee) {
            $employee->employeeEvaluations->each(function ($evaluation) {
                $evaluation->update(['status' => 1]);
            });
        });

    // Case 2: Users NOT in my department but assigned_by = my ID
    $outsideDepartmentUpdated = User::where('status', 1)
        ->where('assigned_by', $departmentManager->id)
        ->where('department_id', '!=', $departmentManager->department_id)
        ->whereHas('employeeEvaluations', function ($query) use ($currentMonth) {
            $query->where('status', 0)
                  ->where('month', $currentMonth);
        })
        ->with(['employeeEvaluations' => function($query) use ($currentMonth) {
            $query->where('status', 0)
                  ->where('month', $currentMonth);
        }])
        ->get()
        ->each(function ($employee) {
            $employee->employeeEvaluations->each(function ($evaluation) {
                $evaluation->update(['status' => 1]);
            });
        });

    $totalUpdated = $inDepartmentUpdated->count() + $outsideDepartmentUpdated->count();
    
    if ($totalUpdated > 0) {
        return redirect()->back()->with('success', "تم تأكيد التقييمات لـ $totalUpdated موظف");
    } else {
        return redirect()->back()->with('info', 'لا يوجد تقييمات للتأكيد');
    }
}
  

    public function showEvaluation()
    {
        $evaluations = EmployeeEvaluation::where('status', 2)
            ->where('employee_id', auth()->id())
            ->orderBy('month', 'desc')
            ->get();

        $currentMonth = now()->format('Y-m');

        return view('department.evaluation.view', compact('evaluations', 'currentMonth'));
    }

    public function details($employee, $month)
    {
        $departmentManager = auth()->user();
        
        // Find the evaluation
        $evaluation = EmployeeEvaluation::with(['employeeEvaluationDetails.standard', 'employee'])
            ->where('month', $month)
            ->whereHas('employee', function ($query) use ($departmentManager, $employee) {
                $query->where('id', $employee)
                      ->where('status', 1)
                      ->where(function($q) use ($departmentManager) {
                          // Users in my department with no assigned evaluator
                          $q->where('department_id', $departmentManager->department_id)
                            ->whereNull('assigned_by');
                          // OR users specifically assigned to me (regardless of department)
                          $q->orWhere('assigned_by', $departmentManager->id);
                      });
            })
            ->first();
    
        if (!$evaluation) {
            return redirect()->back()->with('error', 'لم يتم العثور على التقييم.');
        }
    
        return view('department.evaluation.details', [
            'evaluation' => $evaluation
        ]);
    }


    public function deleteEvaluations(Request $request)
    {
        $deleted = \App\Models\EmployeeEvaluation::where('month', $request->month)
            ->where('department_id', $request->department_id)
            ->delete();
    
        return response()->json(['deleted' => $deleted]);
    }


}
