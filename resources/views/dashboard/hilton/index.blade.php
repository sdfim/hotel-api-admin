@extends('layouts.master')
@section('title')
    {{ __('Hilton') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Hilton" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('hilton-property-table')
        </div>
    </div>
@endsection
