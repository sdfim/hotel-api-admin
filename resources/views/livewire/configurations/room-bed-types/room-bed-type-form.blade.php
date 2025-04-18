<div>
    <form wire:submit="edit">
        {{ $this->form }}

        <x-button class="mt-4">
            @if ($record->exists)
                {{ __('Update') }}
            @else
                {{ __('Create') }}
            @endif
        </x-button>
    </form>

    <x-filament-actions::modals/>
</div>
