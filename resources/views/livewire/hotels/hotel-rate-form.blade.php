<div class="card p-7 mb-10 bg-white dark:bg-gray-800">
    <form wire:submit="edit">
        {{ $this->form }}

        <x-button-save class="mt-4">
            @if ($record->exists)
                {{ __('Update') }}
            @else
                {{ __('Create') }}
            @endif
        </x-button-save>
    </form>

    <x-filament-actions::modals/>
</div>
