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
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body relative overflow-x-auto">
                    @livewire('giata-geography-table')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@endsection
