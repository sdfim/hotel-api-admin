@php
    $group = [
        ['title' => 'Descriptive Content', 'component' => 'products.hotel-descriptive-content-section-table'],
        ['title' => 'Fees and Taxes', 'component' => 'products.hotel-fee-tax-table'],
        ['title' => 'Ultimate Amenities', 'component' => 'products.product-affiliations-table'],
        ['title' => 'Pricing Rules', 'component' => 'pricing-rules.pricing-rules-table'],
        ['title' => 'Deposit Information', 'component' => 'products.product-deposit-information-table'],
        ['title' => 'Cancellation Policy', 'component' => 'products.product-cancellation-policy-table'],
        ['title' => 'Promotions', 'component' => 'products.hotel-promotion-table'],
        ['title' => 'Addons', 'component' => 'products.product-informative-services-table'],
    ];
@endphp

@extends('layouts.master')
@section('title')
    @if($hotelRate->exists)
        {{ __('Edit Rate') }}
    @else
        {{ __('Create Rate') }}
    @endif
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $hotelRate->exists ? $text['edit'] : $text['create'] }}' }"
                    x-text="message"></h6>
            </div>
            <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                <div class="relative">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="mb-6">
                                <x-button-back route="{{ route('hotel-repository.edit', ['hotel_repository' => $hotel->id]) }}?tab=rates" text="Back"/>
                            </div>
                        </div>
                    </div>
                    <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                        @livewire('hotels.hotel-rate-form', compact('hotelRate', 'hotel'))
                    </div>
                    <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                        @if ($product)
                            @foreach ($group as $tab)
                                <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4">{{ $tab['title'] }}</h3>
                                @if ($tab['title'] === 'Pricing Rules')
                                    @livewire($tab['component'], ['productId' => $product->id, 'isSrCreator' => true, 'rateCode' => $hotelRate->code])
                                @else
                                    @livewire($tab['component'], ['product' => $product, 'rateId' => $hotelRate->id])
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
