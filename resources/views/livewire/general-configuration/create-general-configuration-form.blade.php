<div>
    <form wire:submit="save">
        {{ $this->form }}

        @if ($create)
            <x-button class="mt-4">
                {{ __('Update') }}
            </x-button>
        @else{
            <x-button class="mt-4">
                {{ __('Create') }}
            </x-button>
            }
        @endif
    </form>

    <x-filament-actions::modals />
</div>
