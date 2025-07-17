@extends('includes.master')
@section('title', 'المهام')

@section('content')
<section class="main profile">
  <div class="container">
    <div class="row">
      @include('includes.sidebar')
      <div class="col-lg-9 col-md-12">
        <div class="customer-content p-2 mb-5">
          <div class="d-flex justify-content-between align-items-center">
            <h3 class="fw-bold">المهام حسب الشهر</h3>
            <div id="lastRefreshTime" class="text-muted small"></div>
          </div>
          <div class="profile-content settings" id="departments-table-container">
            @if(isset($departments) && $departments->count() > 0)
              <div class="table-responsive">
                <table class="table table-striped table-bordered text-center">
                  <thead class="table-dark">
                    <tr>
                      <th>#</th>
                      <th>اسم القسم</th>
                      <th>حالة المهمة</th>
                      <th>المهام الجارية</th>
                      <th>العمليات</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($departments as $index => $department)
                      <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $department->name }}</td>
                        <td class="d-flex justify-content-center gap-3">
                          @if ($department->missions->count() == 0)
                            <span class="badge bg-secondary">لا توجد مهام حالياً</span>
                          @elseif ($department->missions->where('status', 0)->count() > 0)
                            <span class="badge bg-warning text-dark">مهام جارية</span>
                          @else
                            <span class="badge bg-success">كل المهام مكتملة</span>
                          @endif
                        </td>
                        <td>
                          <span class="badge bg-danger">
                            {{ $department->missions->where('status', 0)->count() }}
                          </span>
                        </td>
                        <td>
                          <a href="{{ route('department.missions', $department->id) }}" class="btn btn-dark btn-sm">
                            <span class="fa fa-pencil-alt"></span> طلب مهمة
                          </a>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateLastRefreshTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('ar-SA');
        document.getElementById('lastRefreshTime').textContent = `آخر تحديث: ${timeString}`;
    }

    function refreshDepartments() {
        fetch('{{ route("departments.data") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('departments-table-container').innerHTML = data.html;
                updateLastRefreshTime();
            })
            .catch(error => console.error('Error:', error));
    }

    // Initial refresh time
    updateLastRefreshTime();

    // Refresh every 10 seconds
    setInterval(refreshDepartments, 10000);
});
</script>
@endpush

@endsection
