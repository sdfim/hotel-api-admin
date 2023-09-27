<body>
    @extends('layouts.master')
    @section('title')
        {{ __('Weight') }}
    @endsection
    @section('css')
        <link rel="stylesheet" href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}">
    @endsection
