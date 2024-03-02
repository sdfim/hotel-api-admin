@extends('layouts.master')
@section('title')
    {{ __('Expedia Charts') }}
@endsection
@section('content')
    <x-page-title title="Statistic charts" pagetitle="index"/>
    <div class="grid grid-cols-2 gap-5">
        <div class="card-body relative overflow-x-auto">
            @livewire('charts.expedia-rating-chart')
        </div>
        <div class="card-body relative overflow-x-auto">
            @livewire('charts.giata-city-chart')
        </div>
    </div>

    <div class="grid grid-cols-2 gap-5">
        <div class="card-body relative overflow-x-auto">
            @livewire('charts.search-inspector-radar-chart')
        </div>
        <div class="card-body relative overflow-x-auto">
            @livewire('charts.search-inspector-frequent-destinations-chart')
        </div>
    </div>

    <div class="grid grid-cols-2 gap-5">
        <div class="card-body relative overflow-x-auto">
            @livewire('charts.search-inspector-rooms-doughnut-chart')
        </div>
        <div class="card-body relative overflow-x-auto">
            @livewire('charts.search-inspector-occupancy-doughnut-chart')
        </div>
    </div>

    <div class="grid grid-cols-2 gap-5">
        <div class="card-body relative overflow-x-auto">
            @livewire('charts.search-inspector-children-doughnut-chart')
        </div>
        <div class="card-body relative overflow-x-auto">
            @livewire('charts.search-inspector-nights-doughnut-chart')
        </div>
    </div>
@endsection
