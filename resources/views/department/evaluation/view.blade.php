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
                        <h3 class="fw-bold">تفاصيل التقييم</h3>
                    </div>

                    <div class="profile-content settings">
                        @if($evaluations->count())
                            <div class="table-responsive mt-4">
                                <table class="table table-bordered text-center align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>الشهر</th>
                                            <th>التقييم العام</th>
                                            <th>تفاصيل التقييم</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($evaluations as $evaluation)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $evaluation->month)->translatedFormat('F Y') }}</td>
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
                                                <td>
                                                    <a href="{{ route('department_manager.evaluation.details', ['employee' => $evaluation->employee_id, 'month' => $evaluation->month]) }}" class="btn btn-sm btn-dark">
                                                        عرض <i class="fa fa-arrow-left"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning text-center mt-4">
                                ⚠️ لا توجد تقييمات متاحة.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
