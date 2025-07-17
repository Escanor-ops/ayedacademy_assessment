@extends('includes.master')

@section('title', 'تفاصيل تقييمات القسم')

@section('content')
<section class="main profile">
    <div class="container">
        <div class="row">
            @include('includes.sidebar')

            <div class="col-lg-9 col-md-12">
                <div class="customer-content p-2 mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold">
                            تقييمات قسم {{ $department->name }}
                            <br>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                            </small>
                        </h3>
                        <a href="{{ route('manager.departments.index', ['month' => $month]) }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-right"></i> الرجوع
                        </a>
                    </div>

                    @php
                        $totalEmployees = $users->count();
                        $evaluatedEmployees = $users->filter(fn($user) => $user->evaluation)->count();
                        $allEvaluated = $totalEmployees > 0 && $evaluatedEmployees === $totalEmployees;
                        $allStatus0 = $users->every(fn($user) => $user->evaluation && $user->evaluation->status === 0);
                        $isCurrentMonth = $month === now()->format('Y-m');
                    @endphp

                    <div class="alert alert-info">
                        تم تقييم {{ $evaluatedEmployees }} من {{ $totalEmployees }} موظف
                    </div>

                    @if($allEvaluated && $allStatus0 && $isCurrentMonth)
                        <form method="POST" action="{{ route('manager.evaluation.changeStatus') }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i> تأكيد التقييم
                            </button>
                        </form>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>المنصب</th>
                                    <th>القسم</th>
                                    <th>يتم تقييمه بواسطة</th>
                                    <th>التقييم العام</th>
                                    <th>حالة التقييم</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->position }}</td>
                                        <td>{{ $user->department->name }}</td>
                                        <td>
                                            @if($user->assigned_by)
                                                {{ $user->assignedBy->name }}
                                            @else
                                                {{ $user->department->users->where('role', 'department_manager')->first()?->name ?? 'مدير القسم' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->evaluation)
                                                <span class="badge rounded-pill 
                                                    @if($user->evaluation->overall_rating >= 85) bg-success
                                                    @elseif($user->evaluation->overall_rating >= 70) bg-primary
                                                    @elseif($user->evaluation->overall_rating >= 50) bg-warning text-dark
                                                    @else bg-danger
                                                    @endif">
                                                    {{ $user->evaluation->overall_rating }} / 100
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$user->evaluation)
                                                <span class="badge bg-secondary">لم يبدأ</span>
                                            @elseif($user->evaluation->status === 0)
                                                <span class="badge bg-warning text-dark">في انتظار تأكيد المدير المباشر</span>
                                            @elseif($user->evaluation->status === 1)
                                                <span class="badge bg-info">في انتظار تأكيد المدير التنفيذي</span>
                                            @elseif($user->evaluation->status === 2)
                                                <span class="badge bg-success">تم التأكيد</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->evaluation)
                                                <a href="{{ route('manager.employee.evaluation', ['employee' => $user->id, 'month' => $month]) }}" 
                                                   class="btn btn-sm btn-primary d-flex gap-1 align-items-center">
                                                    <i class="fa fa-eye"></i> عرض
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
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
