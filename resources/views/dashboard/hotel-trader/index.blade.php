@extends('layouts.master')
@section('title')
    {{ __('Hotel Trade') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Hotel Trade" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('hotel-trader-content-table')
        </div>
    </div>
@endsection
