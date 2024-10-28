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
                                <x-button-back route="{{ route('hotel_repository.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>
                    <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                        @livewire('hotels.hotel-form', compact('hotel'))
                    </div>

                    <div class="mt-8">
                        <h2 class="text-xl font-semibold">Key & Owner</h2>
                        @livewire('hotels.key-mapping-table', ['hotelId' => $hotelId])
                    </div>

                    <div class="mt-8">
                        <h2 class="text-xl font-semibold">Rooms</h2>
                        @livewire('hotels.hotel-room-table', ['hotelId' => $hotelId])
                    </div>

                    <div class="mt-8">
                        <div class="flex flex-col xl:flex-row gap-8">
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold">Attributes</h2>
                                @livewire('hotels.hotel-attributes-table', ['hotelId' => $hotelId])
                            </div>
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold">Services</h2>
                                @livewire('hotels.hotel-informative-services-table', ['hotelId' => $hotelId])
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="flex flex-col xl:flex-row gap-8">
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold">Age Restrictions</h2>
                                @livewire('hotels.hotel-age-restriction-table', ['hotelId' => $hotelId])
                            </div>
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold">Affiliations</h2>
                                @livewire('hotels.hotel-affiliations-table', ['hotelId' => $hotelId])
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-xl font-semibold">Fee and Tax</h2>
                        @livewire('hotels.hotel-fee-tax-table', ['hotelId' => $hotelId])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
