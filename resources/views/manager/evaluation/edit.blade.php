@extends('includes.master')

@section('title', 'تعديل التقييم')

@section('content')
<section class="main profile">
    <div class="container">
        <div class="row">
            @include('includes.sidebar')
            <div class="col-lg-9 col-md-12">
                <div class="customer-content p-4 mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fw-bold">تعديل التقييم - {{ $employee->name }}</h3>
                        <a href="{{ route('manager.evaluation.index', ['month' => $month]) }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-right"></i> العودة
                        </a>
                    </div>

                    <form method="POST" action="{{ route('manager.evaluation.update', $evaluation->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <input type="hidden" name="month" value="{{ $month }}">

                        <!-- Display overall rating -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">التقييم العام: {{ $evaluation->overall_rating }}</h5>
                        </div>

                        <!-- Global Standards Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">المعايير العامة</h5>
                            @foreach ($globalStandards as $standard)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ $standard->name }}</label>
                                    <input type="number" name="standards[{{ $standard->id }}]" class="form-control" 
                                        min="0" max="100" 
                                        value="{{ old('standards.' . $standard->id, $evaluation->employeeEvaluationDetails->where('standard_id', $standard->id)->first()->score ?? '') }}" 
                                        required>
                                </div>
                            @endforeach
                            @if ($globalStandards->isEmpty())
                                <p class="text-muted">لا توجد معايير عامة.</p>
                            @endif
                        </div>

                        <!-- Employee-Specific Standards Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">معايير الموظف</h5>
                            @foreach ($employeeStandards as $standard)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ $standard->name }}</label>
                                    <input type="number" name="standards[{{ $standard->id }}]" class="form-control" 
                                        min="0" max="100" 
                                        value="{{ old('standards.' . $standard->id, $evaluation->employeeEvaluationDetails->where('standard_id', $standard->id)->first()->score ?? '') }}" 
                                        required>
                                </div>
                            @endforeach
                            @if ($employeeStandards->isEmpty())
                                <p class="text-muted">لا توجد معايير خاصة بهذا الموظف.</p>
                            @endif
                        </div>

                        <!-- Notices and Recommendations Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">ملاحظات وتوصيات</h5>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">الملاحظات</label>
                                <textarea name="notices" class="form-control" rows="3" placeholder="أدخل الملاحظات هنا (اختياري)">{{ old('notices', $evaluation->notices) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">التوصيات</label>
                                <textarea name="recommendations" class="form-control" rows="3" placeholder="أدخل التوصيات هنا (اختياري)">{{ old('recommendations', $evaluation->recommendations) }}</textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i> حفظ التقييم
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>    
</section>
@endsection
