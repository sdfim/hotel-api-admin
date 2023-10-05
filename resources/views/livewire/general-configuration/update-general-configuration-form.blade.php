<div>
    <form wire:submit="save">
        {{ $this->form }}

        <x-button class="mt-4">
            {{ __('Update') }}
        </x-button>
    </form>
    <x-filament-actions::modals />
</div>
