@extends('layouts.master')
@section('title')
    {{ __('Failed Jobs') }}
@endsection
@section('content')
    <x-page-title title="Failed Jobs" pagetitle="index" />

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('failed-job-table')
        </div>
    </div>
@endsection