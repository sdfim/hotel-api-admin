@php
    $tabGroups = [
        'Policies sold' => [
            'tab_name' => 'insurances',
            'related' => [
                ['title' => 'Policies sold', 'component' => 'insurance.insurance-plans-table'],
            ],
        ],
        'Documentations' => [
            'tab_name' => 'documentations',
            'related' => [
                ['title' => 'Documentations', 'component' => 'insurance.documentation-table'],
            ],
        ],
        'Restrictions' => [
            'tab_name' => 'restrictions',
            'related' => [
                ['title' => 'Restrictions', 'component' => 'insurance.restrictions-table'],
            ],
        ],
        'Rate Tiers' => [
            'tab_name' => 'rate-tiers',
            'related' => [
                ['title' => 'Rate Tiers', 'component' => 'insurance.rate-tiers-table'],
            ],
        ],
    ];
@endphp
@extends('layouts.master')
@section('title')
    {{ __('Insurance') }}
@endsection
@section('content')
    <div class="breadcrumb-container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                {{--                        <li class="breadcrumb-item"><a href="{{ route('supplier-repository.index') }}">Supplier Repository</a></li>--}}
                <li class="breadcrumb-item"><a href="{{ route('insurance-plans.index') }}">Insurance</a></li>
            </ol>
        </nav>
    </div>

    <h2 class="font-semibold">Insurance</h2>

    <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
        <div class="relative overflow-x-auto">

            <div x-data="{activeTab: '{{ $tabGroups[array_key_first($tabGroups)]['tab_name'] }}' }"
                 class="sr_tab-container">

                <ul class="sr_tab-list flex justify-between w-full" style="margin-top: 0 !important;">
                    @foreach ($tabGroups as $group => $tabs)
                        <li class="sr_tab-item mr-1 flex items-end">
                            <a href="#"
                               class="sr_tab-link"
                               :class="{ 'sr_active': activeTab === '{{ $tabs['tab_name'] }}' }"
                               @click.prevent="activeTab = '{{ $tabs['tab_name'] }}'">
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
                                    @livewire($tab['component'], ['viewAll' => $viewAll])
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection
