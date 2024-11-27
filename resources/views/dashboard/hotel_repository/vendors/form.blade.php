@php
    $tabGroups = [
        'Vendors Detail' => [
            'tab_name' => 'vendors-tab',
            'related' => [
                ['title' => 'General Information', 'component' => 'vendors.vendor-form'],
                ['title' => 'Contact Information', 'component' => 'products.contact-information-table'],
            ],
        ],
        'Products' => [
            'tab_name' => 'products',
            'related' => [
                ['title' => 'Products', 'component' => 'products.product-table'],
            ],
        ],
        'Hotels' => [
            'tab_name' => 'hotels',
            'related' => [
                ['title' => 'Hotels', 'component' => 'hotels.hotel-table'],
            ],
        ],
    ];

    $vendorTitle = ['General Information'];
@endphp
@extends('layouts.master')
@section('title')
    @if($vendor->exists)
        {{ __('Edit Vendors') }}
    @else
        {{ __('Create Vendors') }}
    @endif
@endsection
@section('content')
    <div class="breadcrumb-container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                {{--                        <li class="breadcrumb-item"><a href="{{ route('supplier-repository.index') }}">Supplier Repository</a></li>--}}
                <li class="breadcrumb-item"><a href="{{ route('vendor-repository.index') }}">Vendors</a></li>
                <li class="breadcrumb-item"><a href="#">{{ $vendor->name }}</a></li>
            </ol>
        </nav>
    </div>

    <h2 class="font-semibold" x-data="{ message: '{{ $vendor->exists ? $text['edit'] : $text['create'] }} {{ $vendor->name }}' }"
            x-text="message"></h2>

    <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
        <div class="relative overflow-x-auto">

            <div x-data="{activeTab: '{{ $tabGroups[array_key_first($tabGroups)]['tab_name'] }}' }"
                 class="sr_tab-container">

                <ul class="sr_tab-list flex justify-between w-full">
                    @foreach ($tabGroups as $group => $tabs)
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

                <div class="sr_tab-content w-full">
                    @foreach ($tabGroups as $group => $tabs)
                        <div x-show="activeTab === '{{ $tabs['tab_name'] }}'" class="sr_tab-panel">
                            @foreach ($tabs['related'] as $tab)
                                <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4">{{ $tab['title'] }}</h3>
                                @if (in_array($tab['title'], $vendorTitle))
                                    @livewire($tab['component'], ['vendor' => $vendor])
                                @elseif ($tab['title'] === 'Contact Information')
                                    @livewire($tab['component'], ['contactableId' => $vendor->id, 'contactableType' => 'Vendor'])
                                @else
                                    @livewire($tab['component'], ['vendor' => $vendor])
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection
