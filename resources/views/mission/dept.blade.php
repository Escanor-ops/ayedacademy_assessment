@extends('includes.master')

@section('title', 'مهام القسم')

@section('content')
<style>
  .btn.active{
    background:#198754 !important;
    border-color:none !important !important;
  }
  .pagination{
    align-self:flex-end;
  }
  /* Improved input group styling */
  .mission-type-input-group .form-control {
    border-radius: 4px;
    height: 35px;
    font-size: 14px;
  }
  .mission-type-input-group .btn {
    border-radius: 4px;
    height: 35px;
    padding: 0 15px;
    font-size: 14px;
    white-space: nowrap;
  }
  /* Add custom purple color since Bootstrap doesn't have it by default */
  .text-purple {
    color: #6f42c1 !important;
  }
  
  /* Animation classes */
  @keyframes highlightFadeIn {
    0% { background-color: transparent; }
    20% { background-color: #d1e7dd; }
    80% { background-color: #d1e7dd; }
    100% { background-color: transparent; }
  }
  
  .new-mission {
    animation: highlightFadeIn 30s ease-in-out;
  }

  .animate__animated {
    animation-duration: 1s;
    animation-fill-mode: both;
  }
  
  .animate__fadeIn {
    animation-name: fadeIn;
  }
  
  /* Smooth transition for table updates */
  #missions-table-container {
    transition: opacity 0.3s ease-in-out;
  }
  #missions-table-container.loading {
    opacity: 0.6;
  }

  /* Persistent highlight for new missions */
  tr.new-mission {
    background-color: #d1e7dd !important;
    position: relative;
  }
  
  tr.new-mission::after {
    content: 'جديد';
    position: absolute;
    top: 0;
    right: -4px;
    background: #198754;
    color: white;
    padding: 2px 8px;
    font-size: 12px;
    border-radius: 0 4px 0 4px;
    opacity: 0.9;
  }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<!-- Initialize Bootstrap tooltips -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
</script>

