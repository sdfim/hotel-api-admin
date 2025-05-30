@extends('layouts.master')
@section('title')
    {{ __('Chains') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Chains" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.chains.chains-table')
        </div>
    </div>
@endsection
