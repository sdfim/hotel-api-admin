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

                    <div class="mt-5 ml-1 mr-1 col-span-9 xl:col-span-6" x-data="{ layout: 'grouped' }">
                        <div class="flex items-center justify-end space-x-2">
                            <select @change="layout = $event.target.value" class="ml-2 border rounded dark:bg-zinc-800 dark:border-zinc-600 dark:text-white">
                                <option value="grouped" :selected="layout === 'grouped'">Grouped</option>
                                <option value="list" :selected="layout === 'list'">Listed</option>
                                <option value="tabs" :selected="layout === 'tabs'">Tabbed</option>
                            </select>
                        </div>
                        <div x-show="layout === 'grouped'" class="mt-4">
                            @include('dashboard.hotel_repository.hotel-tables-tabs', ['hotelId' => $hotelId])
                        </div>
                        <div x-show="layout === 'list'" class="mt-4">
                            @include('dashboard.hotel_repository.hotel-tables', ['hotelId' => $hotelId])
                        </div>
                        <div x-show="layout === 'tabs'" class="mt-4">
                            @include('dashboard.hotel_repository.hotel-tables-tabs-v1', ['hotelId' => $hotelId])
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
