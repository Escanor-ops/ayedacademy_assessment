<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0'">
    <link rel="stylesheet" href="{{asset('/layouts/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('/layouts/css/owl.carousel.min.css')}}">
    <link rel="stylesheet" href="{{asset('/layouts/css/owl.theme.default.min.css')}}">
    <link rel="stylesheet" href="{{asset('/layouts/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('/layouts/css/rtl-style.css')}}">
    <link rel="stylesheet" href="{{ asset('layouts/css/toast.min.css') }}">
    <script src="{{asset('layouts/js/toast.js')}}"></script>
    <title>أكاديمية عايد للتدريب</title>
</head>
<body>

@if(session('success'))
        <script>
            _alert(`{{ session('success') }}`, 'success', 4000)
        </script>
    @endif

    @if(session('failed'))
        <script>
            _alert(`{{ session('failed') }}`, 'error')
        </script>
    @endif

   