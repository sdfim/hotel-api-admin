@extends('layouts.master')
@section('title')
    {{ __('Amenities') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Amenities" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.amenities.amenities-table')
        </div>
    </div>
@endsection
