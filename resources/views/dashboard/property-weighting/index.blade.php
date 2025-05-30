@extends('layouts.master')
@section('title')
    {{ __('Property Weightings') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Property Weighting" pagetitle="index"/>
    @can('create', \App\Models\PropertyWeighting::class)
        <div class="row">
            <div class="col-lg-12 margin-tb">
                <div class="mb-6">
                    <a class="btn text-mandarin-500 hover:text-white border-mandarin-500 hover:bg-mandarin-600 hover:border-mandarin-600 focus:bg-mandarin-600 focus:text-white focus:border-mandarin-600 focus:ring focus:ring-mandarin-500/30 active:bg-mandarin-600 active:border-mandarin-600"
                       href="{{ route('property-weighting.create') }}"> <i class="bx bx-plus block text-lg"></i></a>
                </div>

            </div>
        </div>
    @endcan
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('property-weighting.property-weighting-table')
        </div>
    </div>
@endsection
