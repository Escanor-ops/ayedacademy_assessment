@extends('includes.master')
@section('title', 'المديرين')

@section('content')
<div class="modal fade" id="addManagerModal" tabindex="-1" aria-labelledby="addManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="addManagerModalLabel">إضافة مدير جديد</h5>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('admin.managers.store') }}">
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

<div class="modal fade" id="editManagerModal" tabindex="-1" aria-labelledby="editManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editManagerModalLabel">تعديل بيانات المدير</h5>
            </div>
            <div class="modal-body">
                <form method="POST" id="updateManagerForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="manager_id" name="manager_id">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">الاسم</label>
                                <input type="text" id="manager_name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" id="manager_email" name="email" class="form-control" required>
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
                                <select id="manager_status" name="status" class="form-select">
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
                                <h3 class="fw-bold">حسابات الادارة</h3>
                            </div>
                            <div class="d-flex gap-3">
                                <button class="btn btn-dark rounded" style="padding:4px 10px;" data-bs-toggle="modal" data-bs-target="#addManagerModal">
                                    <span class="fa fa-plus"></span>
                                </button>

                                <!-- <button class="btn btn-dark rounded-circle fs-14" onclick="_toggle_customer_sidebar()" style="padding:4px 11px;"><span class="fa fa-bars"></span></button> -->
                            </div>
                        </div>
                        <div class="profile-content settings">

                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="main-info" role="tabpanel" aria-labelledby="main info tab">
                                @if(isset($managers) && $managers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered text-center">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>الاسم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>الحالة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($managers as $index => $manager)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $manager->name }}</td>
                                                    <td>{{ $manager->email }}</td>
                                                    <td>
                                                        @if ($manager->status)
                                                            <span class="badge bg-success">نشط</span>
                                                        @else
                                                            <span class="badge bg-danger">غير نشط</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-primary btn-sm" onclick="highlightManager(this)" 
                                                            data-id="{{ $manager->id }}" 
                                                            data-name="{{ $manager->name }}" 
                                                            data-email="{{ $manager->email }}"
                                                            data-status="{{ $manager->status }}">
                                                            <span class="fa fa-edit"></span> تعديل
                                                        </button>
                                                        <form action="{{ route('admin.managers.destroy', $manager->id) }}" method="POST" class="d-inline">
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
                                <div class="alert alert-warning text-center">⚠️ لا يوجد مديرين حتى الآن.</div>
                            @endif
                                </div>
                                <div class="tab-pane" id="login-info" role="tabpanel" aria-labelledby="main info tab">
                                    <div class="card rounded-4 border-0 mb-3">
                                        <div class="card-body p-4">
                                            <form method="POST" action="" class="row">
                                                @csrf
                                                <div class="col-md-6">
                                                    <label class="fs-12 fw-bold" for="">كلمة المرور الجديدة *</label>
                                                    <input type="password" name="password" class="form-control mt-3 rounded-4 bg-ddd" placeholder="كلمة المرور الجديدة" required autocomplete>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="fs-12 fw-bold" for="">تأكيد كلمة المرور الجديدة *</label>
                                                    <input type="password" name="password_confirmation" class="form-control mt-3 rounded-4 bg-ddd" placeholder="تأكيد كلمة المرور الجديدة" required autocomplete>
                                                </div>
                                                <div class="col-12 mt-1">
                                                    <div class="alert alert-warning rounded-3 p-3 fs-14">يجب أن يتجاوز عدد أحرف كلمة المرور ٧ أحرف</div>
                                                </div>
                                                <div class="col-md-12 mt-2 text-start">
                                                    <button type="submit" name="" class="btn btn-primary rounded-4 w-100 p-3 fs-12 fw-bold shadow-sm">حفظ البيانات</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
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
function highlightManager(button) {
    let managerId = button.getAttribute('data-id');
    let managerName = button.getAttribute('data-name');
    let managerEmail = button.getAttribute('data-email');
    let managerStatus = button.getAttribute('data-status');

    // Fill modal inputs
    document.getElementById('manager_id').value = managerId;
    document.getElementById('manager_name').value = managerName;
    document.getElementById('manager_email').value = managerEmail;
    document.getElementById('manager_status').value = managerStatus;

    // Update form action dynamically
    document.getElementById('updateManagerForm').action = "{{ url('admin/managers') }}/" + managerId;

    // Open the modal
    var modal = new bootstrap.Modal(document.getElementById('editManagerModal'));
    modal.show();
}
</script>


@endsection