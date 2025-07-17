<?php

namespace App\Http\Controllers\Mission;

use App\Models\MissionRequest;
use App\Models\MissionReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MissionReportController extends Controller
{
    // View reports page for a mission
    public function showReports($mission_id)
    {
        $mission = MissionRequest::with(['missionType', 'files.user'])->findOrFail($mission_id);
        
        // Check permissions
        $hasNoDepartment = auth()->user()->department_id === null; // Super admin/general manager
        $isMyDepartment = auth()->user()->department_id == $mission->department_id;
        $isDepartmentManager = auth()->user()->role == 'department_manager' && 
                             auth()->user()->department_id == $mission->user->department_id;
        $isManager = auth()->user()->role == 'manager'; // Check if user is a manager
        $isMissionCreator = auth()->user()->id == $mission->user_id;
        
        $canViewReports = 
            $hasNoDepartment || // Super admin/general manager can view all
            $isMyDepartment || // Can view if it's my department
            $isMissionCreator || // Can view if I'm the creator
            $isDepartmentManager || // Can view if I'm the department manager
            $isManager; // Managers can view all reports
        
        if (!$canViewReports) {
            return redirect()->back()->with('error', 'ليس لديك صلاحية لعرض تقارير هذه المهمة');
        }

        $reports = $mission->reports()->with('user')->latest()->get();
        $files = $mission->files()->with('user')->latest()->get();

        return view('mission.reports', compact('mission', 'reports', 'files'));
    }

    // Store new report for a mission
    public function storeReport(Request $request, $mission_id)
    {
        $mission = MissionRequest::with('user')->findOrFail($mission_id);
        
        // Check if user has permission to add reports
        $hasNoDepartment = auth()->user()->department_id === null; // Super admin/general manager
        $isMyDepartment = auth()->user()->department_id == $mission->department_id;
        $isDepartmentManager = auth()->user()->role == 'department_manager' && 
                             auth()->user()->department_id == $mission->user->department_id;
        $isMissionCreator = auth()->user()->id == $mission->user_id;
        
        $canAddReport = 
            $hasNoDepartment || // Can add if super admin/general manager
            $isMyDepartment || // Can add if it's my department
            $isMissionCreator || // Can add if I'm the creator
            $isDepartmentManager; // Can add if I'm the manager of the creator's department

        if (!$canAddReport) {
            return redirect()->back()->with('error', 'ليس لديك صلاحية لإضافة تقارير لهذه المهمة');
        }

        $request->validate([
            'content' => 'required|string|max:1000',
            'status' => 'sometimes|required|in:0,1,2,3',
        ]);
        
        // Create the report
        $report = MissionReport::create([
            'mission_request_id' => $mission->id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        // If status is being updated and user has permission
        if ($request->has('status') && ($hasNoDepartment || $isMyDepartment || $isDepartmentManager)) {
            $oldStatus = $mission->status;
            $newStatus = $request->status;

            if ($oldStatus != $newStatus) {
                $labels = [
                    0 => 'في الانتظار',
                    1 => 'جاري المعالجة',
                    2 => 'مرفوضة',
                    3 => 'مكتملة',
                ];

                // Update mission status
                $mission->status = $newStatus;
                $mission->save();

                // Create status change report
                MissionReport::create([
                    'mission_request_id' => $mission->id,
                    'user_id' => auth()->id(),
                    'content' => 'تم تغيير حالة المهمة من ' . ($labels[$oldStatus] ?? 'غير معروف') . ' إلى ' . ($labels[$newStatus] ?? 'غير معروف'),
                    'type' => 'status',
                    'from_to' => $oldStatus . ',' . $newStatus
                ]);
            }
        }

        return redirect()->route('missions.reports', $mission->id)
            ->with('success', 'تم إضافة التقرير بنجاح');
    }
}
