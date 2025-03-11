<div>
    <div class="hotel-form-toggle-container">
        <div class="left-block">
            <span class="hotel-form-toggle-label">Activated</span>
            <label class="hotel-form-toggle-button">
                <input type="checkbox" wire:model="verified"
                       wire:click.prevent="toggleActivated" {{ $verified ? 'checked' : '' }}>
                <span class="hotel-form-slider"></span>
            </label>
        </div>
        <div class="right-block">
            <span>Activity Log Info</span>
            <button class="pd-history-button" wire:click="$set('showModalLogInfoVendor', true)">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 13a9 9 0 1 0 3-7m-3 0v4h4"/>
                    <path d="M12 7v5l4 2"/>
                </svg>
            </button>
            <div class="flex pl-12" wire:click="confirmDeleteVendor">
                <label class="delete-label">Delete</label>
                <svg class="delete-icon" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor"
                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                </svg>
            </div>
        </div>
        @if($showDeleteConfirmation)
            <div class="hotel-modal">
                <div class="hotel-modal-header">
                    <h4>Confirm Deletion</h4>
                    <p>Are you sure you want to delete this vendor?</p>
                    <button wire:click="deleteVendor" class="confirm-button">Yes, Delete</button>
                    <button wire:click="$set('showDeleteConfirmation', false)">Cancel</button>
                </div>
            </div>
        @endif
    </div>

    <form class="hotel-form-container" wire:submit="save">
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

    @if($showModalLogInfoVendor)
        <div class="hotel-modal" style="overflow-y: auto; max-height: 100vh;">
            <div class="hotel-modal-header-simple wm-1400">
                <div class="flex justify-between  pb-6">
                    <h4>Activity Log Info for Vendor: {{ $record->name }}</h4>
                    <button wire:click="$set('showModalLogInfoVendor', false)" class="close-button">×</button>
                </div>
                @livewire('activity.activity-table', ['id' => $record->id, 'level' => 'Vendor'])
            </div>
        </div>
    @endif

</div>
