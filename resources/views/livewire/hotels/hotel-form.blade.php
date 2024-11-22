<div>
    <div class="hotel-form-toggle-container">
        <span class="hotel-form-toggle-label">Verified</span>
        <label class="hotel-form-toggle-button">
            <input type="checkbox" wire:click="toggleVerified" {{ $verified ? 'checked' : '' }}>
            <span class="hotel-form-slider"></span>
        </label>
    </div>

    <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4" x-show="activeTab.includes('-product-tab')">General Information</h3>
    <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4" x-show="activeTab.includes('-location-tab')">Location Details</h3>
    <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4" x-show="activeTab.includes('-data-sources-tab')">Data Sources</h3>

    <form class="hotel-form-container" wire:submit="edit" x-show="activeTab.includes('tab')">
        {{ $this->form }}

        <x-button class="mt-4">
            {{ __('Update') }}
        </x-button>
    </form>

    <x-filament-actions::modals/>
</div>
