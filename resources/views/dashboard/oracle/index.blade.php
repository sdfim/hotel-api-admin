@extends('layouts.master')
@section('title')
    {{ __('Oracle') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Oracle" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('oracle-content-table')
        </div>
    </div>
@endsection
