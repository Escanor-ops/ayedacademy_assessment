@extends('includes.master')
@section('title', 'نسيت كلمة المرور')
@php
    $noAppSection = true;
@endphp
@section('content')
   
    <section class="main registration w-100 d-flex justify-content-center align-items-center">
        <div class="container">
            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="block bg-white mx-auto rounded-5 p-3 position-relative overflow-hidden active" id="first-tab">
                    <div class="first-tab tab w-100 d-flex flex-column p-5 justify-content-between position-relative">
                        <div class="block-header d-flex justify-content-between">
                            <div>
                                <h3 class="fw-bold">إعادة تعيين كلمة المرور</h3>
                                <p class="text-muted mt-2">أدخل بريدك الإلكتروني وسنرسل لك رمز التحقق</p>
                            </div>
                            <img width="50px" height="50px" src="{{asset('uploads/images/password.svg')}}" alt="password icon">
                        </div>
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        <div class="block-body"> 
                            <div class="row">
                                <div class="col-12">
                                    <label for="email" class="fw-bold mb-1 fs-14">البريد الإلكتروني</label>
                                    <input type="email" name="email" id="email" class="form-control p-3" placeholder="البريد الإلكتروني" value="{{ old('email') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="block-footer mt-3 d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-dark rounded-4 p-3 d-flex gap-3 align-items-center fs-14">إرسال رمز التحقق <span class="fa fa-arrow-left"></span></button>
                            <a href="{{ route('login') }}" class="text-dark text-decoration-none">العودة لتسجيل الدخول</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection 