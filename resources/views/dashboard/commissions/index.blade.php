@extends('layouts.master')
@section('title')
{{ __('Travel Agency Commissions') }}
@endsection
@section('content')
<x-page-title title="Travel Agency Commissions" pagetitle="index"/>
<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        @livewire('commissions.travel-agency-commission-table', ['productId' => $productId ?? 0])
    </div>
</div>
@endsection
