@extends('includes.master')
@section('title', 'مديري وموظفي الأقسام')

@section('content')
<!-- Add Modal -->
<div class="modal fade" id="addDepartmentManagerModal" tabindex="-1" aria-labelledby="addDepartmentManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDepartmentManagerModalLabel">إضافة مدير/موظف جديد</h5>

            </div>
            <form action="{{ route('admin.departments-managers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">الاسم</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">المنصب</label>
                        <input type="text" class="form-control" id="position" name="position" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">الدور</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="department_manager">مدير قسم</option>
                            <option value="employee">موظف</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">القسم</label>
                        <select class="form-select" id="department" name="department_id" required>
                            <option value="">اختر القسم</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assigned_by" class="form-label">تعيين بواسطة</label>
                        <select class="form-select" id="assigned_by" name="assigned_by">
                            <option value="">اختر المسؤول</option>
                            @foreach($assigners as $assigner)
                                <option value="{{ $assigner->id }}">{{ $assigner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">الحالة</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1">نشط</option>
                            <option value="0">غير نشط</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">تعديل بيانات الموظف</h5>
            </div>
            <form id="editForm" method="POST">
                @csrf
                <div class="modal-body" style="max-height:500px; overflow:auto;">
                    <input type="hidden" id="user_id" name="manager_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">الاسم</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_password" class="form-label">كلمة المرور</label>
                        <input type="password" class="form-control" id="edit_password" name="password" placeholder="اتركه فارغاً إذا لم ترد تغيير كلمة المرور">
                    </div>

                    <div class="mb-3">
                        <label for="edit_position" class="form-label">المنصب</label>
                        <input type="text" class="form-control" id="edit_position" name="position" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_role" class="form-label">الدور</label>
                        <select class="form-control" id="edit_role" name="new_role" required>
                            <option value="department_manager">مدير قسم</option>
                            <option value="employee">موظف</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_department" class="form-label">القسم</label>
                        <select class="form-control" id="edit_department" name="new_department_id" required>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_assigned_by" class="form-label">تعيين بواسطة</label>
                        <select class="form-control" id="edit_assigned_by" name="assigned_by">
                            <option value="">اختر المسؤول</option>
                            @foreach($assigners as $assigner)
                                <option value="{{ $assigner->id }}">{{ $assigner->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_status" class="form-label">الحالة</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="1">نشط</option>
                            <option value="0">غير نشط</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-1">
                            <h3 class="fw-bold mb-0">مديري وموظفي الأقسام</h3>
                        </div>
                        <div class="d-flex gap-3">
                            <button class="btn btn-dark rounded" style="padding:4px 10px;" data-bs-toggle="modal" data-bs-target="#addDepartmentManagerModal">
                                <span class="fa fa-plus"></span>
                            </button>
                        </div>
                    </div>

                    <div class="profile-content settings">
                        <!-- Department Tabs -->
                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $selectedDepartment === 'all' ? 'active' : '' }}" 
                                   href="{{ route('admin.departments-managers.index', ['department' => 'all']) }}">
                                    جميع الأقسام
                                    <span class="badge rounded-pill bg-secondary">{{ $managers->total() }}</span>
                                </a>
                            </li>
                            @foreach($departments as $department)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $selectedDepartment == $department->id ? 'active' : '' }}" 
                                       href="{{ route('admin.departments-managers.index', ['department' => $department->id]) }}">
                                        {{ $department->name }}
                                        <span class="badge rounded-pill bg-secondary">
                                            {{ $departmentCounts[$department->id] ?? 0 }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <!-- Users Table -->
                        <div class="table-responsive">
                            @if($managers->count() > 0)
                            <table class="table table-striped table-bordered text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>القسم</th>
                                        <th>الدور</th>
                                        <th>الحالة</th>
                                        <th>تعيين بواسطة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($managers as $manager)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $manager->name }}</td>
                                        <td>{{ $manager->email }}</td>
                                        <td>{{ $manager->department->name ?? 'غير محدد' }}</td>
                                        <td>
                                            @if($manager->role === 'department_manager')
                                                <span class="badge bg-primary">مدير قسم</span>
                                            @else
                                                <span class="badge bg-info">موظف</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($manager->status)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>{{ $manager->assignedBy->name ?? 'غير معين' }}</td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <button class="btn btn-primary btn-sm" 
                                                        onclick="highlight({{ $manager->id }}, '{{ $manager->name }}', '{{ $manager->email }}', '{{ $manager->role }}', '{{ $manager->department_id }}', '{{ $manager->status }}', '{{ $manager->assigned_by }}', '{{ $manager->position }}')">
                                                    <span class="fa fa-edit"></span> 
                                                </button>
                                                <form action="{{ route('admin.departments-managers.destroy', $manager->id) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المدير؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <span class="fa fa-trash"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center mt-4">
                                {{ $managers->links() }}
                            </div>
                            @else
                            <p class="text-center py-4">لا يوجد مديرين أو موظفين</p>
                            @endif
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
function highlight(id, name, email, role, department, status, assigned_by, position) {
    // Set values
    document.getElementById('user_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_department').value = department;
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_assigned_by').value = assigned_by || '';
    document.getElementById('edit_position').value = position;

    // Set form action
    document.getElementById('editForm').action = `/admin/departments-managers/${id}/transfer`;

    // Highlight fields
    ['edit_name', 'edit_email', 'edit_role', 'edit_department', 'edit_status', 'edit_assigned_by', 'edit_position'].forEach(id => {
        const field = document.getElementById(id);
        field.style.backgroundColor = '#fff3cd';
        setTimeout(() => field.style.backgroundColor = '', 2000);
    });

    // Open modal programmatically
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>
@endsection
