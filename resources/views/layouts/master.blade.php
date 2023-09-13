<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') - Laravel Admin & Dashboard Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta content="Tailwind Admin & Dashboard Template" name="description" />
    <meta content="" name="Themesbrand" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}" />
    <!-- css files -->
    @include('layouts.head-css')
    <!-- Styles -->
    @livewireStyles
</head>

<body data-mode="light" data-sidebar-size="lg">
    <!-- topbar -->
    @include('layouts.topbar')
    <!-- sidebar -->
    @include('layouts.sidebar')

    <div class="main-content">
        <div class="page-content dark:bg-zinc-700 min-h-screen">
            <div class="container-fluid px-[0.625rem]">
                <!-- content -->
                @yield('content')
            </div>
        </div>
        <!-- footer -->
        @include('layouts.footer')
    </div>
    <!-- rtl-ltr -->
    @include('layouts.rtl-ltr')
    <!-- script -->
    @include('layouts.vendor-scripts')
      <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @livewireScripts
</body>

</html>
