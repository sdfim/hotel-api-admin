@extends('layouts.master')
@section('title')
    {{ __('Reservations List') }}
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css') }}">
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Reservations" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('reservations-table')
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/libs/glightbox/js/glightbox.min.js') }}"></script>

    <!-- lightbox init -->
    <script src="{{ URL::asset('build/js/pages/lightbox.init.js') }}"></script>
@endsection
