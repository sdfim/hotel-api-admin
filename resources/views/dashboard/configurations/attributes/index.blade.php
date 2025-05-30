@extends('layouts.master')
@section('title')
    {{ __('Attributes') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Attributes" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.attributes.attributes-table')
        </div>
    </div>
@endsection
