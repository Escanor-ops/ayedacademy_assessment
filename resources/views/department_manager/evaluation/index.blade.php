@extends('includes.master')

@section('title', 'التقييمات')

@section('content')
<div class="modal fade" id="addStandardModal" tabindex="-1" aria-labelledby="addStandardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="addStandardModalLabel">نسخ المعايير</h5>
                <span class="text-muted" id="emp-name"></span>
            </div>
            <div class="modal-body" style="max-height:500px; overflow-y:auto;">
            <form method="POST" action="{{ route('standards.copy') }}">
                    @csrf
                    <input type="hidden" id="from_id" name="from_id">
                    <div class="row" id="employeeCheckboxesContainer"></div>
                    
                
            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">إضافة المعيار</button>
                    </div>
                    </form>
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

                        <h3 class="fw-bold">التقييمات</h3>
                    </div>

                    <div class="profile-content settings">
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

                                        @if($isCurrentMonth && $allEvaluated)
                                            <form method="POST" action="{{ route('department_manager.evaluation.changeStatus') }}">
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
                                                    <!-- <th class="text-nowrap">الدور</th> -->
                                                    <!-- <th class="text-nowrap">القسم</th> -->
                                                    <th class="text-nowrap">التقييم العام</th>
                                                    <th class="text-nowrap">حالة التقييم</th>
                                                    @if($isCurrentMonth)
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
                                                        <td class="text-nowrap">{{ $employee->name }}</td>
                                                        <td class="text-nowrap">{{ $employee->position }}</td>
                                                        
                                                        <!-- <td class="text-nowrap">{{ $employee->department->name }}</td> -->
                                                        <td class="text-nowrap">
                                                            @if($rating)
                                                                <span class="badge rounded-pill 
                                                                    @if($rating >= 85) bg-success
                                                                    @elseif($rating >= 70) bg-primary
                                                                    @elseif($rating >= 50) bg-warning text-dark
                                                                    @else bg-danger
                                                                    @endif">
                                                                    {{ $rating }} / 100
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary">لم يتم التقييم</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-nowrap">
                                                            @if(is_null($status))
                                                                <span class="badge bg-secondary">لم يبدأ</span>
                                                            @elseif($status == 0)
                                                                <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                                            @elseif($status == 1)
                                                                <span class="badge bg-info">تم التقييم</span>
                                                            @elseif($status == 2)
                                                                <span class="badge bg-success">تم التأكيد</span>
                                                            @endif
                                                        </td>
                                                        @if($isCurrentMonth)
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 justify-content-center">
                                                                    <a href="{{ route('department_manager.standards.index', $employee->id) }}" 
                                                                       class="btn btn-sm btn-primary">
                                                                        <i class="fa fa-cog"></i> إدارة المعايير
                                                                    </a>
                                                                    
                                                                    <button class="btn btn-dark btn-sm" onclick="highlightDepartment(this)" 
                                                                        data-id="{{ $employee->id }}"
                                                                        data-name="{{ $employee->name }}">
                                                                        <i class="fa fa-copy"></i> نسخ المعايير
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        @endif
                                                        <td class="text-nowrap">
                                                            @if($evaluation)
                                                                @if($status === 0)
                                                                    <a href="{{ route('department_manager.evaluation.edit', ['evaluation' => $evaluation->id]) }}" 
                                                                       class="btn btn-sm btn-warning mb-1">
                                                                        <i class="fa fa-edit"></i> تعديل
                                                                    </a>
                                                                @endif
                                                                <a href="{{ route('department_manager.evaluation.details', ['employee' => $employee->id, 'month' => $selectedMonth]) }}" 
                                                                   class="btn btn-sm btn-primary">
                                                                    <i class="fa fa-eye"></i> عرض
                                                                </a>
                                                            @elseif($isCurrentMonth)
                                                                <a href="{{ route('department_manager.evaluation.create', ['employee' => $employee->id, 'month' => $selectedMonth, 'page' => request()->get('page', 1)]) }}" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fa fa-plus"></i> إضافة تقييم
                                                                </a>
                                                            @else
                                                                <span class="text-muted">—</span>
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
                                    <div class="alert alert-warning text-center">لا يوجد موظفين للتقييم.</div>
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