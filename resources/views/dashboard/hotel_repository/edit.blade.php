@extends('layouts.master')
@section('title')
    {{ __('Edit Hotel') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h2 class="text-xl font-semibold" x-data="{ message: '{{ $text['edit'] }}' }"
                    x-text="message"></h2>
            </div>
            <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="mb-6">
                                <x-button-back class="large-button" route="{{ route('hotel_repository.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>
                    <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                        @livewire('hotels.hotel-form', compact('hotel'))
                    </div>

                    <hr class="mt-8 mb-8">

                    <div x-show="layout === 'grouped'">
                        @include('dashboard.hotel_repository.hotel-tables-tabs', ['hotelId' => $hotelId])
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
