@extends('includes.master')
@section('title', 'إعادة تعيين كلمة المرور')
@php
    $noAppSection = true;
@endphp
@section('content')
   
    <section class="main registration w-100 d-flex justify-content-center align-items-center">
        <div class="container">
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <div class="block bg-white mx-auto rounded-5 p-3 position-relative overflow-hidden active" id="first-tab">
                    <div class="first-tab tab w-100 d-flex flex-column p-5 justify-content-between position-relative">
                        <div class="block-header d-flex justify-content-between">
                            <div>
                                <h3 class="fw-bold">إعادة تعيين كلمة المرور</h3>
                                <p class="text-muted mt-2">أدخل رمز التحقق وكلمة المرور الجديدة</p>
                            </div>
                            <img width="50px" height="50px" src="{{asset('uploads/images/password.svg')}}" alt="password icon">
                        </div>
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="block-body"> 
                            <div class="row">
                                <div class="col-12">
                                    <label for="verification_code" class="fw-bold mb-1 fs-14">رمز التحقق</label>
                                    <input type="text" name="verification_code" id="verification_code" class="form-control p-3" placeholder="رمز التحقق" required>
                                </div>
                                <div class="col-12 mt-3">
                                    <label for="password" class="fw-bold mb-1 fs-14">كلمة المرور الجديدة</label>
                                    <div class="position-relative">
                                        <input type="password" name="password" id="password" class="form-control p-3" placeholder="كلمة المرور الجديدة" required>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label for="password_confirmation" class="fw-bold mb-1 fs-14">تأكيد كلمة المرور الجديدة</label>
                                    <div class="position-relative">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control p-3" placeholder="تأكيد كلمة المرور الجديدة" required>
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
                            </div>
                        </div>
                        <div class="block-footer mt-3 d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-dark rounded-4 p-3 d-flex gap-3 align-items-center fs-14">تحديث كلمة المرور <span class="fa fa-arrow-left"></span></button>
                            <a href="{{ route('login') }}" class="text-dark text-decoration-none">العودة لتسجيل الدخول</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection 