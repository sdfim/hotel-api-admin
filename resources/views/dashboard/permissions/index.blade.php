@extends('layouts.master')
@section('title')
    {{ __('Permissions') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Permissions" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('permissions-table')
        </div>
    </div>
@endsection