<!-- Modal -->
<div class="modal fade" id="missionModal" tabindex="-1" aria-labelledby="missionModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between">
        <h5 class="modal-title" id="addStandardModalLabel">إضافة مهمة جديدة</h5>
      </div>
      <div class="modal-body">
        <form action="{{ route('mission.add') }}" method="POST">
          @csrf
          <input type="hidden" name="department_id" value="{{ $department->id }}">

          <div class="mb-3">
            <label for="mission_type" class="form-label">
              نوع المهمة 
              <span style="color: red">*</span>
            </label>

            <select id="mission_type" name="mission_type_id" class="form-select" required>
              <option value="">اختر نوع المهمة</option>
              @foreach($missionTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">
              وصف المهمة
              <span style="color: red">*</span>
            </label>
            <textarea id="description" name="description" class="form-control" rows="3" placeholder="من فضلك اجعل وصفك للطلب واضحا" required></textarea>
          </div>

          <div class="mb-3">
            <label for="mission_link">لينك المهمة</label>
            <input type="text" class="form-control" name="link" placeholder="يمكنك أرفاق لينك هنا أذا توفر">
          </div>

          <div class="mb-3">
            <label for="mission_date">تحديد موعد</label>
            <div class="d-flex gap-2">
              <input type="date" name="date" class="form-control">
              <input type="time" name="hour" class="form-control">
            </div>
          </div>

          <div class="mb-3 text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-success">ارسال المهمة</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Update Mission Status Modal -->
<div class="modal fade" id="updateMissionStatusModal" tabindex="-1" aria-labelledby="updateMissionStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-light">تحديث حالة المهمة</h5>
              
            </div>
            <div class="modal-body">
                 <div class="my-2 p-2 text-light bg-danger">
                  <span id="mission_asker"></span>
                  <br>
                  <span  id="mission_typeName"></span>
                </div>
                <form method="POST" id="updateMissionStatusForm" action="{{route('mission.updateStatus')}}">
                    @csrf
                    <input type="hidden" id="mission_id" name="mission_id">
                    <input type="hidden" id="mission_status" name="status">

                    <div class="mb-3 py-2">
                        <div class="btn-group w-100 text-white" role="group">
                            <button type="button" class="btn rounded-0 border-0 status-tab text-light" data-status="0">في الانتظار</button>
                            <button type="button" class="btn rounded-0 border-0 status-tab text-light" data-status="1">تمت الموافقة</button>
                            <button type="button" class="btn rounded-0 border-0  status-tab text-light " data-status="3">مكتملة</button>
                            <button type="button" class="btn rounded-0 border-0 status-tab text-light" data-status="2">مرفوضة</button>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-success">تحديث الحالة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Mission Types Management Modal -->
<div class="modal fade" id="missionTypesModal" tabindex="-1" aria-labelledby="missionTypesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">إدارة أنواع المهام</h5>
      </div>
      <div class="modal-body">
        @if(auth()->user()->role == 'department_manager' && auth()->user()->department_id == $department->id)
          <form id="addMissionTypeForm" onsubmit="return false;" class="mb-4">
            <input type="hidden" name="department_id" value="{{ $department->id }}">
            <div class="mission-type-input-group d-flex align-items-center gap-2">
              <input type="text" name="name" class="form-control" placeholder="اسم نوع المهمة الجديد" required>
              <button type="button" onclick="addMissionType(this)" class="btn btn-success">إضافة نوع جديد</button>
            </div>
          </form>
        @endif

        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead class="table-dark">
              <tr>
                <th width="5%">#</th>
                <th width="45%">نوع المهمة</th>
                <th width="15%">الحالة</th>
                <th width="20%">تاريخ الإنشاء</th>
                @if(auth()->user()->role == 'department_manager' && auth()->user()->department_id == $department->id)
                  <th width="15%">الإجراءات</th>
                @endif
              </tr>
            </thead>
            <tbody id="missionTypesTableBody">
              @foreach($allMissionTypes as $type)
                <tr>
                  <td class="text-center">{{ $loop->iteration }}</td>
                  <td>{{ $type->name }}</td>
                  <td class="text-center">
                    @if($type->status == 0)
                      <span class="badge bg-success">نشط</span>
                    @else
                      <span class="badge bg-danger">غير نشط</span>
                    @endif
                  </td>
                  <td class="text-center">{{ $type->created_at->format('Y-m-d') }}</td>
                  @if(auth()->user()->role == 'department_manager' && auth()->user()->department_id == $department->id)
                    <td class="text-center">
                      <button type="button" 
                              class="btn btn-sm w-75 {{ $type->status == 0 ? 'btn-danger' : 'btn-success' }}"
                              onclick="toggleMissionTypeStatus({{ $type->id }}, this)"
                              data-status="{{ $type->status }}">
                        {{ $type->status == 0 ? 'تعطيل' : 'تفعيل' }}
                      </button>
                    </td>
                  @endif
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>

<section class="main profile">
  <div class="container-xl">
    <div class="row">
      @include('includes.sidebar')

      <div class="col-lg-9 col-md-12">
        <div class="customer-content p-2 mb-5">
          @if(auth()->user()->role == 'department_manager' && auth()->user()->department_id == $department->id)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h3 class="fw-bold mb-0">مهام قسم: {{ $department->name }}</h3>
              <div>
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#missionTypesModal">
                  إدارة أنواع المهام
                </button>
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#missionModal">
                  إضافة مهمة جديدة
                </button>
              </div>
            </div>
          @else
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h3 class="fw-bold mb-0">مهام قسم: {{ $department->name }}</h3>
              <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#missionModal">
                إضافة مهمة جديدة
              </button>
            </div>
          @endif

          <!-- FILTERS WITH TABS START -->
          <form method="GET" class="mb-4">

                <!-- Asker Tabs -->
                <label class="form-label fw-bold d-block mb-1">تصفية حسب الطالب:</label>
                <ul class="nav nav-pills mb-3">
                  @php
                    $askerFilter = request('asker');
                    $isMyDepartment = auth()->user()->department_id == $department->id;
                    $hasNoDepartment = auth()->user()->department_id === null; // Super admin/general manager
                  @endphp
                  @if($isMyDepartment)
                    <li class="nav-item">
                      <a class="nav-link m-0 {{ is_null($askerFilter) || $askerFilter === '' ? 'active' : '' }}" href="?{{ http_build_query(array_merge(request()->except(['asker', 'page']))) }}">كل المستخدمين</a>
                    </li>
                  @endif
                  <li class="nav-item">
                    <a class="nav-link m-0 {{ (!$isMyDepartment && (is_null($askerFilter) || $askerFilter === '')) || $askerFilter === 'me' ? 'active' : '' }}" href="?{{ http_build_query(array_merge(request()->except(['asker', 'page']), ['asker' => 'me'])) }}">أنا</a>
                  </li>
                  @if(auth()->user()->department_id !== null)
                    <li class="nav-item">
                      <a class="nav-link m-0 {{ $askerFilter === 'my_department' ? 'active' : '' }}" href="?{{ http_build_query(array_merge(request()->except(['asker', 'page']), ['asker' => 'my_department'])) }}">قسمي</a>
                    </li>
                  @endif
                  @if(auth()->user()->role != 'employee')
                    <li class="nav-item">
                      <a class="nav-link m-0 {{ $askerFilter === 'others' ? 'active' : '' }}" href="?{{ http_build_query(array_merge(request()->except(['asker', 'page']), ['asker' => 'others'])) }}">أقسام أخرى</a>
                    </li>
                  @endif
                </ul>

                <!-- Status Tabs -->
                <label class="form-label fw-bold d-block mb-1">تصفية حسب الحالة:</label>
                <ul class="nav nav-pills mb-3">
                  @php
                    $statusFilter = request('status');
                    // For employees viewing other departments, force asker=me
                    if(auth()->user()->role == 'employee' && !$isMyDepartment) {
                      $askerFilter = 'me';
                    }
                  @endphp
                  <li class="nav-item">
                    <a class="nav-link m-0 {{ is_null($statusFilter) || $statusFilter === '' ? 'active' : '' }}" 
                       href="?{{ http_build_query(array_merge(request()->except(['status', 'page']), 
                         (!$isMyDepartment && auth()->user()->role == 'employee') ? ['asker' => $askerFilter] : [])) }}">جميع المهام</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link m-0 {{ $statusFilter === '0' ? 'active' : '' }}" 
                       href="?{{ http_build_query(array_merge(request()->except(['status', 'page']), 
                         ['status' => '0'], 
                         (!$isMyDepartment && auth()->user()->role == 'employee') ? ['asker' => $askerFilter] : [])) }}">في الانتظار</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link m-0 {{ $statusFilter === '1' ? 'active' : '' }}" 
                       href="?{{ http_build_query(array_merge(request()->except(['status', 'page']), 
                         ['status' => '1'], 
                         (!$isMyDepartment && auth()->user()->role == 'employee') ? ['asker' => $askerFilter] : [])) }}">تمت الموافقة</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link m-0 {{ $statusFilter === '2' ? 'active' : '' }}" 
                       href="?{{ http_build_query(array_merge(request()->except(['status', 'page']), 
                         ['status' => '2'], 
                         (!$isMyDepartment && auth()->user()->role == 'employee') ? ['asker' => $askerFilter] : [])) }}">مرفوضة</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link m-0 {{ $statusFilter === '3' ? 'active' : '' }}" 
                       href="?{{ http_build_query(array_merge(request()->except(['status', 'page']), 
                         ['status' => '3'], 
                         (!$isMyDepartment && auth()->user()->role == 'employee') ? ['asker' => $askerFilter] : [])) }}">مكتملة</a>
                  </li>
                </ul>

                </form>

          <!-- FILTERS WITH TABS END -->

          <div class="profile-content settings" id="missions-table-container">
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
                      <!-- <th>الوصف</th> -->
                      <th>صاحب الطلب</th>
                      
                      <th>الحالة</th>
                      <th>موعد المهمة</th>
                      <th>تاريخ الإنشاء</th>
                      <th>الإجراء</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($missions as $index => $mission)
                      <tr>
                        <td>{{ $missions->firstItem() + $index }}</td>
                        <td>{{ $mission->missionType->name }}</td>
                        <td>{{ $mission->ticket_number }}</td>
                        <!-- <td>{{ $mission->description }}</td> -->
                        
                        <td>
                          @php
                            $userRole = $mission->user->role;
                            $isMyDepartment = $mission->user->department_id == auth()->user()->department_id;
                            
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
                            {{ $mission->user->name }}
                          </span>
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
                        <td>{{$mission->deadline}}  {{$mission->time}}</td>
                        <td>{{ $mission->created_at->format('Y-m-d H:i') }}</td>
                        <td class="d-flex gap-2 justify-content-center">
                          @php
                            $isMyDepartment = auth()->user()->department_id == $department->id;
                            $isDepartmentManager = auth()->user()->role == 'department_manager' && 
                                                auth()->user()->department_id == $mission->user->department_id;
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
                                data-name="{{ $mission->missionType->name }}"
                                data-asker="{{ $mission->user->name }}">
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
              <div class="alert alert-info text-center">لا توجد مهام تطابق التصفية الحالية.</div>
            @endif
          </div> <!-- profile-content settings -->
        </div> <!-- customer-content -->
      </div> <!-- col-lg-9 -->
    </div> <!-- row -->
  </div> <!-- container -->
