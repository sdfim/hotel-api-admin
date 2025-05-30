<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8"/>
    <title>@yield('title') - Laravel Admin & Dashboard Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta content="Tailwind Admin & Dashboard Template" name="description"/>
    <meta content="" name="TerraMare"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}"/>
    <!-- css files -->
    <!-- Styles -->
    @filamentStyles
    @vite(['resources/css/app.css'])
    @if (request()->is('*-repository*') || request()->is('*-grid*') || request()->is('*insurance*') || request()->is('*activities*') || request()->is('*hotel-rates*'))
        @vite(['resources/css/supplier-repository.css'])
    @endif
    @include('layouts.head-css')
    @stack('head')
</head>

<body data-mode="light" data-sidebar-size="lg">
@livewire('notifications')
<!-- topbar -->
@include('layouts.topbar')
<!-- sidebar -->
@include('layouts.sidebar')

<div class="main-content">
    <div class="page-content dark:bg-zinc-800 min-h-screen">
        @if (session('success'))
            <x-flash-message :message="session('success')"/>
        @endif

        @if (session('error'))
            <x-flash-message type="error" :message="session('error')"/>
        @endif

        <div class="container-fluid px-[0.625rem]">
            <!-- content -->
            @yield('content')
        </div>
    </div>
    <!-- footer -->
    @include('layouts.footer')
</div>
<!-- rtl-ltr -->
{{--@include('layouts.rtl-ltr')--}}
<!-- script -->
@include('layouts.vendor-scripts')
<!-- Scripts -->
@filamentScripts
@vite(['resources/js/app.js'])
@yield('js')
@stack('livewire-scripts')
</body>
</html>
