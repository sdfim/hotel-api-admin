@extends('layouts.master')
@section('title', __('Categories'))

@section('content')
    <!-- -->
    <x-page-title title="Categories" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.attribute-categories.attribute-categories-table')
        </div>
    </div>
@endsection
