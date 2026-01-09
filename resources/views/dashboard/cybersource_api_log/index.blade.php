@extends('layouts.master')
@section('title')
    {{ __('Cybersource Log') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Cybersource Log" pagetitle="index" />

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('cybersource-api-log-table')
        </div>
    </div>
@endsection