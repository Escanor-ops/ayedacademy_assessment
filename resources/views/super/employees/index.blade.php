@extends('includes.master')
@section('title', 'الموظفين')

@section('content')
<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="addEmployeeModalLabel">إضافة موظف جديد</h5>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('admin.employees.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">اسم الوظيفة</label>
                            <input type="text" name="position" class="form-control" required>
                        </div>
                        <div>
                            <label for="department_id">القسم</label>
                            <select class="form-control" name="department_id" id="department_id" required>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="assigned_by">تعيين بواسطة</label>
                            <select class="form-control" name="assigned_by" id="assigned_by">
                                <option value="">-- اختر المسؤول --</option>
                                @foreach ($assigners as $assigner)
                                    <option value="{{ $assigner->id }}">{{ $assigner->name }} ({{ $assigner->role }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">إضافة الموظف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">تعديل بيانات الموظف</h5>
            </div>
            <div class="modal-body">
                <form method="POST" id="updateEmployeeForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="employee_id" name="employee_id">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" id="employee_name" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" id="employee_email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" placeholder="كلمة المرور">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">اسم الوظيفة</label>
                            <input type="text" id="employee_position" name="position" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">الحالة</label>
                            <select id="employee_status" name="status" class="form-select">
                                <option value="1">نشط</option>
                                <option value="0">غير نشط</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="edit_assigned_by">تعيين بواسطة</label>
                            <select class="form-control" name="assigned_by" id="edit_assigned_by">
                                <option value="">-- اختر المسؤول --</option>
                                @foreach ($assigners as $assigner)
                                    <option value="{{ $assigner->id }}">{{ $assigner->name }} ({{ $assigner->role }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-success">حفظ التعديلات</button>
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
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-1">
                            <button id="sidebar-mobile-toggle" class="btn btn-default fs-18 d-none" onclick="_toggle_customer_sidebar()" style="padding:4px 11px;">
                                <span class="fa fa-bars"></span>
                            </button>
                            <h3 class="fw-bold">حسابات الموظفين</h3>
                        </div>
                        <div class="d-flex gap-3">
                            <button class="btn btn-dark rounded" style="padding:4px 10px;" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                <span class="fa fa-plus"></span>
                            </button>
                        </div>
                    </div>
                    <div class="profile-content settings">
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="main-info" role="tabpanel">
                                @if(isset($employees) && $employees->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered text-center">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>الاسم</th>
                                                    <th>البريد الإلكتروني</th>
                                                    <th>الوظيفة</th>
                                                    <th>القسم</th>
                                                    <th>الحالة</th>
                                                    <th>تعيين بواسطة</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($employees as $index => $employee)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $employee->name }}</td>
                                                        <td>{{ $employee->email }}</td>
                                                        <td>{{ $employee->position }}</td>
                                                        <td>{{ $employee->department->name }}</td>
                                                        
                                                        <td>
                                                            @if ($employee->status)
                                                                <span class="badge bg-success">نشط</span>
                                                            @else
                                                                <span class="badge bg-danger">غير نشط</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($employee->assignedBy)
                                                                {{ $employee->assignedBy->name }}
                                                            @else
                                                                <span class="text-muted">غير معين</span>
                                                            @endif
                                                        </td>
                                                        <td class="d-flex">
                                                            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 mx-1" onclick="highlightEmployee(this)"
                                                                data-id="{{ $employee->id }}"
                                                                data-name="{{ $employee->name }}"
                                                                data-email="{{ $employee->email }}"
                                                                data-status="{{ $employee->status }}"
                                                                data-position="{{ $employee->position }}"
                                                                data-assigned_by="{{ $employee->assigned_by }}">
                                                                <span class="fa fa-edit"></span> تعديل
                                                            </button>
                                                            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الموظف؟')">حذف</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        
                                    </div>
                                    <div class="pagination my-4">
                                        {{ $employees->links() }}
                                    </div>
                                @else
                                    <div class="alert alert-warning text-center">⚠️ لا يوجد موظفين حتى الآن.</div>
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
function highlightEmployee(button) {
    let employeeId = button.getAttribute('data-id');
    let employeeName = button.getAttribute('data-name');
    let employeeEmail = button.getAttribute('data-email');
    let employeeStatus = button.getAttribute('data-status');
    let employeePosition = button.getAttribute('data-position');
    let employeeAssignedBy = button.getAttribute('data-assigned_by');

    document.getElementById('employee_id').value = employeeId;
    document.getElementById('employee_name').value = employeeName;
    document.getElementById('employee_email').value = employeeEmail;
    document.getElementById('employee_status').value = employeeStatus;
    document.getElementById('employee_position').value = employeePosition;
    document.getElementById('edit_assigned_by').value = employeeAssignedBy;

    document.getElementById('updateEmployeeForm').action = "{{ url('admin/employees') }}/" + employeeId;

    var modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
    modal.show();
}
</script>
@endsection
