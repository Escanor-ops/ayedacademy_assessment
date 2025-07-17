@extends('includes.master')

@section('title', 'تقييمات كل قسم حسب الشهر')

@section('content')
<section class="main profile">
    <div class="container">
        <div class="row">
            @include('includes.sidebar')
            <div class="col-lg-9 col-md-12">
                <div class="customer-content p-2 mb-5">
                    <div class="d-flex gap-2 align-items-center">
                        <button id="sidebar-mobile-toggle" class="btn btn-default fs-18 d-none" onclick="_toggle_customer_sidebar()" style="padding:9px 11px;"><span class="fa fa-bars"></span></button>

                        <h3 class="fw-bold">تقييمات كل قسم حسب الشهر</h3>
                    </div>

                    <div class="profile-content settings">
                        <ul class="nav nav-pills mb-3 px-0 py-3 bg-light" id="pills-tab" role="tablist">
                            @foreach ($months as $month)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $month == $selectedMonth ? 'active' : '' }}" 
                                    href="{{ route('manager.departments.index', ['month' => $month]) }}">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if($departments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>اسم القسم</th>
                                        <th>عدد الموظفين</th>
                                        <th>حالة التقييم</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($departments as $index => $department)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $department->name }}</td>
                                            <td>
                                                تم تقييم {{ $department->evaluated_count }} من {{ $department->total_employees }} موظف
                                            </td>
                                            <td>
                                                @if($department->total_employees === 0)
                                                    <span class="badge bg-secondary">لا يوجد موظفين</span>
                                                @elseif($department->evaluation_status === null)
                                                    <span class="badge bg-secondary">لم يبدأ</span>
                                                @elseif(!$isCurrentMonth && $department->evaluated_count > 0)
                                                    <span class="badge bg-success">تم التأكيد</span>
                                                @elseif($department->evaluation_status === 2)
                                                    <span class="badge bg-success">تم التأكيد</span>
                                                @elseif($department->evaluation_status === 1)
                                                    <span class="badge bg-info">في انتظار تأكيد المدير التنفيذي</span>
                                                @elseif($department->evaluation_status === 0)
                                                    <span class="badge bg-warning text-dark">في انتظار تأكيد المدير المباشر</span>
                                                @elseif($department->evaluation_status === 'mixed')
                                                    <span class="badge bg-warning text-dark">في انتظار اكتمال التقييم</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">في انتظار اكتمال التقييم</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($department->total_employees > 0)
                                                    @if($isCurrentMonth && $department->evaluation_status === 1)
                                                        <form method="POST" action="{{ route('manager.evaluation.confirm', $department->id) }}" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="fa fa-check"></i> تأكيد التقييم
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    <a href="{{ route('manager.evaluation.view', ['department' => $department->id, 'month' => $selectedMonth]) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fa fa-eye"></i> عرض التقييمات
                                                    </a>
                                                @endif
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

@endsectionn