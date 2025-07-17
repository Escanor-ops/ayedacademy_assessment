@extends('includes.master')
@section('title', 'الملف الشخصي')

@section('content')
    <section class="main profile">
        <div class="container">
            <div class="row">
                @include('includes.sidebar')
                <div class="col-lg-9 col-md-12">
                    <div class="customer-content p-2 mb-5">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-1">
                                <button id="sidebar-mobile-toggle" class="btn btn-default fs-18 d-none" onclick="_toggle_customer_sidebar()" style="padding:4px 11px;"><span class="fa fa-bars"></span></button>
                                <h3 class="fw-bold">ملفي الشخصي</h3>
                            </div>
                        </div>
                        <div class="profile-content settings">
                            <ul class="nav nav-pills gap-3 mt-3 p-0 mx-0" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link m-0 rounded-3 active" id="" data-bs-toggle="pill" data-bs-target="#login-info" type="button" role="tab" aria-controls="Update login data" aria-selected="false">تغيير كلمة المرور</button>
                                </li>
                            </ul>

                            <div class="tab-content mt-4" id="pills-tabContent">
                                <div class="tab-pane active" id="login-info" role="tabpanel" aria-labelledby="main info tab">
                                    <div class="card rounded-4 border-0 mb-3">
                                        <div class="card-body p-4">
                                            @if(session('success'))
                                                <div class="alert alert-success">
                                                    {{ session('success') }}
                                                </div>
                                            @endif

                                            @if($errors->any())
                                                <div class="alert alert-danger">
                                                    <ul class="mb-0">
                                                        @foreach($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <form method="POST" action="{{ route('profile.update-password') }}" class="row">
                                                @csrf
                                                <div class="col-md-6">
                                                    <label class="fs-12 fw-bold" for="">كلمة المرور الجديدة *</label>
                                                    <div class="position-relative">
                                                        <input type="password" name="password" id="password" class="form-control mt-2 rounded-4 bg-ddd" placeholder="كلمة المرور الجديدة" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="fs-12 fw-bold" for="">تأكيد كلمة المرور الجديدة *</label>
                                                    <div class="position-relative">
                                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control mt-2 rounded-4 bg-ddd" placeholder="تأكيد كلمة المرور الجديدة" required>
                                                    </div>
                                                </div>
                                                <div class="col-12 mt-3">
                                                    <div class="form-check" style="width:fit-content;">
                                                        <input class="form-check-input" type="checkbox" id="show_passwords" onclick="document.getElementById('password').type = this.checked ? 'text' : 'password'; document.getElementById('password_confirmation').type = this.checked ? 'text' : 'password';">
                                                        <label class="form-check-label fs-14" for="show_passwords">
                                                            إظهار كلمات المرور
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mt-3">
                                                    <div class="alert alert-warning rounded-3 p-3 fs-14">يجب أن يتجاوز عدد أحرف كلمة المرور ٧ أحرف</div>
                                                </div>
                                                <div class="col-md-12 mt-2 text-start">
                                                    <button type="submit" class="btn btn-primary rounded-4 w-100 p-3 fs-12 fw-bold shadow-sm">تحديث كلمة المرور</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>    
@endsection