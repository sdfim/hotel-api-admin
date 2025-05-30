@extends('layouts.master')
@section('title')
    {{ __('Insurance Rate Tiers') }}
@endsection
@section('content')
    <x-page-title title="Insurance Rate Tiers" pagetitle="index"/>
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('insurance.rate-tiers-table', ['viewAll' => $viewAll])
        </div>
    </div>
@endsection
