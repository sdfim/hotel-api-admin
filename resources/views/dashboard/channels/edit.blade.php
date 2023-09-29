@extends('layouts.master')
@section('title')
    {{ __('General channels') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $text['edit'] }}' }" x-text="message"></h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2 x-data="{ message: '{{ $text['edit'] }}' }" x-text="message"></h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('channels.index') }}" text="Back" />
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('channels.update', $channel->id) }}" method="POST" x-data="{ inputName: '{{ old('name', $channel->name) }}', submitButtonDisable: false }"
                        @submit="submitButtonDisable = true">
                        @csrf
                        @method('PUT')
                        <div class="col-span-12 lg:col-span-6">
                            <div class="mb-4">
                                <x-label for="name" class="dark:text-gray-100" value="{{ __('Name') }}" />
                                <x-input id="name" name="name" value="{{ $channel->name }}" placeholder="Name"
                                    type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.name" autocomplete="name" />
                                <x-input-error for="name" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-label for="description" class="dark:text-gray-100" value="{{ __('Description') }}" />
                                <x-input id="description" name="description" value="{{ $channel->description }}"
                                    placeholder="Description" type="text"
                                    class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                    wire:model="state.description" autocomplete="description" />
                                <x-input-error for="description" class="mt-2" />
                            </div>
                            <div class="mt-6">
                                <x-button class="ml-4" x-bind:disabled="submitButtonDisable">
                                    {{ __('Update') }}
                                </x-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
