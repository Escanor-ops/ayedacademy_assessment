@extends('includes.master')

@section('title', 'تقارير المهمة')

@section('content')
<style>
.file-card {
    transition: transform 0.2s;
}
.file-card:hover {
    transform: translateY(-5px);
}
.file-icon {
    font-size: 2rem;
    margin-bottom: 10px;
}
.file-type-pdf { color: #dc3545; }
.file-type-image { color: #198754; }
.file-type-doc { color: #0d6efd; }
.file-type-zip { color: #fd7e14; }
.file-type-audio { color: #6f42c1; }
.file-type-video { color: #d63384; }
.file-type-other { color: #6c757d; }

/* Timeline Styles */
.timeline {
    position: relative;
    padding: 20px 0;
}
.timeline::before {
    content: '';
    position: absolute;
    width: 3px;
    background: #e9ecef;
    top: 0;
    bottom: 0;
    right: 30px;
}
.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-right: 60px;
}
.timeline-item::before {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    border: 3px solid #0d6efd;
    background: white;
    border-radius: 50%;
    right: 23px;
    top: 15px;
}
.timeline-item.status-0::before { border-color: #ffc107; } /* pending */
.timeline-item.status-1::before { border-color: #0dcaf0; } /* in progress */
.timeline-item.status-2::before { border-color: #dc3545; } /* rejected */
.timeline-item.status-3::before { border-color: #198754; } /* completed */
</style>

<section class="main profile">
  <div class="container">
    <div class="row">
      @include('includes.sidebar')

      <div class="col-lg-9 col-md-12">
        <div class="d-flex justify-content-between align-items-center my-3">
          <h4 class="mb-0 fw-bold">تقارير المهمة</h4>
          <a href="{{ url()->previous() }}" class="btn btn-dark">
            رجوع للمهام
            <i class="fas fa-arrow-right ml-2"></i>
          </a>
        </div>

        <div class="customer-content p-2 mb-5">
          <!-- Mission Header -->
          <div class="card mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold mb-0">{{ $mission->missionType->name }}</h3>
                <div class="d-flex justify-content-between gap-2">
                  @php
                    $hasNoDepartment = auth()->user()->department_id === null;
                    $isMyDepartment = auth()->user()->department_id == $mission->department_id;
                    $isDepartmentManager = auth()->user()->role == 'department_manager' && 
                                        auth()->user()->department_id == $mission->user->department_id;
                    $isManager = auth()->user()->role == 'manager';
                    $isMissionCreator = auth()->user()->id == $mission->user_id;
                    $canAddReport = 
                      $hasNoDepartment || // Can add if super admin/general manager
                      $isMyDepartment || // Can add if it's my department
                      $isMissionCreator || // Can add if I'm the creator
                      $isDepartmentManager || // Can add if I'm the manager of this department
                      $isManager; // Can add if I'm a manager
                  @endphp
                  @if($canAddReport)
                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                      📎 إرفاق ملفات
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReportModal">
                      ✏️ إضافة تقرير
                    </button>
                  @endif
                </div>
              </div>
              <p class="mb-3">{{ $mission->description }}</p>
              <div class="d-flex gap-3">
                @php
                  $statusClasses = [
                    0 => 'bg-warning text-dark',
                    1 => 'bg-info text-white',
                    2 => 'bg-danger text-white',
                    3 => 'bg-success text-white'
                  ];
                  $statusLabels = [
                    0 => 'في الانتظار',
                    1 => 'جاري المعالجة',
                    2 => 'مرفوضة',
                    3 => 'مكتملة'
                  ];
                @endphp
                <span class="badge {{ $statusClasses[$mission->status] ?? 'bg-secondary' }}">
                  {{ $statusLabels[$mission->status] ?? 'غير معروف' }}
                </span>
                <span class="badge bg-secondary">{{ $mission->ticket_number }}</span>
                @if($mission->deadline)
                  <span class="badge bg-primary">
                    <i class="fas fa-calendar-alt me-1"></i>
                    {{ $mission->deadline }}
                  </span>
                @endif
              </div>
            </div>
          </div>

          <!-- Files Section -->
          <div class="accordion mb-5" id="filesAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#filesCollapse">
                  <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-paperclip"></i>
                    <span class="fw-bold">الملفات المرفقة</span>
                    <span class="badge bg-secondary">{{ $files->count() }}</span>
                  </div>
                </button>
              </h2>
              <div id="filesCollapse" class="accordion-collapse collapse show" data-bs-parent="#filesAccordion">
                <div class="accordion-body">
                  @if($files->count() > 0)
                    <div class="row g-3">
                      @foreach($files as $file)
                        <div class="col-md-4">
                          <div class="card h-100 file-card">
                            <div class="card-body text-center">
                              @php
                                $fileType = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                $iconClass = match($fileType) {
                                  'pdf' => 'fa-file-pdf file-type-pdf',
                                  'jpg', 'jpeg', 'png', 'gif', 'webp' => 'fa-file-image file-type-image',
                                  'doc', 'docx' => 'fa-file-word file-type-doc',
                                  'zip', 'rar' => 'fa-file-archive file-type-zip',
                                  'mp3', 'wav', 'm4a' => 'fa-file-audio file-type-audio',
                                  'mp4', 'mov', 'avi' => 'fa-file-video file-type-video',
                                  default => 'fa-file file-type-other'
                                };
                              @endphp
                              
                              <i class="fas {{ $iconClass }} file-icon"></i>
                              <h6 class="mb-2 text-truncate" title="{{ $file->file_name }}">
                                {{ $file->file_name }}
                              </h6>
                              
                              <div class="small text-muted mb-2">
                                <div>الحجم: {{ $file->getHumanReadableSize() }}</div>
                                <div>النوع: {{ strtoupper($fileType) }}</div>
                                <div>تاريخ الرفع: {{ $file->created_at->format('Y-m-d H:i') }}</div>
                                <div>بواسطة: {{ $file->user->name }}</div>
                              </div>
                              
                              <a href="{{ route('missions.file.download', ['file' => $file->id]) }}" 
                                 class="btn btn-sm btn-primary w-100">
                                <i class="fas fa-download me-1"></i> تحميل
                              </a>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @else
                    <div class="alert alert-info text-center">
                      <i class="fas fa-info-circle me-2"></i>
                      لا توجد ملفات مرفقة لهذه المهمة حتى الآن.
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <!-- Reports Section -->
          <div class="mb-4">
            <h5 class="fw-bold mb-3">
              <i class="fas fa-history me-2"></i>
              سجل المهمة والتقارير
              <span class="badge bg-secondary">{{ $reports->count() }}</span>
            </h5>

            @if($reports->count() > 0)
              <div class="timeline">
                @foreach($reports as $report)
                  <div class="timeline-item status-{{ $report->type == 'status' ? $report->to_status : '1' }}">
                    <div class="card">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <div>
                            <span class="fw-bold">{{ $report->user->name }}</span>
                            <span class="text-muted mx-2">•</span>
                            <small class="text-muted">{{ $report->created_at->format('Y-m-d H:i') }}</small>
                          </div>
                          @if($report->type == 'status')
                            <span class="badge {{ $statusClasses[$report->to_status] ?? 'bg-secondary' }}">
                              تغيير الحالة
                            </span>
                          @endif
                        </div>
                        <p class="mb-0">{{ $report->content }}</p>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                لا توجد تقارير لهذه المهمة حتى الآن.
              </div>
            @endif
          </div>

        </div>
      </div> <!-- col -->
    </div> <!-- row -->
  </div> <!-- container -->
</section>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">📂 رفع ملفات المهمة</h5>
      </div>
      <div class="modal-body">
        <form action="{{ route('missions.file', $mission->id) }}" class="dropzone" id="missionDropzone">
          @csrf
        </form>
        <small class="text-muted d-block mt-2">يمكنك رفع حتى 8 ملفات (صور، مستندات، فيديو، صوت، ZIP) بحد أقصى ١٠ ميجا لكل ملف.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Report Modal -->
<div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✏️ إضافة تقرير جديد</h5>
      </div>
      <form action="{{ route('missions.reports.store', $mission->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="report_content" class="form-label">محتوى التقرير</label>
            <textarea class="form-control" id="report_content" name="content" rows="4" required></textarea>
          </div>
          @if(auth()->user()->department_id == $mission->department_id)
            <div class="mb-3">
              <label class="form-label d-block">تحديث حالة المهمة</label>
              <div class="btn-group w-100" role="group">
                @foreach($statusLabels as $value => $label)
                  <input type="radio" class="btn-check" name="status" id="status{{ $value }}" value="{{ $value }}" 
                         {{ $mission->status == $value ? 'checked' : '' }}>
                  <label class="btn btn-outline-primary" for="status{{ $value }}">{{ $label }}</label>
                @endforeach
              </div>
            </div>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn btn-success">حفظ التقرير</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Dropzone CSS/JS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<script>
Dropzone.options.missionDropzone = {
  paramName: 'file',
  maxFilesize: 100,
  maxFiles: 10,
  addRemoveLinks: true,
  acceptedFiles: '.jpg,.jpeg,.png,.pdf,.zip,.webp,.doc,.docx,.mp4,.mov,.avi,.mp3,.wav,.m4a',
  dictDefaultMessage: 'اسحب الملفات هنا أو اضغط لاختيار الملفات (صور، مستندات، فيديو، صوت، ZIP — حد أقصى ٤ ملفات)',
  timeout: 300000, // 5 minutes
  chunking: false,
  createImageThumbnails: false, // Disable thumbnails for better performance
  
  init: function () {
    // Create a reference to the Dropzone instance
    var myDropzone = this;
    
    this.on("maxfilesexceeded", function(file) {
      this.removeFile(file);
      alert('يمكنك رفع ٤ ملفات فقط');
    });

    this.on("sending", function(file, xhr, formData) {
      console.log('Starting upload for file:', file.name, 'Size:', file.size);
      
      // Add CSRF token
      formData.append("_token", "{{ csrf_token() }}");
      
      // Set headers
      xhr.setRequestHeader('X-File-Name', file.name);
      xhr.setRequestHeader('X-File-Size', file.size);
      
      // Log the request headers
      xhr.onreadystatechange = function() {
        if(xhr.readyState === 1) {
          console.log('Request prepared, about to send');
        }
      };
    });
    
    this.on("error", function(file, errorMessage, xhr) {
      console.error('Upload Error Details:', {
        file: file.name,
        size: file.size,
        error: errorMessage,
        response: xhr ? xhr.responseText : 'No response'
      });

      // Try to parse error message if it's an object
      let errorText = errorMessage;
      if (typeof errorMessage === 'object') {
        try {
          errorText = JSON.stringify(errorMessage);
        } catch (e) {
          errorText = 'خطأ في رفع الملف';
        }
      }

      // Handle specific error cases
      if (errorText.includes('File is too big')) {
        alert('حجم الملف كبير جداً. الحد الأقصى هو 100 ميجابايت');
      } else if (xhr && xhr.status === 413) {
        alert('حجم الملف كبير جداً للخادم. يرجى تقليل حجم الملف.');
      } else if (xhr && xhr.status === 422) {
        try {
          const response = JSON.parse(xhr.responseText);
          alert(response.message || 'خطأ في التحقق من صحة الملف');
        } catch (e) {
          alert('خطأ في التحقق من صحة الملف');
        }
      } else {
        alert('حدث خطأ أثناء رفع الملف: ' + errorText);
      }
      
      this.removeFile(file);
    });

    this.on("success", function(file, response) {
      console.log('Upload Success Response:', response);
      
      try {
        if (typeof response === 'string') {
          response = JSON.parse(response);
        }
        
        if (response && response.success) {
          console.log('File uploaded successfully:', response);
          window.location.reload();
        } else {
          console.error('Upload failed:', response);
          const errorMessage = response.message || 'خطأ غير معروف';
          alert('فشل رفع الملف: ' + errorMessage);
          this.removeFile(file);
        }
      } catch (error) {
        console.error('Error processing response:', error);
        if (response && response.success === false) {
          alert('فشل رفع الملف: ' + (response.message || 'خطأ غير معروف'));
        } else {
          window.location.reload();
        }
      }
    });
  }
};
</script>

@endsection
