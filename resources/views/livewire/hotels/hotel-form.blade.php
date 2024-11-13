<div>
    <div class="hotel-form-toggle-container">
        <span class="hotel-form-toggle-label">Verified</span>
        <label class="hotel-form-toggle-button">
            <input type="checkbox" wire:click="toggleVerified" {{ $verified ? 'checked' : '' }}>
            <span class="hotel-form-slider"></span>
        </label>
    </div>

    <form wire:submit="edit">
        {{ $this->form }}

        <x-button class="mt-4">
            {{ __('Update') }}
        </x-button>
    </form>

    <x-filament-actions::modals/>
</div>
