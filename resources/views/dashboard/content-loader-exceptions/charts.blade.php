@extends('layouts.master')
@section('title')
    {{ __('Exceptions Report') }}
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}"
          rel="stylesheet"
          type="text/css">

    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet"
    >
@endsection
@section('content')

    <!-- -->
    <x-page-title title="Exceptions Report Charts" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body relative overflow-x-auto">
                    <div class="card-body relative overflow-x-auto">
                        @livewire('charts.expedia-exception-report-chart')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')

@endsection
