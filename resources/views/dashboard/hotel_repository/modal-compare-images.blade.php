@php
    use Modules\HotelContentRepository\Services\HotelService;

    /** @var HotelService $hotelService */
    $hotelService = app(HotelService::class);
    $hotelImagesData = $hotelService->getHotelImagesData($giataCode);
    $providers = array_keys($hotelImagesData);
@endphp

<div x-data="{ activeTab: 'repo' }" x-init="$nextTick(() => { activeTab = 'repo' })"
     style="height: 550px; overflow-y: auto;">
    <div class="card flex mt-1">
        @foreach($providers as $provider)
            @if (empty($hotelImagesData[$provider]) && $provider !== 'repo')
                @continue
            @endif
            <button
                class="sr_tab-link m-4 px-4 py-2 rounded transition duration-200 ease-in-out"
                type="button"
                @click="activeTab = '{{ $provider }}'"
                :class="activeTab === '{{ $provider }}' ? 'sr_active' : ''"
            >
                {{ ucfirst($provider) }}
            </button>
        @endforeach
    </div>

    @foreach($providers as $provider)
        @if (empty($hotelImagesData[$provider]) && $provider !== 'repo')
            @continue
        @endif
        <div x-show="activeTab === '{{ $provider }}'">
            @component('dashboard.hotel_repository.modal-slider-images', ['items' => $hotelImagesData[$provider]])
            @endcomponent
        </div>
    @endforeach
</div>