</section>

@endsection
@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let lastMissionId = {{ $missions->max('id') ?? 0 }};
    const missionsContainer = document.querySelector('#missions-table-container');
    let newMissionIds = new Set(JSON.parse(localStorage.getItem('newMissionIds') || '[]'));
    let isRefreshing = false;
    
    function getCurrentFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            status: urlParams.get('status'),
            asker: urlParams.get('asker')
        };
    }

    async function refreshMissions() {
        if (!missionsContainer || isRefreshing) return;
        
        // Don't refresh if we're not in our department and we're an employee
        const isEmployee = '{{ auth()->user()->role }}' === 'employee';
        const isMyDepartment = {{ auth()->user()->department_id == $department->id ? 'true' : 'false' }};
        if (isEmployee && !isMyDepartment) return;

        isRefreshing = true;
        missionsContainer.classList.add('loading');
        
        try {
            const filters = getCurrentFilters();
            const url = new URL('{{ route("department.missions.data", $department->id) }}');
            url.searchParams.append('last_mission_id', lastMissionId);
            url.searchParams.append('newMissionIds', JSON.stringify(Array.from(newMissionIds)));
            if (filters.status) url.searchParams.append('status', filters.status);
            if (filters.asker) url.searchParams.append('asker', filters.asker);

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                if (response.status === 401) {
                    // Session expired, reload the page
                    window.location.reload();
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Received non-JSON response from server');
            }

            const data = await response.json();
            
            if (data.html) {
                missionsContainer.innerHTML = data.html;
                
                if (data.newMissions && data.newMissions.length > 0) {
                    data.newMissions.forEach(id => newMissionIds.add(id));
                    localStorage.setItem('newMissionIds', JSON.stringify(Array.from(newMissionIds)));
                }
                
                lastMissionId = data.lastMissionId;
                
                initializeTooltips();
                initializeEventHandlers();
            }
        } catch (error) {
            console.error('Error during refresh:', error);
            // If we get multiple errors, slow down the refresh rate
            if (window.refreshInterval) {
                clearInterval(window.refreshInterval);
                window.refreshInterval = setInterval(refreshMissions, 30000); // Increase to 30 seconds
            }
        } finally {
            isRefreshing = false;
            missionsContainer.classList.remove('loading');
        }
    }

    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    function initializeEventHandlers() {
        document.querySelectorAll('[onclick*="highlightMission"]').forEach(button => {
            button.onclick = function() {
                highlightMission(this);
            };
        });
    }

    // Initial setup
    initializeTooltips();
    initializeEventHandlers();

    // Set up auto-refresh every 10 seconds
    window.refreshInterval = setInterval(refreshMissions, 10000);

    // Clear new missions on manual page refresh
    window.addEventListener('beforeunload', function() {
        localStorage.removeItem('newMissionIds');
    });

    // Handle visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (window.refreshInterval) {
                clearInterval(window.refreshInterval);
            }
        } else {
            refreshMissions(); // Immediate refresh when tab becomes visible
            window.refreshInterval = setInterval(refreshMissions, 10000);
        }
    });
});

