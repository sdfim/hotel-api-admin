@extends('channels.layout')
@section('content')

<x-form-section submit="updatePassword">
    <x-slot name="title">
        {{ __('Update Password') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Ensure your account is using a long, random password to stay secure.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="current_password" class="dark:text-gray-100" value="{{ __('Current Password') }}" />
            <x-input id="current_password" type="password" class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100" wire:model="state.current_password" autocomplete="current-password" placeholder="Enter your current password" />
            <x-input-error for="current_password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password" class="dark:text-gray-100" value="{{ __('New Password') }}" />
            <x-input id="password" type="password" class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100" wire:model="state.password" autocomplete="new-password" placeholder="Enter new password" />
            <x-input-error for="password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password_confirmation" class="dark:text-gray-100" value="{{ __('Confirm Password') }}" />
            <x-input id="password_confirmation" type="password" class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100" wire:model="state.password_confirmation" autocomplete="new-password" placeholder="Enter confirm password" />
            <x-input-error for="password_confirmation" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button class="dark:bg-gray-600">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>

    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100">Edit Channel</h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2>Edit Channel</h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('channels.index') }}" text="Back" style="additional-styles" />
                            </div>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="relative px-5 py-3 border-2 bg-red-50 text-red-700 border-red-100 rounded">
                            <p> <strong>Whoops!</strong>There were some problems with your input.</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('channels.update', $channel->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="col-span-12 lg:col-span-6">
                            <div class="mb-4">
                                <label for="example-text-input"
                                    class="block font-medium text-gray-700 dark:text-gray-100 mb-2">Name</label>
                                <input
                                    class="w-full rounded border-gray-100 placeholder:text-sm focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:placeholder:text-zinc-100 dark:text-zinc-100"
                                    type="text" name="name" value="{{ $channel->name }}" placeholder="Name"
                                    id="example-text-input">
                            </div>
                            <div class="mb-4">
                                <label for="example-text-input"
                                    class="block font-medium text-gray-700 dark:text-gray-100 mb-2">Description:</label>
                                <input
                                    class="w-full rounded border-gray-100 placeholder:text-sm focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:placeholder:text-zinc-100 dark:text-zinc-100"
                                    type="text" name="description" value="{{ $channel->description }}"
                                    placeholder="Description" id="example-search-input">
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
    </div>
@endsection
