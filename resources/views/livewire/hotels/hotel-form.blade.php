<div>
    <div class="hotel-form-toggle-container">
        <div class="left-block">
            <span class="hotel-form-toggle-label">Verified</span>
            <label class="hotel-form-toggle-button">
                <input type="checkbox" wire:click="toggleVerified" {{ $verified ? 'checked' : '' }}>
                <span class="hotel-form-slider"></span>
            </label>
        </div>
        <div class="right-block" wire:click="confirmDeleteHotel">
            <label class="delete-label">Delete</label>
            <svg class="delete-icon" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
            </svg>
        </div>
    </div>

    @if($showDeleteConfirmation)
        <div class="hotel-modal">
            <div class="hotel-modal-header">
                <h4>Confirm Deletion</h4>
                <p>Are you sure you want to delete this hotel?</p>
                <button wire:click="deleteHotel">Yes, Delete</button>
                <button wire:click="$set('showDeleteConfirmation', false)">Cancel</button>
            </div>
        </div>
    @endif

    <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4" x-show="activeTab.includes('-product-tab')">General Information</h3>
    <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4" x-show="activeTab.includes('-location-tab')">Location Details</h3>
    <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4" x-show="activeTab.includes('-data-sources-tab')">Data Sources</h3>

    <form class="hotel-form-container" wire:submit="edit" x-show="activeTab.includes('tab')">
        {{ $this->form }}
    </form>

    <x-filament-actions::modals/>
</div>


