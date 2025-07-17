@extends('includes.master')
@section('title', 'الأقسام')

@section('content')
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="addDepartmentModalLabel">إضافة قسم جديد</h5>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('admin.departments.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">اسم القسم</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">إضافة القسم</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDepartmentModalLabel">تعديل بيانات القسم</h5>
            </div>
            <div class="modal-body">
                <form method="POST" id="updateDepartmentForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="department_id" name="department_id">
                    <div class="mb-3">
                        <label class="form-label">اسم القسم</label>
                        <input type="text" id="department_name" name="name" class="form-control" required>
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
                        <h3 class="fw-bold">الأقسام</h3>
                        <button class="btn btn-dark rounded" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                            <span class="fa fa-plus"></span>
                        </button>
                    </div>
                    <div class="profile-content settings">
                        @if(isset($departments) && $departments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>اسم القسم</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($departments as $index => $department)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $department->name }}</td>
                                            <td>
                                                <button class="btn btn-primary btn-sm" onclick="highlightDepartment(this)" 
                                                    data-id="{{ $department->id }}" 
                                                    data-name="{{ $department->name }}">
                                                    <span class="fa fa-edit"></span> تعديل
                                                </button>
                                                <form action="{{ route('admin.departments.destroy', $department->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا القسم؟')">حذف</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-warning text-center">⚠️ لا يوجد أقسام حتى الآن.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
<script>
function highlightDepartment(button) {
    let departmentId = button.getAttribute('data-id');
    let departmentName = button.getAttribute('data-name');
    
    document.getElementById('department_id').value = departmentId;
    document.getElementById('department_name').value = departmentName;
    document.getElementById('updateDepartmentForm').action = "{{ url('admin/departments') }}/" + departmentId;
    
    var modal = new bootstrap.Modal(document.getElementById('editDepartmentModal'));
    modal.show();
}
</script>
@endsection