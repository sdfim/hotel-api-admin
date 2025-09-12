@extends('layouts.master')
@section('title')
    {{ __('Airwallex Log') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Airwallex Log" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('airwallex-api-log-table')
        </div>
    </div>
@endsection
