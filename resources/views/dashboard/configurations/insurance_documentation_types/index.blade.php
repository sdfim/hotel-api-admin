@extends('layouts.master')
@section('title')
    {{ __('Insurance Documentation Types') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Insurance Documentation Types" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.insurance-documentation-types.insurance-documentation-types-table')
        </div>
    </div>
@endsection