function highlightMission(button) {
    let missionId = button.getAttribute('data-id');
    let missionStatus = button.getAttribute('data-status');
    let missionType = button.getAttribute('data-name');
    let missionAsker = button.getAttribute('data-asker');
    // Set mission id
    document.getElementById('mission_id').value = missionId;

    // Preselect status tab
    let tabs = document.querySelectorAll('.status-tab');
    tabs.forEach(function(tab) {
        tab.classList.remove('active');
        if (tab.getAttribute('data-status') === missionStatus) {
            tab.classList.add('active');
        }
    });

    // Set hidden input value
    document.getElementById('mission_status').value = missionStatus;
    document.getElementById('mission_typeName').innerHTML = missionType;
    document.getElementById('mission_asker').innerHTML = missionAsker;

    // Make tabs clickable & update hidden input + active class
    tabs.forEach(function(tab) {
        tab.onclick = function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('mission_status').value = this.getAttribute('data-status');
        };
    });

    // Open modal
    var modal = new bootstrap.Modal(document.getElementById('updateMissionStatusModal'));
    modal.show();
}

// Function to refresh mission types table
async function refreshMissionTypesTable() {
    try {
        const response = await fetch('{{ route('mission-types.list', $department->id) }}');
        const html = await response.text();
        document.getElementById('missionTypesTableBody').innerHTML = html;
    } catch (error) {
        showNotification('error', 'حدث خطأ أثناء تحديث الجدول');
    }
}

