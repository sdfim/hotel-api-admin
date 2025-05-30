@extends('layouts.master')
@section('title')
    {{ __('TerraMare Department') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="TerraMare Department" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.contact-information-departments.contact-information-department-table')
        </div>
    </div>
@endsection
