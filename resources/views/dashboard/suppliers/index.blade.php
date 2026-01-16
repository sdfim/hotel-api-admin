@extends('layouts.master')
@section('title')
    {{ __('Suppliers') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Suppliers" pagetitle="index" />
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('suppliers.suppliers-table')
        </div>
    </div>
@endsection