<?php

namespace App\Http\Controllers\Mission;

use App\Http\Controllers\Controller;
use App\Models\MissionType;
use App\Models\MissionRequest;
use Illuminate\Http\Request;

class MissionTypeController extends Controller
{
    public function store(Request $request)
    {
        try {
            \Log::info('Received mission type store request', $request->all());

            // Validate request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'department_id' => 'required|exists:departments,id'
            ]);

            \Log::info('Validation passed', $validated);

            // Check if user is department manager of this department
            if (auth()->user()->role !== 'department_manager' || 
                auth()->user()->department_id != $request->department_id) {
                \Log::warning('Unauthorized attempt to create mission type', [
                    'user_role' => auth()->user()->role,
                    'user_department' => auth()->user()->department_id,
                    'requested_department' => $request->department_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية لإضافة أنواع مهام لهذا القسم'
                ], 403);
            }

            // Create new mission type
            $missionType = MissionType::create([
                'name' => $request->name,
                'department_id' => $request->department_id,
                'status' => 0 // Active by default
            ]);

            \Log::info('Mission type created successfully', ['mission_type' => $missionType]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة نوع المهمة بنجاح',
                'data' => $missionType
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in mission type creation', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating mission type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة نوع المهمة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            // Find the mission type without global scope
            $missionType = MissionType::withoutGlobalScope('active')->findOrFail($id);
            
            \Log::info('Attempting to toggle mission type status', [
                'mission_type_id' => $missionType->id,
                'current_status' => $missionType->status,
                'user' => auth()->user()->id
            ]);

            // Check if user is department manager of this department
            if (auth()->user()->role !== 'department_manager' || 
                auth()->user()->department_id != $missionType->department_id) {
                \Log::warning('Unauthorized toggle attempt', [
                    'user_role' => auth()->user()->role,
                    'user_department' => auth()->user()->department_id,
                    'mission_type_department' => $missionType->department_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية لتعديل أنواع مهام هذا القسم'
                ], 403);
            }

            // If trying to deactivate, check if there are active missions of this type
            if ($missionType->status == 0) {
                $hasActiveMissions = MissionRequest::where('mission_type_id', $missionType->id)
                    ->whereIn('status', [0, 1]) // Pending or In Progress missions
                    ->exists();

                if ($hasActiveMissions) {
                    \Log::info('Cannot deactivate: has active missions', [
                        'mission_type_id' => $missionType->id
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يمكن تعطيل هذا النوع لوجود مهام نشطة مرتبطة به'
                    ], 403);
                }
            }

            // Toggle status
            $newStatus = $missionType->status == 0 ? 1 : 0;
            $missionType->status = $newStatus;
            $missionType->save();

            \Log::info('Successfully toggled mission type status', [
                'mission_type_id' => $missionType->id,
                'old_status' => !$newStatus,
                'new_status' => $newStatus
            ]);

            return response()->json([
                'success' => true,
                'message' => $newStatus == 0 ? 'تم تفعيل نوع المهمة بنجاح' : 'تم تعطيل نوع المهمة بنجاح',
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling mission type status', [
                'error' => $e->getMessage(),
                'mission_type_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة نوع المهمة: ' . $e->getMessage()
            ], 500);
        }
    }

    public function list($department)
    {
        // Get all mission types for the department
        $allMissionTypes = MissionType::withoutGlobalScope('active')
            ->where('department_id', $department)
            ->latest()
            ->get();

        return view('mission.partials.mission-types-table', compact('allMissionTypes'));
    }
} 