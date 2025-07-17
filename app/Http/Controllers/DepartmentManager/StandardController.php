<?php

namespace App\Http\Controllers\DepartmentManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Standard;
use App\Models\EmployeeEvaluationDetail;

class StandardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $employee = User::findOrFail($id); // Find the employee
        
        // Get standards ordered by status (active first) and then by name
        $standards = Standard::where('type', 'employee')
            ->where('employee_id', $employee->id)
            ->orderByDesc('status') // Active (1) will come before inactive (0)
            ->orderBy('name')
            ->get();

        return view('department.standards.index', compact('employee', 'standards'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Show form for creating a new standard (if required in the future)
        return view('department.standard.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employee_id' => 'required|exists:users,id',

        ]);


        // Create the new standard
        Standard::create([
            'name' => $request->name,
            'description' => $request->description,
            'employee_id' => $request->employee_id,
            'type'    =>'employee',
        ]);


        // Redirect back with success message
        return redirect()->back()->with('success', 'تم أضافة المعيار بنجاح');

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $standard = Standard::findOrFail($id); 

        return view('department.standard.show', compact('standard')); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $standard = Standard::findOrFail($id);  

        return view('department.standard.edit', compact('standard'));  
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $standard = Standard::findOrFail($request->standard_id);  // Find the standard by ID
        $standard->update([
            'name'  => $request->name,
            'description' => $request->description,
            'status'  =>$request->status
        ]);

        // Redirect back with success message
        return redirect()->back()->with('success', 'تم تعديل المعيار بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $standard = Standard::findOrFail($id);
    
        // Check if the standard is linked to any evaluation detail
        $isLinked = EmployeeEvaluationDetail::where('standard_id', $standard->id)->exists();
    
        if ($isLinked) {
            // If linked, just deactivate
            $standard->status = 0;
            $standard->save();
    
            return redirect()->back()->with('success', 'تم تعطيل المعيار بنجاح لارتباطه بتقييم سابق.');
        } else {
            // If not linked, delete permanently
            $standard->delete();
    
            return redirect()->back()->with('success', 'تم حذف المعيار بنجاح.');
        }
    }
    
    public function copy(Request $request)
    {
        $sourceEmployeeId = $request->from_id;
        $targetEmployeeIds = $request->employee_ids;
    
        // Get standards to copy from source employee
        $standardsToCopy = Standard::where('employee_id', $sourceEmployeeId)
            ->where('status', 1)
            ->where('type', 'employee')
            ->get();

        foreach ($targetEmployeeIds as $targetId) {
            // Get names of existing standards for this employee
            $existingStandardNames = Standard::where('employee_id', $targetId)
                ->where('type', 'employee')
                ->pluck('name')
                ->toArray();
    
            foreach ($standardsToCopy as $standard) {
                if (!in_array($standard->name, $existingStandardNames)) {
                    // Only copy if the name doesn't exist for this employee
                    Standard::create([
                        'name' => $standard->name,
                        'description' => $standard->description,
                        'type' => 'employee',
                        'status' => 1,
                        'employee_id' => $targetId,
                    ]);
                }
            }
        }
    
        return redirect()->back()->with('success', 'تم نسخ المعايير بنجاح');
    }
}
