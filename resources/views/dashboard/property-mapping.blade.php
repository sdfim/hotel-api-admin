@extends('layouts.master')
@section('title')
    {{ __('Property Mapping') }}
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}"
          rel="stylesheet"
          type="text/css">

    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
@endsection
@section('content')

    <!-- -->
    <x-page-title title="Property Mapping" pagetitle="index"/>

@endsection
@section('scripts')
    
@endsection
