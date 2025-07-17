@extends('includes.master')

@section('title', 'تفاصيل التقييم')

@section('content')
<section class="main profile">
    <div class="container">
        <div class="row">
            @include('includes.sidebar')

            <div class="col-lg-9 col-md-12">
                <div class="customer-content p-2 mb-5">
                   <div class="d-flex justify-content-between align-items-center">
                        <h3 class="fw-bold">
                            تفاصيل التقييم - {{ $evaluation->employee->name }}
                        </h3>

                        <a href="{{ route('department_manager.evaluation.index', ['month' => $evaluation->month]) }}" class="btn btn-secondary mt-4">
                            <i class="fa fa-arrow-right"></i> الرجوع
                        </a>
                    </div>

                    <div class="profile-content settings">

                        <div class="alert alert-info my-3">
                            تقييم شهر:
                            <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $evaluation->month)->translatedFormat('F Y') }}</strong>
                        </div>
                        <div class="my-4">
                            <strong>التقييم العام:</strong>
                            <span class="badge rounded-pill 
                                @if($evaluation->overall_rating >= 85) bg-success
                                @elseif($evaluation->overall_rating >= 70) bg-primary
                                @elseif($evaluation->overall_rating >= 50) bg-warning text-dark
                                @else bg-danger
                                @endif">
                                {{ $evaluation->overall_rating }} / 100
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>المعيار</th>
                                        <th>الوصف</th>
                                        <th>الدرجة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($evaluation->employeeEvaluationDetails as $detail)
                                        <tr>
                                            <td>{{ $detail->standard->name ?? '—' }}</td>
                                            <td>{{ $detail->standard->description ?? '—' }}</td>
                                            <td>
                                                @if($detail->score !== null)
                                                    <span class="badge rounded-pill 
                                                        @if($detail->score >= 85) bg-success
                                                        @elseif($detail->score >= 70) bg-primary
                                                        @elseif($detail->score >= 50) bg-warning text-dark
                                                        @else bg-danger
                                                        @endif">
                                                        {{ $detail->score }} / 100
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Notices and Recommendations Section -->
                        <div class="mt-4">
                            <h5 class="border-bottom pb-2">ملاحظات وتوصيات</h5>
                            
                            <div class="mb-3">
                                <label class="fw-semibold">الملاحظات:</label>
                                <p class="p-2 bg-light rounded">
                                    {{ $evaluation->notices ?: 'لا توجد ملاحظات' }}
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="fw-semibold">التوصيات:</label>
                                <p class="p-2 bg-light rounded">
                                    {{ $evaluation->recommendations ?: 'لا توجد توصيات' }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
