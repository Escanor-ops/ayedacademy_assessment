@extends('includes.master')

@section('title', 'تفاصيل تقييم الموظف')

@section('content')
<section class="main profile">
    <div class="container">
        <div class="row">
            @include('includes.sidebar')

            <div class="col-lg-9 col-md-12">
                <div class="customer-content p-2 mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold">
                            تفاصيل تقييم - {{ $evaluation->employee->name }}
                            <br>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $evaluation->month)->translatedFormat('F Y') }}
                            </small>
                        </h3>

                        <a href="{{ route('manager.evaluation.view', ['department' => $evaluation->employee->department_id, 'month' => $evaluation->month]) }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-right"></i> الرجوع
                        </a>
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
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-start fw-bold">التقييم العام</td>
                                    <td>
                                        <span class="badge rounded-pill 
                                            @if($evaluation->overall_rating >= 85) bg-success
                                            @elseif($evaluation->overall_rating >= 70) bg-primary
                                            @elseif($evaluation->overall_rating >= 50) bg-warning text-dark
                                            @else bg-danger
                                            @endif">
                                            {{ $evaluation->overall_rating }} / 100
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($evaluation->notices || $evaluation->recommendations)
                        <div class="row mt-4">
                            @if($evaluation->notices)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title mb-0">الملاحظات</h5>
                                        </div>
                                        <div class="card-body">
                                            {{ $evaluation->notices }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($evaluation->recommendations)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title mb-0">التوصيات</h5>
                                        </div>
                                        <div class="card-body">
                                            {{ $evaluation->recommendations }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 