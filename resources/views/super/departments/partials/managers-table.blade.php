@if($managers->count() > 0)
    <div class="table-responsive">
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
                        <td>
                            {{ $manager->assignedBy->name ?? 'غير معين' }}
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <button class="btn btn-primary btn-sm edit-manager" 
                                        data-id="{{ $manager->id }}"
                                        data-name="{{ $manager->name }}"
                                        data-email="{{ $manager->email }}"
                                        data-role="{{ $manager->role }}"
                                        data-department="{{ $manager->department_id }}"
                                        data-status="{{ $manager->status }}"
                                        data-assigned-by="{{ $manager->assigned_by }}">
                                    <span class="fa fa-exchange"></span> تعديل
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
    </div>
@else
    <p class="text-center py-4">لا يوجد مديرين أو موظفين</p>
@endif 