@extends('includes.master')

@section('title', 'الموظفين')
<span class="text-muted" id="emp-name"></span>
@section('content')
<div class="modal fade" id="addStandardModal" tabindex="-1" aria-labelledby="addStandardModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between align-items-center">
        <h5 class="modal-title" id="addStandardModalLabel">نسخ المعايير</h5>
        <span class="text-muted" id="emp-name"></span>
      </div>

      <div class="modal-body">
        <form method="POST" action="{{ route('department_manager.standards.copy') }}">
          @csrf
          <input type="hidden" id="from_id" name="from_id">

          <!-- Dynamic Container for Employee Checkboxes -->
          <div class="row" id="employeeCheckboxesContainer"></div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            <button type="submit" class="btn btn-primary">نسخ المعايير</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>



<section class="main profile">
    <div class="container">
        <div class="row">
            @include('includes.sidebar')
            <div class="col-lg-9 col-md-12">
                <div class="customer-content p-2 mb-5">
                    <div class="d-flex gap-2 align-items-center">
                        <button id="sidebar-mobile-toggle" class="btn btn-default fs-18 d-none" onclick="_toggle_customer_sidebar()" style="padding:9px 11px;"><span class="fa fa-bars"></span></button>

                        <h3 class="fw-bold"> تقييم موظفين القسم</h3>
                    </div>

                    <div class="profile-content settings">
                        <!-- Tab Navigation for Months -->
                        <ul class="nav nav-pills mb-3 px-0 py-3 bg-light" id="pills-tab" role="tablist">
                            @foreach ($months as $month)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $month == $selectedMonth ? 'active' : '' }}" 
                                       href="{{ route('department_manager.evaluation.index', ['month' => $month]) }}">
                                       {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <!-- Display Employee Evaluations for Selected Month -->
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-evaluation" role="tabpanel" aria-labelledby="pills-evaluation-tab">
                                @if($employees->count() > 0)
                                    <h5 class="mb-3">
                                        التقييمات الشهرية - 
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y') }}<br>
                                        <small class="text-muted">
                                            تم تقييم {{ $employees->filter(fn($emp) => $emp->evaluation)->count() }} من {{ $employees->count() }} موظف
                                        </small>
                                    </h5>

                                   
                                    @php
                                        // Check if all employees have an evaluation with status 1
                                        $allEvaluated = $employees->every(fn($emp) => $emp->evaluation && $emp->evaluation->status == 0);
                                        

                                        // Check if the selected month is the current month
                                        $isCurrentMonth = \Carbon\Carbon::now()->format('Y-m') == $selectedMonth;
                                    @endphp

                                    @if($allEvaluated && $isCurrentMonth)
                                    <form method="POST" action="{{ route('department_manager.evaluation.changeStatus') }}">
                                        @csrf
                                        <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}">
                                        <button type="submit" class="btn btn-success mb-3">
                                            تأكيد التقييمات لجميع الموظفين
                                        </button>
                                    </form>
                                    @endif

                                    <table class="table table-striped table-bordered text-center align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>اسم الموظف</th>
                                                <th>المنصب</th>
                                                <th>الدور</th>
                                                <th>التقييم العام</th>
                                                @if($isCurrentMonth)
                                                <th>أضافة معايير للموظف</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($employees as $employee)
                                            @php
                                                $evaluation = $employee->evaluation;
                                                $rating = $evaluation?->overall_rating;
                                            @endphp
                                            <tr>
                                                <td>{{ $employee->name }}</td>
                                                <td>{{ $employee->position }}</td>
                                                <td>
                                                    @if($employee->role == 'employee')
                                                        موظف
                                                    @elseif($employee->role == 'manager')
                                                        مدير
                                                    @elseif($employee->role == 'department_manager')
                                                        مدير قسم
                                                    @else
                                                        {{ $employee->role }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($rating) 
                                                        {{-- If there's a rating --}}
                                                        <span class="badge rounded-pill 
                                                            @if($rating >= 85) bg-success
                                                            @elseif($rating >= 70) bg-primary
                                                            @elseif($rating >= 50) bg-warning text-dark
                                                            @else bg-danger
                                                            @endif">
                                                            {{ $rating }} / 100
                                                        </span>
                                                        
                                                        @if ($evaluationInProgress)
                                                            {{-- If evaluation is in progress and there's a rating, show edit button --}}
                                                            <a href="{{ route('department_manager.evaluation.edit', ['evaluation' => $evaluation->id]) }}" 
                                                               class="btn btn-sm btn-outline-success">
                                                               <i class="fa fa-pencil"></i> تعديل التقييم
                                                            </a>
                                                        @endif
                                                    @elseif ($evaluationInProgress)
                                                        {{-- If there's no rating but evaluation is in progress --}}
                                                        <a href="{{ route('department_manager.evaluation.create', ['employee' => $employee->id, 'month' => $selectedMonth]) }}" 
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="fa fa-plus"></i> إضافة تقييم
                                                        </a>
                                                    @else
                                                        {{-- If no rating and no evaluation in progress --}}
                                                        <a href="{{ route('department_manager.evaluation.create', ['employee' => $employee->id, 'month' => $selectedMonth]) }}" 
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="fa fa-plus"></i> إضافة تقييم
                                                        </a>
                                                    @endif
                                                </td>
                                                
                                                @if($isCurrentMonth)
                                                <td>
                                                    <a href="{{ route('department_manager.standards.index',  $employee->id) }}" 
                                                    class="btn btn-sm btn-primary">
                                                        إدارة المعايير
                                                    </a>
                                                    
                                                    <button class="btn btn-dark btn-sm" onclick="highlightDepartment(this)" 
                                                    data-id="{{ $employee->id }}"
                                                    data-name="{{$employee->name}}">
                                                    <span class="fa fa-plus"></span> نقل المعايير الى
                                                </button>
                                                </td>
                                                @endif
    
                                            
                                           
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    <div class="pagination my-4">
                                        {{ $employees->appends(['month' => $selectedMonth])->links() }}
                                    </div>
                                @else
                                    <div class="alert alert-warning text-center mt-4">
                                        ⚠️ لا توجد تقييمات للموظفين في هذا الشهر.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</section>
@endsection
@section('js')
<script>
const employees = @json($employees);
const employeeList = employees.data; // Get the actual array

function renderEmployeeList(excludeId = null) {
    const container = document.getElementById('employeeCheckboxesContainer');
    if (!container) return;

    container.innerHTML = '';

    employeeList.forEach(employee => {
        if (employee.id == excludeId) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'radio-inputs gender-toggle';
        wrapper.id = `emp_${employee.id}`;

        wrapper.innerHTML = `
            <label class="w-100 cursor-pointer">
                <input 
                    class="radio-input d-none" 
                    type="checkbox" 
                    name="employee_ids[]" 
                    value="${employee.id}"
                >
                <span class="radio-tile">
                    <span class="radio-icon">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 101.47 122.88" style="enable-background:new 0 0 101.47 122.88" xml:space="preserve"><style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;}</style><g><path class="st0" d="M96.4,56.66l0.51,5.66c1.55,0.19,2.69,1.1,3.48,2.51c0.96,1.74,1.24,4.29,0.99,7.08 c-0.24,2.64-0.93,5.54-1.95,8.16c-1.8,4.61-4.67,8.41-8.05,8.88c-0.36,1.16-0.71,2.36-1.05,3.5c-1.73,5.83-3.08,10.4-6.62,14.89 c-3.94,4.99-9.03,8.8-14.42,11.4c-5.92,2.85-12.25,4.25-17.86,4.14c-5.3-0.11-11.33-1.59-17.04-4.36 c-5.2-2.52-10.15-6.1-14.07-10.68c-4.33-5.07-6.13-10.43-8.47-17.4c-0.27-0.81-0.55-1.64-0.81-2.41c-2.97-0.07-5.47-2.59-7.23-6.09 c-1.38-2.72-2.33-6.08-2.68-9.17c-0.37-3.26-0.06-6.32,1.13-8.26c0.83-1.37,2.01-2.23,3.55-2.37l0.5-5.04 c-0.6-3.61-1.29-7.4-1.81-11.02c-0.14-1-0.23-1.97-0.28-2.93C2.07,43.57,0.31,41.56,0,35.92v-9.11 C1.32,15.92,6.39,11.55,14.71,12.8c7.92-8.28,19.1-12.9,34.5-12.78c17.15-0.34,31.77,3.66,42.38,14.53 c5.58,5.56,7.96,14.17,6.52,26.42c0.06,2.32-0.08,4.76-0.45,7.33C97.25,51.08,96.8,53.95,96.4,56.66L96.4,56.66z M36.11,77.78 c0.07,0.26,0.11,0.53,0.11,0.8c0,1.69-1.37,3.07-3.07,3.07c-1.69,0-3.07-1.37-3.07-3.07c0-0.51,0.12-0.99,0.34-1.41 c-1.63,0.11-3.26,0.47-4.9,1.11c-0.7,0.27-1.46-0.15-1.7-0.94c-0.24-0.79,0.13-1.66,0.83-1.93c2.31-0.9,4.63-1.32,6.96-1.3 c2.31,0.02,4.62,0.47,6.92,1.3c0.7,0.25,1.09,1.1,0.87,1.9c-0.22,0.8-0.97,1.24-1.67,0.98C37.2,78.1,36.65,77.93,36.11,77.78 L36.11,77.78z M46.47,89.11c-0.62-0.56-0.66-1.52-0.1-2.14c0.56-0.62,1.52-0.66,2.14-0.1c0.89,0.81,1.73,1.2,2.53,1.19 c0.81-0.01,1.67-0.42,2.58-1.21c0.63-0.55,1.58-0.48,2.13,0.15c0.55,0.63,0.48,1.58-0.15,2.13c-1.46,1.26-2.97,1.93-4.53,1.95 C49.48,91.1,47.95,90.46,46.47,89.11L46.47,89.11z M90.8,74.66c-0.27-0.47-0.11-1.08,0.36-1.35c0.47-0.27,1.08-0.11,1.35,0.36 c1.01,1.76,1.18,3.53-0.04,5.32c-0.31,0.45-0.92,0.57-1.37,0.26c-0.45-0.31-0.57-0.92-0.26-1.37 C91.55,76.84,91.43,75.76,90.8,74.66L90.8,74.66z M9.45,73.68c0.27-0.47,0.88-0.64,1.35-0.36c0.47,0.27,0.64,0.88,0.36,1.35 c-0.63,1.1-0.75,2.18-0.04,3.23c0.31,0.45,0.19,1.07-0.26,1.37C10.41,79.57,9.8,79.45,9.49,79C8.27,77.2,8.44,75.43,9.45,73.68 L9.45,73.68z M80.59,69.21c0.64,0.53,0.74,1.48,0.21,2.12c-0.53,0.64-1.48,0.74-2.12,0.21c-2.39-1.97-7.2-1.1-11.34-0.36 c-2.2,0.4-4.23,0.76-5.85,0.7c-0.83-0.03-1.48-0.73-1.45-1.56c0.03-0.83,0.73-1.48,1.56-1.46c1.31,0.05,3.18-0.29,5.21-0.66 C71.57,67.35,77.13,66.35,80.59,69.21L80.59,69.21z M23.6,71.87c-0.64,0.53-1.59,0.44-2.12-0.21c-0.53-0.64-0.44-1.59,0.21-2.12 c3.46-2.86,9.02-1.86,13.79-1c2.03,0.37,3.91,0.7,5.21,0.66c0.83-0.03,1.53,0.62,1.56,1.46c0.03,0.83-0.62,1.53-1.46,1.56 c-1.62,0.06-3.65-0.31-5.85-0.7C30.81,70.76,25.99,69.9,23.6,71.87L23.6,71.87z M77.72,75.07c0.71,0.23,1.12,1.07,0.92,1.87 c-0.2,0.81-0.94,1.27-1.65,1.04c-1.65-0.54-3.28-0.86-4.89-0.98c0.17,0.38,0.26,0.8,0.26,1.25c0,1.69-1.37,3.07-3.07,3.07 c-1.69,0-3.07-1.37-3.07-3.07c0-0.26,0.03-0.51,0.09-0.75c-0.55,0.13-1.1,0.29-1.64,0.47c-0.71,0.23-1.44-0.23-1.65-1.03 c-0.2-0.8,0.2-1.64,0.91-1.87c2.26-0.75,4.55-1.13,6.85-1.13C73.1,73.94,75.41,74.31,77.72,75.07L77.72,75.07z M8.7,65.21l4.4,2.47 l2.28-13.75c11.18,7.04,23.19,9.79,35.72,9.88c13.09-0.33,25.11-3.26,35.65-9.83l2.09,14.21l4.99-3.12 c0.34,0.56,0.97,0.9,1.66,0.84c0.07-0.01,0.14-0.02,0.21-0.03l0,0c0.8-0.17,1.33,0.1,1.63,0.66c0.6,1.09,0.76,2.93,0.56,5.07 c-0.21,2.29-0.82,4.85-1.73,7.18c-1.51,3.86-3.68,7.02-5.75,6.65c-0.93-0.16-1.82,0.44-2.02,1.35c-0.56,1.76-1,3.23-1.41,4.62 l-9.47,7.78c-2.52,3.11-5.66,3.27-9.36-0.12c-6.72-7.56-27.68-6.06-34.13,0c-2.62,2.78-5.52,3.45-8.93,0.12l-8.81-6.72 c-0.35-1.02-0.7-2.07-1.07-3.16c-0.37-1.11-0.76-2.26-1.28-3.78c-0.3-0.89-1.25-1.37-2.14-1.12l0,0c-1.72,0.49-3.46-1.39-4.81-4.06 C5.76,78,4.94,75.08,4.63,72.38c-0.29-2.54-0.13-4.8,0.63-6.04c0.33-0.55,0.88-0.8,1.7-0.6C7.61,65.9,8.27,65.68,8.7,65.21 L8.7,65.21z M46.12,105.28c-0.87-0.43-1.23-1.48-0.81-2.35c0.43-0.87,1.48-1.23,2.35-0.81c1.47,0.72,2.77,1.06,4.03,1.05 c1.27-0.01,2.57-0.37,4.05-1.07c0.88-0.41,1.93-0.03,2.34,0.85c0.41,0.88,0.03,1.93-0.85,2.34c-1.93,0.91-3.71,1.39-5.52,1.4 C49.89,106.7,48.09,106.24,46.12,105.28L46.12,105.28z"/></g></svg>
                    </span>
                    <span class="radio-label">${employee.name}</span>
                </span>
            </label>
        `;

        container.appendChild(wrapper);
    });
}


    function highlightDepartment(button) {
        const empId = button.getAttribute('data-id');
        const empName = button.getAttribute('data-name');

        document.getElementById('from_id').value = empId;
        document.getElementById('emp-name').innerHTML = empName;

        renderEmployeeList(empId); // Render list excluding the selected employee

        const modal = new bootstrap.Modal(document.getElementById('addStandardModal'));
        modal.show();
    }
</script>



@endsection