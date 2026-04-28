<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="{{ asset('assets/css/material-dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('dashboard/assets/css/nucleo-icons.css') }}">
<link rel="stylesheet" href="{{ asset('dashboard/assets/css/nucleo-svg.css') }}">


<head>

@include('chat.layout.common-head')
</head>
<body class="g-sidenav-show  bg-gray-200">
@include('chat.layout.sidebar')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
@include('chat.layout.header')
    @section('main-content')
    @include('chat.layout.footer')
    @show

@include('chat.layout.common-end')
@stack('custom-scripts')
</body>
</html>
