@extends('includes.master')
@section('title', 'المعايير')

@section('content')
<div class="modal fade" id="addStandardModal" tabindex="-1" aria-labelledby="addStandardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="addStandardModalLabel">إضافة معيار جديد</h5>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('department_manager.standards.store') }}">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{$employee->id}}">

                    <div class="mb-3">
                        <label class="form-label">الاسم</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">إضافة المعيار</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editStandardModal" tabindex="-1" aria-labelledby="editStandardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStandardModalLabel">تعديل بيانات المعيار</h5>
            </div>
            <div class="modal-body">
                <form method="POST" id="updateStandardForm" action="{{ route('department_manager.standards.update') }}"> 
                    @csrf

                    <input type="hidden" id="standard_id" name="standard_id">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">الاسم</label>
                                <input type="text" id="standard_name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea id="standard_description" name="description" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                            <label class="form-label">الحالة</label>
                            <select id="standard_status" name="status" class="form-select">
                                <option value="1">مفعل</option>
                                <option value="0">غير مفعل</option>
                            </select>
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
                        <h3 class="fw-bold">إدارة المعايير</h3>
                        <button class="btn btn-dark rounded" data-bs-toggle="modal" data-bs-target="#addStandardModal">
                            <span class="fa fa-plus"></span> إضافة معيار جديد
                        </button>
                    </div>
                    <div class="profile-content settings">
                    <h5 class="mb-3">
                                        معايير التقيم الخاصة بالموظف
                                            <br>
                                        <small class="text-muted">
                                        {{$employee->name}}
                                        </small>
                                    </h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>الوصف</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($standards as $standard)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $standard->name }}</td>
                                            <td>{{ $standard->description }}</td>
                                            <td>
                                                @if ($standard->status)
                                                    <span class="badge bg-success">مفعل</span>
                                                @else
                                                    <span class="badge bg-danger">غير مفعل</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-primary btn-sm" onclick="highlightStandard(this)" 
                                                    data-id="{{ $standard->id }}" 
                                                    data-name="{{ $standard->name }}" 
                                                    data-status="{{ $standard->status }}" 
                                                    data-description="{{ $standard->description }}">
                                                    <span class="fa fa-edit"></span> تعديل
                                                </button>
                                                <form action="{{ route('department_manager.standards.destroy', $standard->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('post')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('يتم التعطيل اذا كان مرتبط بتقيم سابق والحذف اذا لم يكن!')">تعطيل او حذف</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($standards->isEmpty())
                            <div class="alert alert-warning text-center">⚠️ لا توجد معايير حتى الآن.</div>
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
    function highlightStandard(button) {
        let standardId = button.getAttribute('data-id');
        console.log(standardId)
        let standardName = button.getAttribute('data-name');
        let standardDescription = button.getAttribute('data-description');
        let standardStatus = button.getAttribute('data-status');

        // Fill modal inputs
        document.getElementById('standard_id').value = standardId;
        document.getElementById('standard_name').value = standardName;
        document.getElementById('standard_description').value = standardDescription;
        document.getElementById('standard_status').value = standardStatus;


        // Open the modal
        var modal = new bootstrap.Modal(document.getElementById('editStandardModal'));
        modal.show();
    }
</script>
@endsection
