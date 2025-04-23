@php
    $offColor = $getOffColor() ?? 'red';
    $onColor = $getOnColor() ?? 'lime';
    $handleOffColor = $getOffColor() ?? 'white';
    $handleOnColor = $getOnColor() ?? 'white';
    $statePath = $getStatePath();

    $handleOffColor = '#ee6a64';
    $handleOnColor = '#87e291';

    $getBackgroundClasses = function($color) {
        return match ($color) {
            'gray' => 'bg-gray-200 dark:bg-gray-700',
            default => "bg-{$color}-100 dark:bg-{$color}-700",
        };
    };
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
    @capture($content)
    <button
        x-data="{
                state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            }"
        x-bind:aria-checked="state?.toString()"
        x-on:click="state = ! state"
        x-bind:class="[
            state
                ? '{{ $getBackgroundClasses($onColor) }}'
                : '{{ $getBackgroundClasses($offColor) }}'
        ]"
        {{
            $attributes
                ->merge([
                    'aria-checked' => 'false',
                    'autofocus' => $isAutofocused(),
                    'disabled' => $isDisabled(),
                    'id' => $getId(),
                    'role' => 'switch',
                    'type' => 'button',
                    'wire:loading.attr' => 'disabled',
                    'wire:target' => $statePath,
                ], escape: false)
                ->merge($getExtraAttributes(), escape: false)
                ->merge($getExtraAlpineAttributes(), escape: false)
                ->class(['fi-fo-toggle relative inline-flex h-8 w-15 shrink-0 cursor-pointer rounded-full outline-none transition-colors duration-200 ease-in-out disabled:pointer-events-none disabled:opacity-70'])        }}
    >
            <span
                class="pointer-events-none relative inline-block mt-1 ml-1 h-6 w-6 transform rounded-full shadow ring-0 transition duration-200 ease-in-out"
                x-bind:class="{
                    'translate-x-7 rtl:-translate-x-7': state,
                    'translate-x-0': ! state,
                }"
                x-bind:style="{
                    'background-color': state ? '{{ $handleOnColor }}' : '{{ $handleOffColor }}'
                }"
            >
            </span>
    </button>
    @endcapture

    @if ($isInline())
        <x-slot name="labelPrefix">
            {{ $content() }}
        </x-slot>
    @else
        {{ $content() }}
    @endif
</x-dynamic-component>
