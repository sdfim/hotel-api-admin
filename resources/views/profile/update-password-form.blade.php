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
