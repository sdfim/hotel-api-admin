@extends('layouts.master')
@section('title')
    {{ __('Informational Services') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Informational Services" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('informational-services.informational-services-table')
        </div>
    </div>
@endsection
