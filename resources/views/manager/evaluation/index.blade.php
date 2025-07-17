@extends('includes.master')

@section('title', 'تقييم مديري الأقسام والموظفين')

@section('content')
<div class="modal fade" id="addStandardModal" tabindex="-1" aria-labelledby="addStandardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="addStandardModalLabel">نسخ المعايير</h5>
                <span class="text-muted" id="emp-name"></span>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('standards.copy') }}">
                    @csrf
                    <input type="hidden" id="from_id" name="from_id">
                    <div class="row" id="employeeCheckboxesContainer"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">إضافة المعيار</button>
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
                    <div class="d-flex gap-1 align-items-center mb-4">
                        <button id="sidebar-mobile-toggle" class="btn btn-default fs-18 d-none" onclick="_toggle_customer_sidebar()" style="padding:4px 11px;"><span class="fa fa-bars"></span></button>
                        <h3 class="fw-bold">تقييم مديري الأقسام والموظفين</h3>
                    </div>

                    <div class="profile-content settings">
                        <!-- Month Navigation -->
                        <div class="month-navigation mb-4">
                            <ul class="nav nav-pills" id="pills-tab" role="tablist">
                                @foreach ($months as $month)
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link {{ $month == $selectedMonth ? 'active' : '' }}" 
                                           href="{{ route('manager.evaluation.index', ['month' => $month]) }}">
                                           {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-evaluation" role="tabpanel">
                                @if($employees->count() > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5>
                                            التقييمات الشهرية - 
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y') }}<br>
                                            <small class="text-muted">
                                                تم تقييم {{ $employees->filter(fn($emp) => $emp->evaluation)->count() }} من {{ $employees->count() }} شخص
                                            </small>
                                        </h5>

                                        @if($allEvaluated && $selectedMonth === $currentMonth)
                                            <form method="POST" action="{{ route('manager.evaluation.changeStatus') }}">
                                                @csrf
                                                <button type="submit" class="btn btn-success">
                                                    تأكيد جميع التقييمات
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered text-center align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th class="text-nowrap">اسم الموظف</th>
                                                    <th class="text-nowrap">المنصب</th>
                                                    <th class="text-nowrap">الدور</th>
                                                    <th class="text-nowrap">القسم</th>

                                                    <th class="text-nowrap">التقييم العام</th>
                                                    <th class="text-nowrap">حالة التقييم</th>
                                                    @if($selectedMonth === $currentMonth && auth()->check() && auth()->user()->status == 1)
                                                        <th class="text-nowrap">المعايير</th>
                                                    @endif
                                                    <th class="text-nowrap">الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($employees as $employee)
                                                @php
                                                    $evaluation = $employee->evaluation;
                                                    $rating = $evaluation?->overall_rating;
                                                    $status = $evaluation?->status;
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
                                                    <td>{{ $employee->department->name }}</td>

                                                    <td class="text-nowrap">
                                                        @if ($rating) 
                                                            <span class="badge rounded-pill 
                                                                @if($rating >= 85) bg-success
                                                                @elseif($rating >= 70) bg-primary
                                                                @elseif($rating >= 50) bg-warning text-dark
                                                                @else bg-danger
                                                                @endif">
                                                                {{ $rating }} / 100
                                                            </span>
                                                            
                                                            @if ($evaluationInProgress && $selectedMonth === $currentMonth && $employee->status == 1)
                                                                <a href="{{ route('manager.evaluation.edit', ['evaluation' => $evaluation->id]) }}" 
                                                                   class="btn btn-sm btn-outline-success ms-2">
                                                                    <i class="fa fa-pencil"></i> تعديل
                                                                </a>
                                                            @endif
                                                        @elseif ($selectedMonth === $currentMonth && $employee->status == 1)
                                                            <a href="{{ route('manager.evaluation.create', ['employee' => $employee->id, 'month' => $selectedMonth]) }}" 
                                                                class="btn btn-sm btn-outline-primary">
                                                                <i class="fa fa-plus"></i> إضافة تقييم
                                                            </a>
                                                        @else
                                                            <span class="badge bg-secondary">لم يتم التقييم</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(is_null($status))
                                                            <span class="badge bg-secondary">غير متوفر</span>
                                                        @elseif ($status == 0)
                                                            <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                                        @elseif ($status == 1)
                                                            <div>
                                                                <span class="badge bg-info mb-2">تم التقييم</span>
                                                                @if($selectedMonth === $currentMonth)
                                                                    <br>
                                                                    <a href="{{ route('manager.evaluation.edit', ['evaluation' => $evaluation->id]) }}" 
                                                                       class="btn btn-sm btn-outline-success">
                                                                        <i class="fa fa-edit"></i> تعديل 
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        @elseif ($status == 2)
                                                            <span class="badge bg-success">تم التأكيد</span>
                                                        @endif
                                                    </td>

                                                    @if($selectedMonth === $currentMonth && auth()->check() && auth()->user()->status == 1)
                                                        <td class="text-nowrap">
                                                            <a href="{{ route('manager.standards.index', $employee->id) }}" 
                                                               class="btn btn-sm btn-primary">
                                                                إدارة المعايير
                                                            </a>
                                                            
                                                            <button class="btn btn-dark btn-sm" onclick="highlightDepartment(this)" 
                                                                data-id="{{ $employee->id }}"
                                                                data-name="{{ $employee->name }}">
                                                                <span class="fa fa-copy"></span> نسخ المعايير
                                                            </button>
                                                        </td>
                                                    @endif

                                                    <td class="text-nowrap">
                                                        @if($evaluation)
                                                            <a href="{{ route('manager.employee.evaluation', ['employee' => $employee->id, 'month' => $selectedMonth]) }}" 
                                                               class="btn btn-sm btn-primary">
                                                                <i class="fa fa-eye"></i> عرض التقييم
                                                            </a>
                                                        @else
                                                            @if($selectedMonth === $currentMonth)
                                                                <a href="{{ route('manager.evaluation.create', ['employee' => $employee->id, 'month' => $selectedMonth]) }}" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fa fa-plus"></i> إضافة تقييم
                                                                </a>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $employees->links() }}
                                    </div>
                                @else
                                    <div class="alert alert-warning text-center">
                                        ⚠️ لا يوجد موظفين للتقييم في هذا الشهر.
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
const employeeList = employees.data;

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
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 101.47 122.88" style="enable-background:new 0 0 101.47 122.88" xml:space="preserve"><style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;}</style><g><path class="st0" d="M96.4,56.66l0.51,5.66c1.55,0.19,2.69,1.1,3.48,2.51c0.96,1.74,1.24,4.29,0.99,7.08 c-0.24,2.64-0.93,5.54-1.95,8.16c-1.8,4.61-4.67,8.41-8.05,8.88c-0.36,1.16-0.71,2.36-1.05,3.5c-1.73,5.83-3.08,10.4-6.62,14.89 c-3.94,4.99-9.03,8.8-14.42,11.4c-5.92,2.85-12.25,4.25-17.86,4.14c-5.3-0.11-11.33-1.59-17.04-4.36 c-5.2-2.52-10.15-6.1-14.07-10.68c-4.33-5.07-6.13-10.43-8.47-17.4c-0.27-0.81-0.55-1.64-0.81-2.41c-2.97-0.07-5.47-2.59-7.23-6.09 c-1.38-2.72-2.33-6.08-2.68-9.17c-0.37-3.26-0.06-6.32,1.13-8.26c0.83-1.37,2.01-2.23,3.55-2.37l0.5-5.04 c-0.6-3.61-1.29-7.4-1.81-11.02c-0.14-1-0.23-1.97-0.28-2.93C2.07,43.57,0.31,41.56,0,35.92v-9.11 C1.32,15.92,6.39,11.55,14.71,12.8c7.92-8.28,19.1-12.9,34.5-12.78c17.15-0.34,31.77,3.66,42.38,14.53 c5.58,5.56,7.96,14.17,6.52,26.42c0.06,2.32-0.08,4.76-0.45,7.33C97.25,51.08,96.8,53.95,96.4,56.66L96.4,56.66z"/></g></svg>
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

    renderEmployeeList(empId);

    const modal = new bootstrap.Modal(document.getElementById('addStandardModal'));
    modal.show();
}
</script>
@endsection
