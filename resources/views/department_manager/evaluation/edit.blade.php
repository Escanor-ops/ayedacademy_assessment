@extends('includes.master')

@section('title', 'تعديل تقييم')

@section('content')
<section class="main profile">
    <div class="container">
        <div class="row">
            @include('includes.sidebar')
            <div class="col-lg-9 col-md-12">
                <div class="customer-content p-2 mb-5">
                    <div class="d-flex gap-1 align-items-center">
                        <button id="sidebar-mobile-toggle" class="btn btn-default fs-18 d-none" onclick="_toggle_customer_sidebar()" style="padding:4px 11px;"><span class="fa fa-bars"></span></button>
                        <h3 class="fw-bold">تعديل تقييم - {{ $employee->name }}</h3>
                    </div>

                    <div class="profile-content settings">
                        <form method="POST" action="{{ route('department_manager.evaluation.update', $evaluation->id) }}">
                            @csrf
                            @method('PUT')

                            <!-- Display overall rating -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">التقييم العام</h5>
                                </div>
                                <div class="card-body">
                                    <h4 class="text-center">
                                        <span class="badge rounded-pill 
                                            @if($evaluation->overall_rating >= 85) bg-success
                                            @elseif($evaluation->overall_rating >= 70) bg-primary
                                            @elseif($evaluation->overall_rating >= 50) bg-warning text-dark
                                            @else bg-danger
                                            @endif">
                                            {{ $evaluation->overall_rating }} / 100
                                        </span>
                                    </h4>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">المعايير العامة</h5>
                                </div>
                                <div class="card-body">
                                    @foreach($globalStandards as $standard)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $standard->name }}</label>
                                            <input type="number" class="form-control" name="standards[{{ $standard->id }}]" 
                                                   min="0" max="100" required
                                                   value="{{ $evaluation->employeeEvaluationDetails->where('standard_id', $standard->id)->first()?->score }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if($employeeStandards->count() > 0)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">معايير خاصة بالموظف</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($employeeStandards as $standard)
                                            <div class="mb-3">
                                                <label class="form-label">{{ $standard->name }}</label>
                                                <input type="number" class="form-control" name="standards[{{ $standard->id }}]" 
                                                       min="0" max="100" required
                                                       value="{{ $evaluation->employeeEvaluationDetails->where('standard_id', $standard->id)->first()?->score }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">ملاحظات وتوصيات</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea class="form-control" name="notices" rows="3">{{ $evaluation->notices }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">توصيات</label>
                                        <textarea class="form-control" name="recommendations" rows="3">{{ $evaluation->recommendations }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <a href="{{ route('department_manager.evaluation.index', ['month' => $month]) }}" class="btn btn-secondary">إلغاء</a>
                                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 