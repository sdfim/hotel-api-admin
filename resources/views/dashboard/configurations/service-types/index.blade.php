@extends('layouts.master')
@section('title')
    {{ __('Service Types') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Service Types" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.service-types.service-types-table')
        </div>
    </div>
@endsection
