@php
    $tabGroups = [
        'Additional Info' => [
            ['title' => 'Attributes', 'component' => 'hotels.product-attributes-table'],
            ['title' => 'Age Restrictions', 'component' => 'hotels.hotel-age-restriction-table'],
            ['title' => 'Affiliations', 'component' => 'hotels.product-affiliations-table'],
            ['title' => 'Contact Information', 'component' => 'hotels.product-contact-information-table'],
        ],
        'Informational Service' => [
            ['title' => 'Informational Service', 'component' => 'hotels.product-informative-services-table'],
        ],
        'Website' => [
            ['title' => 'Website Search Generation', 'component' => 'hotels.hotel-web-finder-table'],
        ],
        'Rooms' => [
            ['title' => 'Rooms', 'component' => 'hotels.hotel-room-table'],
        ],
        'Promotions' => [
            ['title' => 'Promotions', 'component' => 'hotels.hotel-promotion-table'],
        ],
        'Pricing Rules' => [
            ['title' => 'Key & Owner', 'component' => 'hotels.key-mapping-table'],
            ['title' => 'Pricing Rules', 'component' => 'pricing-rules.pricing-rules-table'],
            ['title' => 'Deposit Information', 'component' => 'hotels.product-deposit-information-table'],
        ],
        'Fee and Tax' => [
            ['title' => 'Fee and Tax', 'component' => 'hotels.hotel-fee-tax-table'],
        ],
        'Descriptive Content' => [
            ['title' => 'Descriptive Content Section', 'component' => 'hotels.hotel-descriptive-content-section-table'],
        ],
    ];

    $hotelTitle = ['Rooms', 'Website Search Generation'];

    $formTabs = [
        '-product-tab' => 'Product',
        '-location-tab' => 'Location',
        '-data-sources-tab' => 'Data Sources',
    ];

//    ksort($tabGroups);
@endphp
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
                        <div class="col-lg-12 margin-tb row">
                            <div class="mb-6">
                                <x-button-back class="large-button" route="{{ route('product-repository.index') }}" text="Back to Products"/>
                            </div>
                            <div class="mb-6 pl-3">
                                <x-button-back class="large-button" route="{{ route('hotel-repository.index') }}" text="Back to Hotels"/>
                            </div>
                        </div>
                    </div>

                    <div x-data="{
                        activeTab: '{{ array_key_first($formTabs) }}' }"
                         class="sr_tab-container">

                        <ul class="sr_tab-list flex justify-between w-full">
                            @foreach ($formTabs as $tabId => $tabName)
                                <li class="sr_tab-item mr-1 flex items-end">
                                    <a href="#"
                                       class="sr_tab-link"
                                       :class="{ 'sr_active': activeTab === '{{ $tabId }}' }"
                                       @click.prevent="activeTab = '{{ $tabId }}'; $wire.set('activeTab', '{{ $tabId }}')">
                                        <span>{{ $tabName }}</span>
                                    </a>
                                </li>
                            @endforeach

                            @foreach ($tabGroups as $group => $tabs)
                                <li class="sr_tab-item mr-1 flex items-end">
                                    <a href="#"
                                       class="sr_tab-link"
                                       :class="{ 'sr_active': activeTab === '{{ Str::slug($group) }}' }"
                                       @click.prevent="activeTab = '{{ Str::slug($group) }}'">
                                        <span>{{ $group }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                            @livewire('hotels.hotel-form', compact('hotel'))
                        </div>

                        <div class="sr_tab-content w-full">
                            @foreach ($tabGroups as $group => $tabs)
                                <div x-show="activeTab === '{{ Str::slug($group) }}'" class="sr_tab-panel">
                                    @foreach ($tabs as $tab)
                                        <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4">{{ $tab['title'] }}</h3>
                                        @if ($tab['title'] === 'Pricing Rules')
                                            @livewire($tab['component'], ['hotelId' => $hotelId, 'isSrCreator' => true])
                                        @elseif (in_array($tab['title'], $hotelTitle))
                                            @livewire($tab['component'], ['hotelId' => $hotelId])
                                        @else
                                            @livewire($tab['component'], ['productId' => $productId])
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
