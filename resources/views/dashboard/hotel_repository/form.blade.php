@php
    $tabGroups = [
        'Product' => [
            'tab_name' => '-product-tab',
            'related' => [
                ['title' => 'External Identifiers', 'component' => 'products.key-mapping-table'],
                ['title' => 'Advisor Commission', 'component' => 'commissions.travel-agency-commission-table'],
                ['title' => 'Meal Plan Mapping', 'component' => 'meal-plan-mapping.table'],
            ],
        ],
        'Location' => [
            'tab_name' => '-location-tab',
            'related' => [],
        ],
        'Rooms' => [
            'tab_name' => 'rooms',
            'related' => [
                ['title' => 'Rooms', 'component' => 'hotels.hotel-room-table'],
            ],
        ],
        'Fees and Taxes' => [
            'tab_name' => 'fee-and-tax',
            'marker' => 'entity-pricing',
            'related' => [
                ['title' => 'Fees and Taxes', 'component' => 'products.product-fee-tax-table', 'marker' => ['pricing']],
            ],
        ],
        'Attributes' => [
            'tab_name' => 'attributes',
            'related' => [
                ['title' => 'Hotel Attributes', 'component' => 'products.product-attributes-table'],
            ],
        ],
        'Descriptive Content' => [
            'tab_name' => 'descriptive-content',
            'related' => [
                ['title' => 'Descriptive Content', 'component' => 'products.hotel-descriptive-content-section-table'],
            ],
        ],
        'Pricing Rules' => [
            'tab_name' => 'pricing-rules',
            'related' => [
                ['title' => 'Pricing Rules', 'component' => 'pricing-rules.pricing-rules-table'],
                ['title' => 'Deposit Information', 'component' => 'products.product-deposit-information-table'],
            ],
        ],
        'Galleries' => [
            'tab_name' => 'images-gallery',
            'related' => [
                ['title' => 'Image Galleries', 'component' => 'image-galleries.image-galleries-table'],
            ],
        ],
    ];

    $hotelTitle = ['Rooms', 'Rates', 'Website Search Generation', 'Meal Plan Mapping'];
    $createTabs = ['Product', 'Location', 'Gallery',];
@endphp
@extends('layouts.master')
@section('title')
    {{ $hotel->exists ? __('Edit Hotel') : __('Create Hotel') }} {{ $hotel?->product?->name }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="pb-0">
            <h2 class="font-semibold dark:text-white"
                x-data="{ message: '{{ $hotel->exists ? $text['edit'] : $text['create'] }} {{ $hotel?->product?->name }}' }"
                x-text="message"></h2>
        </div>

        <div class="text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
            <div class="relative overflow-x-auto">

                <div x-data="{activeTab: new URLSearchParams(window.location.search).get('tab') || '{{ $tabGroups[array_key_first($tabGroups)]['tab_name'] }}' }"
                    class="sr_tab-container">

                    <ul class="sr_tab-list flex justify-between w-full dark:bg-gray-900">
                        @foreach ($tabGroups as $group => $tabs)
                                                @if (!$hotel->exists && !in_array($group, $createTabs))
                                                    @continue
                                                @endif
                                                <li class="sr_tab-item mr-1 flex items-end">
                                                    <a href="#" class="sr_tab-link dark:bg-gray-400"
                                                        :class="{ 'sr_active': activeTab === '{{ $tabs['tab_name'] }}' }" @click.prevent="
                                                                   activeTab = '{{ $tabs['tab_name'] }}';
                            {{--                                       $wire.set('activeTab', '{{ $tabs['tab_name'] }}')--}}
                                                                    const url = new URL(window.location);
                                                                   url.searchParams.set('tab', '{{ $tabs['tab_name'] }}');
                                                                   window.history.pushState({}, '', url);
                                                                   ">
                                                        <span>{{ $group }}</span>
                                                    </a>
                                                </li>
                        @endforeach
                    </ul>

                    <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                        @livewire('hotels.hotel-form', compact('hotel'))
                    </div>

                    @if ($hotel->exists)
                        <div class="sr_tab-content w-full">
                            @foreach ($tabGroups as $group => $tabs)
                                <div x-show="activeTab === '{{ $tabs['tab_name'] }}'" class="sr_tab-panel">
                                    @foreach ($tabs['related'] as $tab)
                                            <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4">{{ $tab['title'] }}</h3>
                                            @if ($tab['title'] === 'Pricing Rules')
                                                @livewire($tab['component'], [
                                                    'productId' => $product->id,
                                                    'isSrCreator' =>
                                                        true
                                                ])
                                            @elseif ($tab['title'] === 'Contact Information')
                                            @livewire($tab['component'], [
                                                'contactableId' => $product->id,
                                                'contactableType' => 'Product'
                                            ])
                                        @elseif (in_array($tab['title'], $hotelTitle))
                                                @livewire($tab['component'], ['hotel' => $hotel])
                                            @else
                                                @livewire($tab['component'], ['product' => $product])
                                            @endif
                                    @endforeach
                                    </div>
                            @endforeach
                            </div>
                    @endif
                </div>
            </div>
            </div>
        </div>
@endsection
@section('scripts')
    @stack('scripts')
@endsection
