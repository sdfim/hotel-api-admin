@extends('layouts.master')
@section('title')
    {{ __('Commissions') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Commissions" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('configurations.commissions.commission-table')
        </div>
    </div>
@endsection
