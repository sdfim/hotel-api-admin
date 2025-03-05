@php use Spatie\Activitylog\Models\Activity; @endphp
<div>
    @if ($record->exists)
        <div class="hotel-form-toggle-container">
            <div class="left-block">
                <span class="hotel-form-toggle-label dark:text-white">Verified</span>
                <label class="hotel-form-toggle-button">
                    <input type="checkbox" wire:model="verified" wire:click.prevent="toggleVerified" {{ $verified ? 'checked' : '' }}>
                    <span class="hotel-form-slider"></span>
                </label>
                <span class="hotel-form-toggle-label pl-6 dark:text-white">On Sale</span>
                <label class="hotel-form-toggle-button">
                    <input type="checkbox" wire:model="onSale" wire:click.prevent="toggleOnSale">
                    <span class="hotel-form-slider"></span>
                </label>
                <button class="pd-history-button" wire:click="$set('showInfoModal', true)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 13a9 9 0 1 0 3-7m-3 0v4h4"/>
                        <path d="M12 7v5l4 2"/>
                    </svg>
                </button>
            </div>
            @can('delete', Product::class)
                <div class="right-block" wire:click="confirmDeleteHotel">
                    <label class="delete-label">Delete</label>
                    <svg class="delete-icon" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                    </svg>
                </div>
            @endcan
        </div>
    @endif

    @if($showInfoModal)
        <div class="hotel-modal">
            <div class="hotel-modal-header wm-800">
                <h4>History. Reasons for Changes</h4>
                @php
                    /**  @var Activity $activity */
                    $activity = app(Activity::class);
                    $activityDetails = $activity->where('subject_id', $record->product->id)
                        ->where('subject_type', get_class($record->product))
                        ->whereRaw("JSON_CONTAINS(properties->'$.attributes.onSale', 'false')")
                        ->get();
                @endphp
                <ul>
                    @foreach($activityDetails ?? [] as $activity)
                        <li class="text-left">
                            {{ $activity->created_at->format('m/d/Y H:i') }}
                            {{ $activity->causer->name ?? 'Unknown' }}
                            ({{ $activity->causer->email ?? 'Unknown' }})
                            <em>reason:</em> {{ $activity->properties['attributes']['on_sale_causation'] ?? 'N/A' }}
                        </li>
                    @endforeach
                </ul>
                <button wire:click="$set('showInfoModal', false)" class="close-button">Close</button>
            </div>
        </div>
    @endif

    @if($showDeleteConfirmation)
        <div class="hotel-modal">
            <div class="hotel-modal-header">
                <h4>Confirm Deletion</h4>
                <p>Are you sure you want to delete this hotel?</p>
                <button wire:click="deleteHotel" class="confirm-button">Yes, Delete</button>
                <button wire:click="$set('showDeleteConfirmation', false)">Cancel</button>
            </div>
        </div>
    @endif

    <x-filament::modal id="open-modal-on-sale-causation">
        <x-slot name="heading">
            {{ __('Reason for On Sale Status Change') }}
        </x-slot>
        <div class="col-span-6 sm:col-span-4">
            <textarea id="onSaleCausation"
                      class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                      wire:model="onSaleCausation" required></textarea>
            <x-input-error for="onSaleCausation" class="mt-2"/>
        </div>
        <x-slot name="footerActions">
            <button
                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring focus:ring-primary-300"
                @click="$wire.submitOnSaleForm(); $dispatch('close-modal', { id: 'open-modal-on-sale-causation' });">
                Submit
            </button>
        </x-slot>
    </x-filament::modal>

    <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4" x-show="activeTab.includes('-product-tab')">General
        Information</h3>
    <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4" x-show="activeTab.includes('-location-tab')">Location
        Details</h3>
    <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4" x-show="activeTab.includes('-data-sources-tab')">Data
        Sources</h3>

    <form class="hotel-form-container dark:bg-gray-900" wire:submit="edit" x-show="activeTab.includes('tab')">
        {{ $this->form }}
    </form>

    <x-filament-actions::modals/>
</div>


