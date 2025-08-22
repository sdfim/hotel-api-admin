@extends('layouts.master')
@section('title')
    {{ __('HBSI') }}
@endsection
@section('content')

    <x-page-title title="HBSI" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('hbsi-property-table')
        </div>
    </div>
@endsection
