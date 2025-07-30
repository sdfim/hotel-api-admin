@extends('layouts.master')
@section('title')
    {{ __('Hotel Trader') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Hotel Trader" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('hotel-trader-content-hotel-table')
        </div>
    </div>
@endsection
