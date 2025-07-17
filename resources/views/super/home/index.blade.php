@extends('includes.master')
@section('title', 'من نحن')

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
                            <div class="d-flex gap-3">
                                <a href="#" class="btn btn-dark rounded-circle" style="padding:4px 7px;"><span class="fa fa-graduation-cap"></span></a>
                                <!-- <button class="btn btn-dark rounded-circle fs-14" onclick="_toggle_customer_sidebar()" style="padding:4px 11px;"><span class="fa fa-bars"></span></button> -->
                            </div>
                        </div>
                        <div class="profile-content settings">
                           to be designed
    
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>    
    
@endsection