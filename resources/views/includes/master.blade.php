<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    @include('includes.header')
    @unless (isset($noNavbar) && $noNavbar) 
        @include('includes.navbar')
    @endunless
</head>
<body>
    @yield('content')

    @if (isset($loader) && $loader) 
        @include('includes.loader')
    @endif
    @unless (isset($noFooter) && $noFooter) 
        @include('includes.footer')
    @endunless

    @yield('js')
</body>
</html>