@extends('layouts.master')
@section('title')
    {{ __('Products') }}
@endsection
@section('content')
    <h2 class="font-semibold">Products</h2>
    {{-- <x-page-title title="Products" pagetitle="index" />--}}
    @php
        $tabs = [
            'Hotels' => route('hotel-repository.index'),
        ];
    @endphp
    <div x-data="{ activeTab: 'Hotels' }" class="sr_tab-container mb-8">
        <ul class="sr_tab-list flex">
            @foreach ($tabs as $tab => $link)
                <li class="sr_tab-item mr-1 flex items-end">
                    <a href="{{ $link }}" class="sr_tab-link">
                        <span>{{ $tab }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="col-span-12">
        <div class="relative overflow-x-auto text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
            @livewire('products.product-table')
        </div>
    </div>

@endsection