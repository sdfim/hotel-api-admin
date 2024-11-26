@php
    $tabGroups = [
        'Product' => [
            'tab_name' => '-product-tab',
            'related' => [
                ['title' => 'Contact Information', 'component' => 'products.contact-information-table'],
                ['title' => 'Age Restrictions', 'component' => 'products.hotel-age-restriction-table'],
                ['title' => 'Affiliations', 'component' => 'products.product-affiliations-table'],
            ],
        ],
        'Location' => [
            'tab_name' => '-location-tab',
            'related' => [],
        ],
        'Data Sources' => [
            'tab_name' => '-data-sources-tab',
            'related' => [
                ['title' => 'Website Search Generation', 'component' => 'hotels.hotel-web-finder-table'],
            ],
        ],
        'Attributes' => [
            'tab_name' => 'attributes',
            'related' => [
                ['title' => 'Hotel Attributes', 'component' => 'products.product-attributes-table'],
            ],
        ],
        'Rooms' => [
            'tab_name' => 'rooms',
             'related' => [
                ['title' => 'Rooms', 'component' => 'hotels.hotel-room-table'],
             ],
        ],
        'Fee and Tax' => [
            'tab_name' => 'fee-and-tax',
            'related' => [
                ['title' => 'Fee and Tax', 'component' => 'products.hotel-fee-tax-table'],
            ],
        ],
        'Service' => [
            'tab_name' => 'service',
            'related' => [
                ['title' => 'Informational Service', 'component' => 'products.product-informative-services-table'],
            ],
        ],
        'Promotions' => [
            'tab_name' => 'promotions',
            'related' => [
                ['title' => 'Promotions', 'component' => 'products.hotel-promotion-table'],
            ],
        ],
        'Descriptive Content' => [
            'tab_name' => 'descriptive-content',
            'related' => [
                ['title' => 'Descriptive Content Section', 'component' => 'products.hotel-descriptive-content-section-table'],
            ],
        ],
        'Pricing Rules' => [
            'tab_name' => 'pricing-rules',
            'related' => [
                ['title' => 'Key & Owner', 'component' => 'products.key-mapping-table'],
                ['title' => 'Pricing Rules', 'component' => 'pricing-rules.pricing-rules-table'],
                ['title' => 'Deposit Information', 'component' => 'products.product-deposit-information-table'],
            ],
        ],
    ];

    $hotelTitle = ['Rooms', 'Website Search Generation'];
    $createTabs = ['Product', 'Location', 'Data Sources'];
@endphp
@extends('layouts.master')
@section('title')
    {{ $hotel->exists ? __('Edit Hotel') : __('Create Hotel') }} {{ $hotel?->product?->name }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class=" dark:bg-zinc-800 dark:border-zinc-600">
            <div class="breadcrumb-container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Admin</a></li>
{{--                        <li class="breadcrumb-item"><a href="{{ route('supplier-repository.index') }}">Supplier Repository</a></li>--}}
{{--                        <li class="breadcrumb-item"><a href="{{ route('vendor-repository.index') }}">Vendors</a></li>--}}
                        <li class="breadcrumb-item"><a href="{{ route('product-repository.index') }}">Products</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hotel-repository.index') }}">Hotels</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $hotel?->product?->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="card-body pb-0">
                <h2 class="font-semibold" x-data="{ message: '{{ $hotel->exists ? $text['edit'] : $text['create'] }} {{ $hotel?->product?->name }}' }"
                    x-text="message"></h2>
            </div>

            <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                <div class="relative overflow-x-auto">

                    <div x-data="{activeTab: '{{ $tabGroups[array_key_first($tabGroups)]['tab_name'] }}' }"
                         class="sr_tab-container">

                        <ul class="sr_tab-list flex justify-between w-full">
                            @foreach ($tabGroups as $group => $tabs)
                                @if (!$hotel->exists && !in_array($group, $createTabs))
                                    @continue
                                @endif
                                <li class="sr_tab-item mr-1 flex items-end">
                                    <a href="#"
                                       class="sr_tab-link"
                                       :class="{ 'sr_active': activeTab === '{{ $tabs['tab_name'] }}' }"
                                       @click.prevent="activeTab = '{{ $tabs['tab_name'] }}'; $wire.set('activeTab', '{{ $tabs['tab_name'] }}')">
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
                                                @livewire($tab['component'], ['productId' => $productId, 'isSrCreator' => true])
                                            @elseif ($tab['title'] === 'Contact Information')
                                                @livewire($tab['component'], ['contactableId' => $productId, 'contactableType' => 'Product'])
                                            @elseif (in_array($tab['title'], $hotelTitle))
                                                @livewire($tab['component'], ['hotelId' => $hotelId])
                                            @else
                                                @livewire($tab['component'], ['productId' => $productId])
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
    </div>
@endsection
