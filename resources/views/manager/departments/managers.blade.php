@extends('includes.master')
@section('title', 'مديري الأقسام')

@section('content')
<div class="modal fade" id="addDepartmentManagerModal" tabindex="-1" aria-labelledby="addDepartmentManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="addDepartmentManagerModalLabel">إضافة مدير قسم جديد</h5>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('admin.departments-managers.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">الاسم</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div>
                            <label for="department_id">القسم</label>
                            <select class="form-control" name="department_id" id="department_id" required>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">إضافة المدير</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editDepartmentManagerModal" tabindex="-1" aria-labelledby="editDepartmentManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDepartmentManagerModalLabel">تعديل بيانات مدير القسم</h5>
            </div>
            <div class="modal-body">
                <form method="POST" id="updateDepartmentManagerForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="department_manager_id" name="department_manager_id">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">الاسم</label>
                                <input type="text" id="department_manager_name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" id="department_manager_email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" name="password" class="form-control" placeholder="كلمة المرور" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">الحالة</label>
                                <select id="department_manager_status" name="status" class="form-select">
                                    <option value="1">نشط</option>
                                    <option value="0">غير نشط</option>
                                </select>
                            </div>
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
                            <button id="sidebar-mobile-toggle" class="btn btn-default fs-18 d-none" onclick="_toggle_customer_sidebar()" style="padding:4px 11px;"><span class="fa fa-bars"></span></button>
                            <h3 class="fw-bold">مديري الأقسام</h3>
                        </div>
                        <div class="d-flex gap-3">
                            <button class="btn btn-dark rounded" style="padding:4px 10px;" data-bs-toggle="modal" data-bs-target="#addDepartmentManagerModal">
                                <span class="fa fa-plus"></span>
                            </button>
                        </div>
                    </div>
                    <div class="profile-content settings">
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="main-info" role="tabpanel">
                                @if(isset($departmentManagers) && $departmentManagers->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered text-center">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>الاسم</th>
                                                    <th>البريد الإلكتروني</th>
                                                    <th>القسم</th>
                                                    <th>الحالة</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($departmentManagers as $index => $manager)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $manager->name }}</td>
                                                        <td>{{ $manager->email }}</td>
                                                        <td>{{ $manager->department->name }}</td>
                                                        <td>
                                                            @if ($manager->status)
                                                                <span class="badge bg-success">نشط</span>
                                                            @else
                                                                <span class="badge bg-danger">غير نشط</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-primary btn-sm" onclick="editDepartmentManager(this)" 
                                                                data-id="{{ $manager->id }}" 
                                                                data-name="{{ $manager->name }}" 
                                                                data-email="{{ $manager->email }}"
                                                                data-status="{{ $manager->status }}">
                                                                <span class="fa fa-edit"></span> تعديل
                                                            </button>
                                                            <form action="{{ route('admin.departments-managers.destroy', $manager->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المدير؟')">حذف</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-warning text-center">⚠️ لا يوجد مديري أقسام حتى الآن.</div>
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
function editDepartmentManager(button) {
    let managerId = button.getAttribute('data-id');
    let managerName = button.getAttribute('data-name');
    let managerEmail = button.getAttribute('data-email');
    let managerStatus = button.getAttribute('data-status');

    document.getElementById('department_manager_id').value = managerId;
    document.getElementById('department_manager_name').value = managerName;
    document.getElementById('department_manager_email').value = managerEmail;
    document.getElementById('department_manager_status').value = managerStatus;

    document.getElementById('updateDepartmentManagerForm').action = "{{ url('admin/departments-managers') }}/" + managerId;

    let modal = new bootstrap.Modal(document.getElementById('editDepartmentManagerModal'));
    modal.show();
}

</script>
@endsection
