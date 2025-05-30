@extends('layouts.master')
@section('title')
    {{ __('Bed Types in Room') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Bed Type in Room" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.room-bed-types.room-bed-type-table')
        </div>
    </div>
@endsection
