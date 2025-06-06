@extends('layouts.master')
@section('title')
{{ __('Deposit Information') }}
@endsection
@section('content')
<x-page-title title="Deposit Information" pagetitle="index"/>
<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        @livewire('deposit-information.deposit-information-table')
    </div>
</div>
@endsection
