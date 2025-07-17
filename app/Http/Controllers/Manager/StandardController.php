<?php

namespace App\Http\Controllers\Manager;


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
        $standards = $employee->standards;  // Assuming the employee has a relationship with standards

        return view('manager.standards.index', compact('employee', 'standards'));  // Return the view with employee and standards data
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Show form for creating a new standard (if required in the future)
        return view('manager.standard.create');
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

        return view('manager.standard.show', compact('standard')); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $standard = Standard::findOrFail($id);  

        return view('manager.standard.edit', compact('standard'));  
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
    
}
