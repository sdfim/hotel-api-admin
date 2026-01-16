@extends('layouts.master')
@section('title')
    {{ __('Hotel') }}
@endsection
@section('content')
    <h2 class="font-semibold">Hotels</h2>
    {{-- <x-page-title title="Hotel" pagetitle="index" />--}}

    <div class="col-span-12">
        <div class="relative overflow-x-auto text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
            @livewire('hotels.hotel-table')
        </div>
    </div>
@endsection