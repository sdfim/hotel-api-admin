@extends('layouts.master')
@section('title')
    {{ __('ICE Portal') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="ICE Portal" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('ice-portal-property-table')
        </div>
    </div>
@endsection
