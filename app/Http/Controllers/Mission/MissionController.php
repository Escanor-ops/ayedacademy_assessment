<?php
namespace App\Http\Controllers\Mission;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\MissionRequest;
use App\Models\MissionType;
use App\Models\MissionReport;
use App\Models\MissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class MissionController extends Controller
{
    // Show missions for departments based on the selected month
    public function index(Request $request)
    {
        $departments = Department::with('missions')->get();
    
        return view('mission.depts', compact('departments'));
    }
    
    public function viewMissions(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        
        // Get active mission types for the dropdown
        $missionTypes = MissionType::where('department_id', $id)
                                 ->where('status', 0) // Only fetch active mission types
                                 ->get();

        // Get all mission types for the management modal
        $allMissionTypes = MissionType::withoutGlobalScope('active')
                                    ->where('department_id', $id)
                                    ->latest()
                                    ->get();

        $statusFilter = $request->input('status');
        $askerFilter = $request->input('asker');
        $isEmployee = auth()->user()->role == 'employee';
        $isMyDepartment = auth()->user()->department_id == $department->id;

        // Base query: missions for this department
        $missionsQuery = MissionRequest::with(['user', 'missionType'])
                        ->where('department_id', $id);

        // If employee viewing other department, only show missions from their department members
        if ($isEmployee && !$isMyDepartment) {
            $missionsQuery->whereHas('user', function($q) {
                $q->where('department_id', auth()->user()->department_id);
            });
        }

        // Apply status filter if provided
        if (!is_null($statusFilter) && $statusFilter !== '') {
            $missionsQuery->where('status', $statusFilter);
        }

        // Apply asker filter
        if ($askerFilter === 'me') {
            $missionsQuery->where('user_id', auth()->id());
        } elseif ($askerFilter === 'my_department') {
            $missionsQuery->whereHas('user', function($q) {
                $q->where('department_id', auth()->user()->department_id);
            });
        } elseif ($askerFilter === 'others' && !$isEmployee) {
            // Only non-employees can see missions from other departments
            $missionsQuery->whereHas('user', function($q) {
                $q->where('department_id', '!=', auth()->user()->department_id);
            });
        }

        $missions = $missionsQuery->latest()->paginate(12)->withQueryString();

        return view('mission.dept', compact('department', 'missionTypes', 'missions', 'statusFilter', 'askerFilter', 'allMissionTypes'));
    }


    // Store a new mission
    public function store(Request $request)
    {

        // Validate the incoming request
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'description' => 'required|string|max:255',
        ]);

        // Create a new mission for the selected department
        MissionRequest::create([
            'department_id' => $request->department_id,
            'description' => $request->description,
            'mission_type_id' =>$request->mission_type_id,
            'user_id' =>auth()->user()->id,
            'link'=>$request->link,
            'deadline'=>$request->date,
            'hour'=>$request->time,
            'ticket_number' => '#' . strtoupper(uniqid()),
            'status' => 0, // New missions are considered in-progress
        ]);

        // Redirect back with a success message
        return back()->with('success', 'تم طلب المهمة بنجاح!');

    }

    // View the details of a specific mission (optional)
    public function show(Mission $mission)
    {
        return view('manager.missions.show', compact('mission'));
    }

    public function updateStatus(Request $request)
    {
        $mission = MissionRequest::findOrFail($request->mission_id);
        
        // Check permissions
        $hasNoDepartment = auth()->user()->department_id === null;
        $isMyDepartment = auth()->user()->department_id == $mission->department_id;
        $isDepartmentManager = auth()->user()->role == 'department_manager' && 
                             auth()->user()->department_id == $mission->user->department_id;
        $isManager = auth()->user()->role == 'manager';
        
        $canUpdateStatus = 
            $hasNoDepartment || // Can update if super admin/general manager
            $isMyDepartment || // Can update if it's my department
            $isDepartmentManager; // Can update if I'm the department manager
            
        if (!$canUpdateStatus || $isManager) {
            return redirect()->back()->with('error', 'ليس لديك صلاحية لتحديث حالة هذه المهمة');
        }

        $oldStatus = $mission->status;
        $newStatus = $request->status;
        if ($oldStatus == $newStatus) {
            return redirect()->back()->with('failed', 'لم يتم تغيير حالة المهمة لأنها بالفعل في نفس الحالة.');
        }
    
        $labels = [
            0 => 'الانتظار',
            1 => 'جاري المعالجة',
            2 => 'مرفوضة',
            3 => 'مكتملة',
        ];
    
        // Update mission status
        $mission->status = $newStatus;
        $mission->save();
    
        // Insert report
        MissionReport::create([
            'mission_request_id' => $mission->id,
            'user_id' => auth()->user()->id,
            'content' => 'تم تغيير حالة المهمة إلى ' . ($labels[$newStatus] ?? 'غير معروف'),
        ]);
    
        return redirect()->back()->with('success', 'تم تحديث حالة المهمة بنجاح.');
    }
    // MissionController.php

    public function uploadFiles(Request $request, $id)
    {
        // Log all request information
        \Log::info('Upload request received', [
            'id' => $id,
            'has_file' => $request->hasFile('file'),
            'all_files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
            'request_keys' => array_keys($request->all())
        ]);
        
        $mission = MissionRequest::with('user')->findOrFail($id);
        
        // Check permissions
        $hasNoDepartment = auth()->user()->department_id === null; // Super admin/general manager
        $isMyDepartment = auth()->user()->department_id == $mission->department_id;
        $isDepartmentManager = auth()->user()->role == 'department_manager' && 
                             auth()->user()->department_id == $mission->user->department_id;
        $isMissionCreator = auth()->user()->id == $mission->user_id;
        
        if (!$hasNoDepartment && !$isMyDepartment && !$isDepartmentManager && !$isMissionCreator) {
            return response()->json(['success' => false, 'message' => 'ليس لديك صلاحية لإضافة ملفات لهذه المهمة'], 403);
        }
    
        // Try different approaches to get the file
        $file = null;
        
        // Method 1: Standard Laravel file check
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            \Log::info('File found using standard method');
        } 
        // Method 2: Check in $_FILES array
        else if (isset($_FILES['file']) && !empty($_FILES['file']['tmp_name'])) {
            $path = $_FILES['file']['tmp_name'];
            $originalName = $_FILES['file']['name'];
            $mimeType = $_FILES['file']['type'];
            $size = $_FILES['file']['size'];
            $error = $_FILES['file']['error'];
            
            \Log::info('Raw file data', [
                'path' => $path,
                'name' => $originalName,
                'type' => $mimeType,
                'size' => $size,
                'error' => $error
            ]);
            
            if ($error === UPLOAD_ERR_OK && file_exists($path)) {
                $file = new \Illuminate\Http\UploadedFile(
                    $path,
                    $originalName,
                    $mimeType,
                    $error
                );
                \Log::info('File created from $_FILES');
            } else {
                \Log::error('File error in $_FILES', ['error_code' => $error]);
            }
        }
        
        if ($file) {
            \Log::info('File details', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension()
            ]);
            
            try {
                // Validate the file
                $validator = \Illuminate\Support\Facades\Validator::make(
                    ['file' => $file],
                    ['file' => 'required|file|max:102400|mimes:jpg,jpeg,png,pdf,webp,zip,doc,docx,mp4,mov,avi,mp3,wav,m4a']
                );
                
                if ($validator->fails()) {
                    \Log::error('File validation failed', ['errors' => $validator->errors()]);
                    return response()->json([
                        'success' => false,
                        'message' => 'خطأ في التحقق من صحة الملف: ' . implode(', ', $validator->errors()->get('file'))
                    ], 422);
                }
                
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . preg_replace('/\s+/', '_', $originalName);
                $filePath = $file->storeAs('missions/' . $mission->id, $filename, 'public');
        
                // Store file record in database
                $missionFile = MissionFile::create([
                    'mission_id' => $mission->id,
                    'user_id' => auth()->id(),
                    'file_name' => $originalName,
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize()
                ]);
        
                return response()->json([
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => asset('storage/' . $filePath),
                    'file_id' => $missionFile->id
                ]);
            } catch (\Exception $e) {
                \Log::error('Validation or storage error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Error: ' . $e->getMessage()
                ], 422);
            }
        } else {
            \Log::warning('No file in request', [
                'request_keys' => array_keys($request->all()),
                'files_array' => $_FILES
            ]);
            
            return response()->json(['success' => false, 'message' => 'No file uploaded or file is invalid'], 400);
        }
    }
    
    public function getDepartmentsData()
    {
        $departments = Department::with('missions')->get();
        return response()->json([
            'html' => view('mission.partials.departments-table', compact('departments'))->render()
        ]);
    }

    public function getMissionsData(Request $request, $id)
    {
        try {
            $department = Department::findOrFail($id);
            $statusFilter = $request->input('status');
            $askerFilter = $request->input('asker');
            $lastMissionId = $request->input('last_mission_id', 0);
            
            try {
                $newMissionIds = json_decode($request->input('newMissionIds', '[]'), true) ?? [];
            } catch (\Exception $e) {
                $newMissionIds = [];
            }
            
            $isEmployee = auth()->user()->role == 'employee';
            $isMyDepartment = auth()->user()->department_id == $department->id;

            // If employee viewing other department, force asker=me
            if ($isEmployee && !$isMyDepartment) {
                $askerFilter = 'me';
            }

            // Base query: missions for this department with eager loading
            $missionsQuery = MissionRequest::with([
                'user',
                'missionType',
                'user.department' // Add this to avoid N+1 queries
            ])->where('department_id', $id);

            // Apply status filter if provided
            if (!is_null($statusFilter) && $statusFilter !== '') {
                $missionsQuery->where('status', $statusFilter);
            }

            // Apply asker filter based on role and department
            if ($askerFilter === 'me') {
                $missionsQuery->where('user_id', auth()->id());
            } elseif ($askerFilter === 'my_department') {
                $missionsQuery->whereHas('user', function($q) {
                    $q->where('department_id', auth()->user()->department_id);
                });
            } elseif ($askerFilter === 'others' && !$isEmployee) {
                $missionsQuery->whereHas('user', function($q) {
                    $q->where('department_id', '!=', auth()->user()->department_id);
                });
            }

            $missions = $missionsQuery->latest()->paginate(12)->withQueryString();
            
            // Get new missions since last check
            $recentNewMissions = $missionsQuery->where('id', '>', $lastMissionId)->pluck('id')->toArray();
            
            // Combine with existing new missions
            $allNewMissions = array_unique(array_merge($newMissionIds, $recentNewMissions));

            $view = view('mission.partials.missions-table', [
                'missions' => $missions,
                'department' => $department,
                'newMissions' => $allNewMissions,
                'isEmployee' => $isEmployee,
                'isMyDepartment' => $isMyDepartment
            ])->render();

            return response()->json([
                'html' => $view,
                'lastMissionId' => $missions->max('id') ?? 0,
                'newMissions' => $allNewMissions
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getMissionsData:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'حدث خطأ أثناء تحديث البيانات',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadFile($file)
    {
        $file = MissionFile::findOrFail($file);
        $mission = $file->mission;

        // Check permissions
        $hasNoDepartment = auth()->user()->department_id === null;
        $isMyDepartment = auth()->user()->department_id == $mission->department_id;
        $isDepartmentManager = auth()->user()->role == 'department_manager' && 
                             auth()->user()->department_id == $mission->user->department_id;
        $isManager = auth()->user()->role == 'manager';
        $isMissionCreator = auth()->user()->id == $mission->user_id;
        
        $canDownload = 
            $hasNoDepartment || // Can download if super admin/general manager
            $isMyDepartment || // Can download if it's my department
            $isMissionCreator || // Can download if I'm the creator
            $isDepartmentManager || // Can download if I'm the department manager
            $isManager; // Can download if I'm a manager
            
        if (!$canDownload) {
            return redirect()->back()->with('error', 'ليس لديك صلاحية لتحميل هذا الملف');
        }

        $filePath = storage_path('app/public/' . $file->file_path);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'الملف غير موجود');
        }

        return response()->download($filePath, $file->file_name);
    }
}