// Function to add new mission type
async function addMissionType(button) {
    const form = document.getElementById('addMissionTypeForm');
    const nameInput = form.querySelector('input[name="name"]');
    const originalText = button.textContent;
    
    if (!nameInput.value.trim()) {
        showNotification('error', 'يرجى إدخال اسم نوع المهمة');
        return;
    }

    button.disabled = true;
    button.textContent = 'جاري الإضافة...';

    try {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const response = await fetch('{{ route('mission-types.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            nameInput.value = '';
            await refreshMissionTypesTable();
            showNotification('success', result.message);
        } else {
            showNotification('error', result.message);
        }
    } catch (error) {
        showNotification('error', 'حدث خطأ أثناء إضافة نوع المهمة');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

// Function to toggle mission type status
async function toggleMissionTypeStatus(typeId, button) {
    try {
        // Disable button and show loading state
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'جاري التحديث...';

        // Prepare form data
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('_method', 'PATCH');

        // Make the request with the correct URL format
        const toggleUrl = `{{ url('mission-types') }}/${typeId}/toggle-status`;
        
        const response = await fetch(toggleUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        // Handle different response statuses
        if (response.status === 404) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'نوع المهمة غير موجود في قاعدة البيانات'
            });
            return;
        }

        if (response.status === 403) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'ليس لديك صلاحية لتعديل حالة هذا النوع من المهام'
            });
            return;
        }

        const result = await response.json();
        
        if (result.success) {
            // Update button appearance immediately
            const currentStatus = button.getAttribute('data-status');
            const newStatus = currentStatus == '0' ? '1' : '0';
            button.setAttribute('data-status', newStatus);
            button.className = `btn btn-sm w-75 ${newStatus == '0' ? 'btn-danger' : 'btn-success'}`;
            button.textContent = newStatus == '0' ? 'تعطيل' : 'تفعيل';
            
            // Refresh table and show success message
            await refreshMissionTypesTable();
            Swal.fire({
                icon: 'success',
                title: 'تم',
                text: result.message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: result.message || 'حدث خطأ أثناء تحديث الحالة'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: 'حدث خطأ أثناء الاتصال بالخادم'
        });
    } finally {
        // Re-enable button and restore original text
        button.disabled = false;
        button.textContent = button.getAttribute('data-status') == '0' ? 'تعطيل' : 'تفعيل';
    }
}
</script>