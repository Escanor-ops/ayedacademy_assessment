<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Standard;
use App\Models\Department;
use App\Models\EmployeeEvaluation;
use App\Models\EmployeeEvaluationDetail;
use Carbon\Carbon;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $manager = auth()->user();
        $currentMonth = now()->format('Y-m');
        $selectedMonth = $request->get('month', $currentMonth);
        $isCurrentMonth = $selectedMonth === $currentMonth;

        // Get last 12 months that have evaluations
        $months = EmployeeEvaluation::whereHas('employee', function ($query) use ($manager) {
            $query->where('status', 1)
                  ->where(function($q) use ($manager) {
                      // Department managers with no assigned_by or assigned by current manager
                      $q->where('role', 'department_manager')
                        ->where(function($sq) use ($manager) {
                            $sq->whereNull('assigned_by')
                               ->orWhere('assigned_by', $manager->id);
                        });
                      // Or employees directly assigned by current manager
                      $q->orWhere(function($sq) use ($manager) {
                          $sq->where('assigned_by', $manager->id);
                      });
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

        // Check if there are any evaluations in progress
        $evaluationInProgress = EmployeeEvaluation::where('month', $selectedMonth)
            ->where('status', 0)
            ->whereHas('employee', function ($query) use ($manager) {
                $query->where('status', 1)
                      ->where(function($q) use ($manager) {
                          // Department managers with no assigned_by or assigned by current manager
                          $q->where('role', 'department_manager')
                            ->where(function($sq) use ($manager) {
                                $sq->whereNull('assigned_by')
                                   ->orWhere('assigned_by', $manager->id);
                            });
                          // Or employees directly assigned by current manager
                          $q->orWhere(function($sq) use ($manager) {
                              $sq->where('assigned_by', $manager->id);
                          });
                      });
            })
            ->exists();

        // Get employees based on the rules
        $employees = User::where('status', 1)
            ->where(function($query) use ($manager) {
                // Department managers with no assigned_by or assigned by current manager
                $query->where('role', 'department_manager')
                      ->where(function($q) use ($manager) {
                          $q->whereNull('assigned_by')
                             ->orWhere('assigned_by', $manager->id);
                      });
                // Or employees directly assigned by current manager
                $query->orWhere(function($q) use ($manager) {
                    $q->where('assigned_by', $manager->id);
                });
            });

        // If it's a past month, only show employees who have been evaluated
        if ($selectedMonth !== $currentMonth) {
            $employees->whereHas('employeeEvaluations', function($query) use ($selectedMonth) {
                $query->where('month', $selectedMonth);
            });
        }

        $employees = $employees->with(['department', 'employeeEvaluations' => function($query) use ($selectedMonth) {
            $query->where('month', $selectedMonth);
        }])
        ->paginate(10)
        ->appends(['month' => $selectedMonth]);

        // Attach evaluation to each employee
        foreach ($employees as $employee) {
            $employee->evaluation = $employee->employeeEvaluations->first();
        }

        // Calculate if all employees have been evaluated with status 0
        $allEvaluated = $employees->isNotEmpty() && $employees->every(function($employee) {
            return $employee->evaluation && $employee->evaluation->status == 0;
        });

        return view('manager.evaluation.index', compact(
            'employees',
            'months',
            'selectedMonth',
            'currentMonth',
            'evaluationInProgress',
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
    
        return view('manager.evaluation.create', compact(
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
        $user = User::findOrFail($employeeId);

        $month = $request->month;

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
            'department_id' =>$user->department_id,
            'assigned_by' => auth()->user()->id,
            'status' => 1,
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
            ->route('manager.evaluation.index', ['month' => $month])
            ->with('success', 'تم حفظ التقييم بنجاح.');
    }
    public function edit($evaluationId)
    {
        $manager = auth()->user();
        
        // Fetch the evaluation based on evaluation ID
        $evaluation = EmployeeEvaluation::with(['employeeEvaluationDetails', 'employee'])
            ->where('id', $evaluationId)
            ->whereHas('employee', function ($query) use ($manager) {
                $query->where('status', 1)
                      ->where(function($q) use ($manager) {
                          // Department managers with no assigned_by or assigned by current manager
                          $q->where('role', 'department_manager')
                            ->where(function($sq) use ($manager) {
                                $sq->whereNull('assigned_by')
                                   ->orWhere('assigned_by', $manager->id);
                            });
                          // Or employees directly assigned by current manager
                          $q->orWhere(function($sq) use ($manager) {
                              $sq->where('assigned_by', $manager->id);
                          });
                      });
            })
            ->first();
        
        // Check if the evaluation exists and user has permission
        if (!$evaluation) {
            return redirect()->route('manager.evaluation.index', ['month' => now()->format('Y-m')])
                ->with('error', 'التقييم غير موجود أو لا يمكن تعديله.');
        }

        // Fetch the employee details from the evaluation
        $employee = $evaluation->employee;

        // Fetch global and employee-specific standards
        $globalStandards = Standard::where('type', 'global')->where('status', 1)->get();
        $employeeStandards = Standard::where('type', 'employee')
            ->where('employee_id', $employee->id)
            ->where('status', 1)
            ->get();

        // Fetch the month from the evaluation
        $month = $evaluation->month;
        
        // Pass all required data to the view
        return view('manager.evaluation.edit', compact('evaluation', 'employee', 'month', 'globalStandards', 'employeeStandards'));
    }
    
    public function update(Request $request, $evaluationId)
    {
        $manager = auth()->user();
        
        // Validate the input
        $validated = $request->validate([
            'standards.*' => 'required|numeric|min:0|max:100', // Ensure scores are numbers between 0 and 100
            'notices' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        // Fetch the evaluation to update with permission check
        $evaluation = EmployeeEvaluation::whereHas('employee', function ($query) use ($manager) {
            $query->where('status', 1)
                  ->where(function($q) use ($manager) {
                      // Department managers with no assigned_by or assigned by current manager
                      $q->where('role', 'department_manager')
                        ->where(function($sq) use ($manager) {
                            $sq->whereNull('assigned_by')
                               ->orWhere('assigned_by', $manager->id);
                        });
                      // Or employees directly assigned by current manager
                      $q->orWhere(function($sq) use ($manager) {
                          $sq->where('assigned_by', $manager->id);
                      });
                  });
        })->findOrFail($evaluationId);

        // Submitted scores and standard IDs
        $submittedScores = $validated['standards'];
        $submittedStandardIds = array_keys($submittedScores);

        // Update the overall rating and additional fields
        $evaluation->update([
            'overall_rating' => count($submittedScores) > 0 ? array_sum($submittedScores) / count($submittedScores) : null,
            'notices' => $request->notices,
            'recommendations' => $request->recommendations,
        ]);

        // Get existing standard IDs from DB
        $existingStandardIds = $evaluation->employeeEvaluationDetails()
            ->pluck('standard_id')
            ->toArray();

        // Delete evaluation details that are not submitted anymore
        $toDelete = array_diff($existingStandardIds, $submittedStandardIds);
        if (!empty($toDelete)) {
            $evaluation->employeeEvaluationDetails()
                ->whereIn('standard_id', $toDelete)
                ->delete();
        }

        // Update or create submitted evaluation details
        foreach ($submittedScores as $standardId => $score) {
            $evaluation->employeeEvaluationDetails()->updateOrCreate(
                ['standard_id' => $standardId],
                ['score' => $score]
            );
        }

        return redirect()->route('manager.evaluation.index', ['month' => $request->month])
            ->with('success', 'تم تعديل التقييم بنجاح');
    }
    
    public function changeStatus(Request $request)
    {
        $manager = auth()->user();
        $currentMonth = now()->format('Y-m');

        // Get all evaluations for users that this manager can evaluate
        $evaluations = EmployeeEvaluation::where('month', $currentMonth)
            ->where('status', 0)
            ->whereHas('employee', function ($query) use ($manager) {
                $query->where('status', 1)
                      ->where(function($q) use ($manager) {
                          // Department managers with no assigned_by or assigned by current manager
                          $q->where('role', 'department_manager')
                            ->where(function($sq) use ($manager) {
                                $sq->whereNull('assigned_by')
                                   ->orWhere('assigned_by', $manager->id);
                            });
                          // Or employees directly assigned by current manager
                          $q->orWhere(function($sq) use ($manager) {
                              $sq->where('assigned_by', $manager->id);
                          });
                      });
            })
            ->get();

        if ($evaluations->isEmpty()) {
            return redirect()->back()->with('info', 'لا يوجد تقييمات للتأكيد');
        }

        // Update all evaluations to status 1
        foreach ($evaluations as $evaluation) {
            $evaluation->update(['status' => 1]);
        }

        $updatedCount = $evaluations->count();
        return redirect()->back()->with('success', "تم تأكيد التقييمات لـ $updatedCount موظف");
    }

    public function view($departmentId, $month)
    {
        // Fetch the department
        $department = Department::findOrFail($departmentId);
        $isCurrentMonth = $month === now()->format('Y-m');

        // Base query for active users in this department
        $query = User::where('status', 1)
            ->where('department_id', $departmentId);

        // For past months, only show users who have evaluations
        if (!$isCurrentMonth) {
            $query->whereHas('employeeEvaluations', function($q) use ($month) {
                $q->where('month', $month);
            });
        }

        // Get users with their evaluations
        $users = $query->with([
            'employeeEvaluations' => function($query) use ($month) {
                $query->where('month', $month);
            },
            'employeeEvaluations.employeeEvaluationDetails.standard',
            'assignedBy',
            'department'
        ])
        // Order by whether they have an evaluation for the selected month
        ->orderByRaw("(SELECT COUNT(*) FROM employee_evaluations 
                     WHERE employee_evaluations.employee_id = users.id 
                     AND employee_evaluations.month = ?) DESC", [$month])
        ->orderBy('name')
        ->get();

        // Attach evaluation to each user for easier access in view
        foreach ($users as $user) {
            $user->evaluation = $user->employeeEvaluations->first();
        }

        // Pass the users and department to the view
        return view('manager.evaluation.view', compact('users', 'department', 'month', 'isCurrentMonth'));
    }
    public function empEvaluation($emp, $month)
    {
        $evaluation = EmployeeEvaluation::with(['employeeEvaluationDetails.standard', 'employee'])
            ->where('month', $month)
            ->where('employee_id', $emp)
            ->first();
    
        if (!$evaluation) {
            return redirect()->back()->with('error', 'لم يتم العثور على التقييم.');
        }
    
        return view('manager.evaluation.month', [
            'evaluation' => $evaluation,
            'type' => 'emp'
        ]);
    }

}
