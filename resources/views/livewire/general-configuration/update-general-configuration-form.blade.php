<div>
    <form wire:submit="save">
        {{ $this->form }}

        <x-button class="mt-4">
            {{ __('Update') }}
        </x-button>
    </form>
{{-- @if ($create)        
            <x-button class="mt-4">
                {{ __('Update') }}
            </x-button>
        @else{
            <x-button class="mt-4">
                {{ __('Create') }}
            </x-button>
            }
        @endif --}}
    <x-filament-actions::modals />
</div>
