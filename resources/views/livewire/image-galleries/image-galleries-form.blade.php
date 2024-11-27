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

    <div class="pt-16">
        {{ $this->table }}
    </div>

    <x-filament-actions::modals/>
</div>
