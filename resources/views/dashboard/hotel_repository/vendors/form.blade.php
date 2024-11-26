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

    @php
        $tabs = [
            'Vendors Detail' => route('vendor-repository.index'),
            'Hotels' => route('hotel-repository.index'),
        ];
    @endphp
    <div x-data="{ activeTab: 'Vendors Detail' }" class="sr_tab-container mb-8">
        <ul class="sr_tab-list flex">
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

    <div class="col-span-12 xl:col-span-6">
        <div class="card-body pb-0">
            <h4 class="mb-1 text-gray-700 dark:text-gray-100">General Information</h4>
        </div>

        <div class="hotel-form-container dark:bg-zinc-800 dark:border-zinc-600">

            <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                <div class="relative overflow-x-auto">
                    <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                        @livewire('vendors.vendor-form', compact('vendor'))
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
