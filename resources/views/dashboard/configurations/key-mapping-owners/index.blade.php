@extends('layouts.master')
@section('title')
    {{ __('External Identifiers') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="External Identifiers" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.key-mapping-owners.key-mapping-owner-table')
        </div>
    </div>
@endsection
