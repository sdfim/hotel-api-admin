@extends('layouts.master')
@section('title')
    {{ __('Geography') }}
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
@endsection
@section('content')

    <!-- -->
    <x-page-title title="Geography" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('giata-geography-table')
        </div>
    </div>
@endsection
@section('scripts')
@endsection
