@extends('layouts.master')
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $text['create'] }}' }" x-text="message"></h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2 x-data="{ message: '{{ $text['create'] }}' }" x-text="message"></h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('pricing_rules.index') }}" text="Back" />
                            </div>
                        </div>
                    </div>
                    <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                        @livewire('pricing-rules.create-pricing-rules')
                    </div>
                    <form action="{{ route('pricing_rules.store') }}" method="POST" x-data="{ inputName: '{{ old('name') }}', submitButtonDisable: false }"
                        @submit="submitButtonDisable = true">
                        @csrf
                        <div class="col-span-12 lg:col-span-6">
                            <div class="mb-4">
                                <x-label for="name" class="dark:text-gray-100" value="{{ __('Name') }}" />
                                <x-input id="name" name="name" value="{{ old('name') }}" placeholder="Name"
                                    type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.name" autocomplete="name" />
                                <x-input-error for="name" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="property" class="dark:text-gray-100" value="{{ __('Property') }}" />
                                <x-input id="property" name="property" value="{{ old('property') }}" placeholder="Property"
                                    type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.property" autocomplete="property" />
                                <x-input-error for="property" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="destination" class="dark:text-gray-100" value="{{ __('Destination') }}" />
                                <x-input id="destination" name="destination" value="{{ old('destination') }}"
                                    placeholder="Destination" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.destination" autocomplete="destination" />
                                <x-input-error for="destination" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="travel_date" class="dark:text-gray-100" value="{{ __('Travel Date') }}" />
                                <x-input id="travel_date" name="travel_date"
                                    value="{{ old('travel_date', now()->format('Y-m-d\TH:i')) }}" placeholder="Travel Date"
                                    type="datetime-local"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.travel_date" autocomplete="travel_date" />
                                <x-input-error for="travel_date" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="days" class="dark:text-gray-100" value="{{ __('Days until Travel') }}" />
                                <x-input id="days" name="days" value="{{ old('days') }}"
                                    placeholder="Days until Travel" type="number"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.days" autocomplete="days" />
                                <x-input-error for="days" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="nights" class="dark:text-gray-100" value="{{ __('Nights') }}" />
                                <x-input id="nights" name="nights" value="{{ old('nights') }}" placeholder="Nights"
                                    type="number"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.nights" autocomplete="nights" />
                                <x-input-error for="nights" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <label for="supplier_id" class="dark:text-gray-100">{{ __('Supplier') }}</label>
                                <x-select id="supplier_id" class="block mt-1 w-full" name="supplier_id" :value="old('supplier_id')"
                                    required :options="$suppliers" />
                                @error('supplier_id')
                                    <span class="text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <x-label for="rate_code" class="dark:text-gray-100" value="{{ __('Rate Code') }}" />
                                <x-input id="rate_code" name="rate_code" value="{{ old('rate_code') }}"
                                    placeholder="Rate Code" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.rate_code" autocomplete="rate_code" />
                                <x-input-error for="rate_code" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="room_type" class="dark:text-gray-100" value="{{ __('Room type') }}" />
                                <x-input id="room_type" name="room_type" value="{{ old('room_type') }}"
                                    placeholder="Roome type" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.room_type" autocomplete="room_type" />
                                <x-input-error for="room_type" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="total_guests" class="dark:text-gray-100"
                                    value="{{ __('Total Guests') }}" />
                                <x-input id="total_guests" name="total_guests" value="{{ old('total_guests') }}"
                                    placeholder="Total Guests" type="number"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.total_guests" autocomplete="total_guests" />
                                <x-input-error for="total_guests" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="room_guests" class="dark:text-gray-100" value="{{ __('Room Guests') }}" />
                                <x-input id="room_guests" name="room_guests" value="{{ old('room_guests') }}"
                                    placeholder="Room Guests" type="number"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.room_guests" autocomplete="room_guests" />
                                <x-input-error for="room_guests" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="number_rooms" class="dark:text-gray-100"
                                    value="{{ __('Number of Rooms') }}" />
                                <x-input id="number_rooms" name="number_rooms" value="{{ old('number_rooms') }}"
                                    placeholder="Number of Rooms" type="number"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.number_rooms" autocomplete="number_rooms" />
                                <x-input-error for="number_rooms" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="meal_plan" class="dark:text-gray-100"
                                    value="{{ __('Meal Plan / Board Basis') }}" />
                                <x-input id="meal_plan" name="meal_plan" value="{{ old('meal_plan') }}"
                                    placeholder="Meal Plan / Board Basis" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.meal_plan" autocomplete="meal_plan" />
                                <x-input-error for="meal_plan" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="rating" class="dark:text-gray-100" value="{{ __('Rating') }}" />
                                <x-input id="rating" name="rating" value="{{ old('rating') }}" placeholder="Rating"
                                    type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.rating" autocomplete="rating" />
                                <x-input-error for="rating" class="mt-2" />
                            </div>
                            <div class="mt-6">
                                <x-button class="ml-4" x-bind:disabled="submitButtonDisable">
                                    {{ __('Create') }}
                                </x-button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
