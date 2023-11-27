@extends('layouts.master')
@section('title')
    {{ __('Pricing Rule Details') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100 " x-data="{ message: '{{ $text['show'] }}' }"
                    x-text="message"></h6>
            </div>
            <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2 x-data="{ message: '{{ $text['show'] }}' }" x-text="message"></h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('pricing_rules.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>
                        <div class="mt-10 sm:mt-0">
                            <strong>Name:</strong>
                            {{ $pricingRule->name }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Property:</strong>
                            {{ $pricingRule->property }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Destination:</strong>
                            {{ $pricingRule->destination }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Travel Date:</strong>
                            {{ $pricingRule->travel_date }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Days until Travel:</strong>
                            {{ $pricingRule->days }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Nights:</strong>
                            {{ $pricingRule->nights }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Supplier:</strong>
                            {{ $pricingRule->supplier_id}}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Rate Code:</strong>
                            {{ $pricingRule->rate_code }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Room type:</strong>
                            {{ $pricingRule->room_type }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Total Guests:</strong>
                            {{ $pricingRule->total_guests }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Room Guests:</strong>
                            {{ $pricingRule->room_guests }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Number of Rooms:</strong>
                            {{ $pricingRule->number_rooms }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Meal Plan / Board Basis:</strong>
                            {{ $pricingRule->meal_plan }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Rating:</strong>
                            {{ $pricingRule->rating }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Create:</strong>
                            {{ $pricingRule->created_at }}
                        </div>
                        <x-section-border/>
                        <div class="mt-10 sm:mt-0">
                            <strong>Update:</strong>
                            {{ $pricingRule->updated_at }}
                        </div>
                    <x-section-border/>
                </div>

            </div>
        </div>
    </div>
@endsection
