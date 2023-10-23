@extends('layouts.master')
@section('title')
    {{ __('Create Property Weighting') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $text['create'] }}' }"
                    x-text="message"></h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2 x-data="{ message: '{{ $text['create'] }}' }" x-text="message"></h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('property-weighting.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>
                    <div class="mx-1 py-1 col-span-9 xl:col-span-6">
                        @livewire('property-weighting.create-property-weighting')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
