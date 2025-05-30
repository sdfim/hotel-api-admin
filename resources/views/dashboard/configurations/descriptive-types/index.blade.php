@extends('layouts.master')
@section('title')
    {{ __('Descriptive Type') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Descriptive Type" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.descriptive-types.descriptive-types-table')
        </div>
    </div>
@endsection
