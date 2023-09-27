<body>
@extends('layouts.master')
@section('title')
    {{ __('Reservations') }}
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <style>
        .ml-15 {
            margin-left: 15px;
        }
    </style>
@endsection
