@extends('layouts.master')
@section('title')
    {{ __('Insurance Vendors Documentation') }}
@endsection
@section('content')
    <x-page-title title="Insurance Vendors Documentation" pagetitle="index"/>
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('insurance.documentation-table', ['viewAll' => $viewAll])
        </div>
    </div>
@endsection
