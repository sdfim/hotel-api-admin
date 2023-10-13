<div>
    <form wire:submit="create">
        {{ $this->form }}

        <x-button class="mt-4">
            {{ __('Create') }}
        </x-button>
    </form>

    <x-filament-actions::modals/>
</div>
