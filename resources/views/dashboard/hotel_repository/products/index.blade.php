@extends('layouts.master')
@section('title')
    {{ __('Products') }}
@endsection
@section('content')
    <div class="breadcrumb-container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                {{--                        <li class="breadcrumb-item"><a href="{{ route('supplier-repository.index') }}">Supplier Repository</a></li>--}}
{{--                <li class="breadcrumb-item"><a href="{{ route('vendor-repository.index') }}">Vendors</a></li>--}}
                <li class="breadcrumb-item"><a href="{{ route('product-repository.index') }}">Products</a></li>
            </ol>
        </nav>
    </div>
    <h2 class="font-semibold">Products</h2>
{{--    <x-page-title title="Products" pagetitle="index"/>--}}
    @php
        $tabs = [
            'Hotels' => route('hotel-repository.index'),
            'Tours' => '#',
            'Transfers' => '#',
        ];
    @endphp
    <div x-data="{ activeTab: 'Hotels' }" class="sr_tab-container mb-8">
        <ul class="sr_tab-list flex justify-center">
            @foreach ($tabs as $tab => $link)
                <li class="sr_tab-item mr-1 flex items-end">
                    <a href="{{ $link }}"
                       class="sr_tab-link">
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
