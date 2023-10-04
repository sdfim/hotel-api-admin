@extends('layouts.master')
@section('title')
    {{ __('Weight') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $text['edit'] }}' }"
                    x-text="message"></h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2 x-data="{ message: '{{ $text['edit'] }}' }" x-text="message"></h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('weight.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('weight.update', $weight->id) }}" method="POST"
                          x-data="{ inputProperty: '{{ old('property', $weigh->name) }}', submitButtonDisable: false }"
                          @submit="submitButtonDisable = true">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-label for="property" class="dark:text-gray-100" value="{{ __('Property') }}"/>
                            <x-input id="property" name="property" value="{{ old('property', $weight->property) }}"
                                     placeholder="Property" type="text"
                                     class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                     wire:model="state.property" autocomplete="property"/>
                            <x-input-error for="property" class="mt-2"/>
                        </div>
                        <div class="mb-4">
                            <?php
                            //dd($suppliers);
                            ?>
                            <x-label for="supplier" class="dark:text-gray-100" value="{{ __('Supplier') }}"/>
                            <x-select id="supplier" class="block mt-1 w-full" name="supplier_id"
                                      :value="old('supplier_id', $weight->supplier_id)"
                                      :options="$array_suppliers" autofocus/>
                            <x-input-error for="supplier" class="mt-2"/>
                        </div>
                        <div class="mb-4">
                            <x-label for="weight" class="dark:text-gray-100" value="{{ __('Weight') }}"/>
                            <x-input id="weight" name="weight" value="{{ old('weight', $weight->weight) }}"
                                     placeholder="Weight" type="text"
                                     class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                     wire:model="state.weight" autocomplete="weight"/>
                            <x-input-error for="weight" class="mt-2"/>
                        </div>

                        <div class="mt-6">
                            <x-button class="ml-4" x-bind:disabled="submitButtonDisable">
                                {{ __('Update') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection
