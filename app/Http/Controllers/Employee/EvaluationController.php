<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Standard;
use App\Models\EmployeeEvaluation;
use App\Models\EmployeeEvaluationDetail;
use Carbon\Carbon;

class EvaluationController extends Controller
{
   
    public function showEvaluation()
    {
        $evaluations = EmployeeEvaluation::whereIn('status', [1, 2])
            ->where('employee_id', auth()->id())
            ->orderBy('month', 'desc')
            ->get();

        return view('employee.evaluation.view', compact('evaluations'));
    }

    public function details($month)
    {
        $evaluation = EmployeeEvaluation::with(['employeeEvaluationDetails.standard', 'employee'])
            ->where('month', $month)
            ->where('employee_id', auth()->user()->id)
            ->first();
    
        if (!$evaluation) {
            return redirect()->back()->with('error', 'لم يتم العثور على التقييم.');
        }
    
        return view('employee.evaluation.details', [
            'evaluation' => $evaluation,
        ]);
    }

}
