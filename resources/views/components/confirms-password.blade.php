@props(['title' => __('Confirm Password'), 'content' => __('For your security, please confirm your password to continue.'), 'button' => __('Confirm')])

@php
    $confirmableId = md5($attributes->wire('then'));
@endphp

<span
    {{ $attributes->wire('then') }}
    x-data
    x-ref="span"
    x-on:click="$wire.startConfirmingPassword('{{ $confirmableId }}')"
    x-on:password-confirmed.window="setTimeout(() => $event.detail.id === '{{ $confirmableId }}' && $refs.span.dispatchEvent(new CustomEvent('then', { bubbles: false })), 250);"
>
    {{ $slot }}
</span>

@once
    <x-dialog-modal wire:model.live="confirmingPassword">
        <x-slot name="title">
            {{ $title }}
        </x-slot>

        <x-slot name="content">
            {{ $content }}

            <div class="mt-4" x-data="{}"
                 x-on:confirming-password.window="setTimeout(() => $refs.confirmable_password.focus(), 250)">
                <x-input type="password" name="confirmable_password"
                         class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                         placeholder="{{ __('Password') }}" autocomplete="current-password"
                         x-ref="confirmable_password"
                         wire:model.defer="confirmable_password"
                         autofocus/>

                <x-input-error for="confirmable_password" class="mt-2"/>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="stopConfirmingPassword" wire:loading.attr="disabled"
                                class="dark:bg-gray-600 dark:text-gray-100 dark:border-transparent">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-button class="ml-3" dusk="confirm-password-button" wire:click="confirmPassword"
                      wire:loading.attr="disabled">
                {{ $button }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
@endonce
