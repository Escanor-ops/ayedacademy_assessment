@extends('includes.master')
@section('title', 'تسجيل الدخول')
@php

    $noAppSection = true;
@endphp
@section('content')
   
    <section class="main registration w-100 d-flex justify-content-center align-items-center">
        <div class="container">
            <form action="{{ route('attempt_login') }}" method="POST">
                @csrf
                <div class="block bg-white mx-auto rounded-5 p-3 position-relative overflow-hidden active" id="first-tab">
                    <div class="first-tab tab w-100 d-flex flex-column p-5 justify-content-between position-relative">
                        <div class="block-header d-flex justify-content-between">
                            <div>
                                <h3 class="fw-bold">تسجيل الدخول</h3>
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
                                <div class="col-12 mt-2">
                                    <label for="password" class="fw-bold mb-1 fs-14">كلمة المرور</label>
                                    <div class="position-relative">
                                        <input type="password" name="password" id="password" class="form-control p-3" value="{{ old('password') }}" placeholder="كلمة المرور" required>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="form-check" style="width:fit-content;">
                                            <input class="form-check-input" type="checkbox" id="show_password" onclick="document.getElementById('password').type = this.checked ? 'text' : 'password';">
                                            <label class="form-check-label fs-14" for="show_password">
                                                إظهار كلمة المرور
                                            </label>
                                        </div>
                                        <a href="{{ route('password.request') }}" class="text-primary text-decoration-none fs-14">نسيت كلمة المرور؟</a>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="block-footer mt-3 d-flex justify-content-between">
                            <button type="submit" name="submit" class="btn btn-dark rounded-4 p-3 d-flex gap-3 align-items-center fs-14">دخول <span class="fa fa-arrow-left"></span></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    @endsection