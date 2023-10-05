<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8"/>
    <title>@yield('title') - Laravel Admin & Dashboard Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta content="Tailwind Admin & Dashboard Template" name="description"/>
    <meta content="" name="Themesbrand"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}"/>
    <!-- css -->
    <!-- Styles -->
    @livewireStyles
    @filamentStyles
    @vite(['resources/css/app.css'])
    @include('layouts.head-css')
</head>

<body data-mode="light" data-sidebar-size="lg">
@livewire('notifications')
<!-- content -->
@yield('content')
<!-- rtl-ltr -->
@include('layouts.rtl-ltr')
<!-- script -->
@include('layouts.vendor-scripts')
<!-- Scripts -->
@livewireScripts
@filamentScripts
@vite(['resources/js/app.js'])
</body>
</html>
