<?php

namespace App\Http\Controllers\SuperManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Standard;
use App\Models\EmployeeEvaluationDetail;
use Illuminate\Support\Facades\Auth;

class StandardController extends Controller
{
    public function index()
    {
        $standards = Standard::where('type','global')->latest()->get();
        return view('super.standards.index', compact('standards'));
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $globalStandard = Standard::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 1,
            'created_by' => Auth::id(), 
        ]);

        return redirect()->back()->with('success', 'تم أضافة المعيار بنجاح');

    }

  
    public function update(Request $request, $id)
    {


        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $standard = Standard::findOrFail($id);
 
        $standard->update([
            'name'  => $request->name,
            'description' => $request->description,
            'status'  =>$request->status
        ]);

        // Redirect back with success message
        return redirect()->back()->with('success', 'تم تعديل المعيار بنجاح');
    }

  

    public function destroy($id)
    {
        $standard = Standard::findOrFail($id);
    
        // Check if the standard is linked to any evaluation detail
        $isLinked = EmployeeEvaluationDetail::where('standard_id', $standard->id)->exists();
    
        if ($isLinked) {
            // If linked, just deactivate
            $standard->status = 0;
            $standard->save();
    
            return redirect()->back()->with('success', 'تم تعطيل المعيار لارتباطه بتقييم سابق.');
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
        $standardsToCopy = Standard::where('employee_id', $sourceEmployeeId)->where('status',1)->get();

    
        foreach ($targetEmployeeIds as $targetId) {
            // Get names of existing standards for this employee
            $existingStandardNames = Standard::where('employee_id', $targetId)->pluck('name')->toArray();
    
            foreach ($standardsToCopy as $standard) {
                if (!in_array($standard->name, $existingStandardNames)) {
                    // Only copy if the name doesn't exist for this employee
                    Standard::create([
                        'name' => $standard->name,
                        'description' => $standard->description,
                        'type' => $standard->type,
                        'status' => $standard->status,
                        'employee_id' => $targetId,
                    ]);
                }
            }
        }
    
        return redirect()->back()->with('success', 'تم نسخ المعايير بنجاح');
    }

}
