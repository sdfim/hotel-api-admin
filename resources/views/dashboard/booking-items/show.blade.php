@extends('layouts.master')
@section('title')
    {{ __('Booking Item') }}
@endsection
@section('content')
    <style>
        json-viewer {
            padding: 10px;
            margin-top: 10px;
            font-size: 0.8em;
            word-wrap: break-word;
        }
    </style>
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0 flex">
                <x-button-back route="{{ redirect()->getUrlGenerator()->previous() }}" text="Back"/>
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100 ml-4">Booking item</h6>
            </div>
            <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="mt-2">
                                <strong>Booking Item:</strong>
                                {{ $item->booking_item }}
                            </div>
                            <div class="mt-2">
                                <strong>Search ID:</strong>
                                {{ $item->search_id }}
                            </div>
                            <div class="mt-2">
                                <strong>Supplier:</strong>
                                {{ $item->supplier->name }}
                            </div>
                            <div class="mt-2">
                                <strong>Booking item data:</strong>
                                <pre>{{json_encode(json_decode($item->booking_item_data), JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            <div class="mt-2">
                                <strong>Booking pricing data:</strong>
                                <pre>{{json_encode(json_decode($item->booking_pricing_data), JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>

                    <x-section-border/>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ URL::asset('build/js/json-viewer.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/nav&tabs.js') }}"></script>

@endsection
