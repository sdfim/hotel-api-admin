@extends('channels.layout')
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100">Add New Channel</h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2>Add New Channel</h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('pricing-rules.index') }}" text="Back"
                                    style="additional-styles" />
                            </div>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong>
                            <p>There were some problems with your input.</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('pricing-rules.store') }}" method="POST">
                        @csrf
                        <div class="col-span-12 lg:col-span-6">
                            <div class="mb-4">
                                <x-label for="name" class="dark:text-gray-100" value="{{ __('Name') }}" />
                                <x-input id="name" name="name" placeholder="Name" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.name" required autocomplete="name" />
                                <x-input-error for="name" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="property" class="dark:text-gray-100" value="{{ __('Property') }}" />
                                <x-input id="property" name="property" placeholder="Property" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.property" required autocomplete="property" />
                                <x-input-error for="property" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="destination" class="dark:text-gray-100" value="{{ __('Destination') }}" />
                                <x-input id="destination" name="destination" placeholder="Destination" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.destination" required autocomplete="destination" />
                                <x-input-error for="destination" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="travelDate" class="dark:text-gray-100" value="{{ __('Travel Date') }}" />
                                <x-input id="travelDate" name="travelDate" placeholder="Travel Date" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.travelDate" required autocomplete="travelDate" />
                                <x-input-error for="travelDate" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="days" class="dark:text-gray-100" value="{{ __('Days until Travel') }}" />
                                <x-input id="days" name="days" placeholder="Days until Travel" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.days" required autocomplete="days" />
                                <x-input-error for="days" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="nights" class="dark:text-gray-100" value="{{ __('Nights') }}" />
                                <x-input id="nights" name="nights" placeholder="Nights" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.nights" required autocomplete="nights" />
                                <x-input-error for="nights" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="supplierId" class="dark:text-gray-100" value="{{ __('Supplier') }}" />
                                <x-input id="supplierId" name="supplierId" placeholder="Supplier" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.supplierId" required autocomplete="supplierId" />
                                <x-input-error for="supplierId" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="supplierId" class="dark:text-gray-100" value="{{ __('Rate Code') }}" />
                                <x-input id="supplierId" name="supplierId" placeholder="Rate Code" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.supplierId" required autocomplete="supplierId" />
                                <x-input-error for="supplierId" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="roomType" class="dark:text-gray-100" value="{{ __('Room type') }}" />
                                <x-input id="roomType" name="roomType" placeholder="Roome type" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.roomType" required autocomplete="roomType" />
                                <x-input-error for="roomType" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="totalGuests" class="dark:text-gray-100"
                                    value="{{ __('Total Guests') }}" />
                                <x-input id="totalGuests" name="totalGuests" placeholder="Total Guests" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.totalGuests" required autocomplete="totalGuests" />
                                <x-input-error for="totalGuests" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="roomGuests" class="dark:text-gray-100" value="{{ __('Room Guests') }}" />
                                <x-input id="roomGuests" name="roomGuests" placeholder="Roome Guests" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.roomGuests" required autocomplete="roomGuests" />
                                <x-input-error for="roomGuests" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="numberRooms" class="dark:text-gray-100"
                                    value="{{ __('Number of Rooms') }}" />
                                <x-input id="numberRooms" name="numberRooms" placeholder="Number of Rooms"
                                    type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.numberRooms" required autocomplete="numberRooms" />
                                <x-input-error for="numberRooms" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="mealPlan" class="dark:text-gray-100"
                                    value="{{ __('Meal Plan / Board Basis') }}" />
                                <x-input id="mealPlan" name="mealPlan" placeholder="Meal Plan / Board Basis"
                                    type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.mealPlan" required autocomplete="mealPlan" />
                                <x-input-error for="mealPlan" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="rating" class="dark:text-gray-100" value="{{ __('Rating') }}" />
                                <x-input id="rating" name="rating" placeholder="Rating" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.rating" required autocomplete="rating" />
                                <x-input-error for="rating" class="mt-2" />
                            </div>

                            <div class="mt-6">
                                <x-button>
                                    Submit
                                </x-button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
