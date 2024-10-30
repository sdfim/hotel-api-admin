@extends('layouts.master')
@section('title')
    {{ __('Config Group') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Config Group" pagetitle="index"/>

    <div class="mt-8">
        <div class="flex flex-col xl:flex-row gap-8">
            <div class="flex-1">
                <h2 class="text-xl font-semibold">Chains</h2>
                @livewire('configurations.chains.chains-table')
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-semibold">Attributes</h2>
                @livewire('configurations.attributes.attributes-table')
            </div>
        </div>
    </div>

    <div class="mt-8">
        <div class="flex flex-col xl:flex-row gap-8">
            <div class="flex-1">
                <h2 class="text-xl font-semibold">Consortia</h2>
                @livewire('configurations.consortia.consortia-table')
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-semibold">Descriptive Types</h2>
                @livewire('configurations.descriptive-types.descriptive-types-table')
            </div>
        </div>
    </div>

    <div class="mt-8">
        <div class="flex flex-col xl:flex-row gap-8">
            <div class="flex-1">
                <h2 class="text-xl font-semibold">Job Descriptions</h2>
                @livewire('configurations.job-descriptions.job-descriptions-table')
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-semibold">Service Types</h2>
                @livewire('configurations.service-types.service-types-table')
            </div>
        </div>
    </div>
@endsection
