@extends('layouts.master')
@section('title')
    {{ __('Departments') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Departments" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.job-descriptions.job-descriptions-table')
        </div>
    </div>
@endsection
