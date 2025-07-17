@if($missions->count() > 0)
  <div class="mt-3 d-flex justify-content-end links">
    {{ $missions->links() }}
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>عنوان المهمة</th>
          <th>مُعرف المهمة</th>
          <th>صاحب الطلب</th>
          <th>الحالة</th>
          <th>موعد المهمة</th>
          <th>تاريخ الإنشاء</th>
          <th>الإجراء</th>
        </tr>
      </thead>
      <tbody>
        @foreach($missions as $index => $mission)
          @php
            $missionType = $mission->missionType ?? null;
            $user = $mission->user ?? null;
          @endphp
          <tr class="{{ in_array($mission->id, $newMissions ?? []) ? 'new-mission' : '' }}">
            <td>{{ $missions->firstItem() + $index }}</td>
            <td>{{ $missionType ? $missionType->name : 'غير متوفر' }}</td>
            <td>{{ $mission->ticket_number }}</td>
            <td>
              @if($user)
                @php
                  $userRole = $user->role ?? 'unknown';
                  $userDepartmentId = $user->department_id ?? null;
                  $isMyDepartment = $userDepartmentId && $userDepartmentId == auth()->user()->department_id;
                  
                  if ($userRole == 'manager') {
                    $roleClass = 'text-purple fw-bold';
                    $roleText = 'مدير عام';
                  } elseif ($userRole == 'department_manager') {
                    $roleClass = 'text-primary fw-bold';
                    $roleText = 'مدير قسم';
                  } elseif ($isMyDepartment) {
                    $roleClass = 'text-success fw-bold';
                    $roleText = 'زميل';
                  } else {
                    $roleClass = 'text-secondary fw-bold';
                    $roleText = 'موظف';
                  }
                @endphp
                <span class="{{ $roleClass }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $roleText }}">
                  {{ $user->name }}
                </span>
              @else
                <span class="text-muted">غير متوفر</span>
              @endif
            </td>
            <td>
              @if($mission->status === 0)
                <span class="badge bg-warning text-dark">في الانتظار</span>
              @elseif($mission->status === 1)
                <span class="badge bg-primary">تمت الموافقة</span>
              @elseif($mission->status === 2)
                <span class="badge bg-danger">مرفوضة</span>
              @elseif($mission->status === 3)
                <span class="badge bg-success">مكتملة</span>
              @else
                <span class="badge bg-secondary">غير معروف</span>
              @endif
            </td>
            <td>{{ $mission->deadline ?? '-' }}  {{ $mission->hour ?? '' }}</td>
            <td>{{ $mission->created_at ? $mission->created_at->format('Y-m-d H:i') : '-' }}</td>
            <td class="d-flex gap-2 justify-content-center">
              @php
                $isMyDepartment = auth()->user()->department_id == $department->id;
                $isDepartmentManager = auth()->user()->role == 'department_manager' && 
                                    $user && auth()->user()->department_id == $user->department_id;
                $isManager = auth()->user()->role == 'manager';
                $hasNoDepartment = auth()->user()->department_id === null;
                $isMissionCreator = auth()->user()->id == $mission->user_id;
                
                $canAccessMission = 
                  $isMyDepartment || // Can access if it's my department
                  $isMissionCreator || // Can access if I'm the creator
                  $isDepartmentManager || // Can access if I'm the department manager
                  $isManager || // Can access if I'm a manager
                  $hasNoDepartment; // Can access if I'm super admin/general manager
              @endphp
              
              @if($canAccessMission)
                <a href="{{ route('missions.reports', $mission->id) }}" class="btn btn-sm btn-primary">تقارير المهمة</a>

                @if(auth()->user()->department_id == $department->id && !$isManager)
                  <button class="btn btn-success btn-sm d-flex align-items-center gap-1 mx-1"
                    onclick="highlightMission(this)"
                    data-id="{{ $mission->id }}"
                    data-status="{{ $mission->status }}"
                    data-name="{{ $missionType ? $missionType->name : 'غير متوفر' }}"
                    data-asker="{{ $user ? $user->name : 'غير متوفر' }}">
                    <span class="fa fa-edit"></span> تحديث الحالة
                  </button>
                @endif
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@else
  <div class="alert alert-warning text-center">لا توجد مهام تطابق التصفية الحالية.</div>
@endif 