@php
    use Modules\HotelContentRepository\Services\HotelService;

    /** @var HotelService $hotelService */
    $hotelService = app(HotelService::class);
    $hotelData = $hotelService->getHotelData($giataCode);
    $providers = array_keys($hotelData); // Dynamically get provider keys
@endphp

<div x-data="{ activeTab: '{{ $providers[0] }}' }" x-init="$nextTick(() => { activeTab = '{{ $providers[0] }}' })"
     style="height: 85vh; overflow-y: auto;">
    <div class="card flex mt-1">
        @foreach($providers as $provider)
            @if (empty($hotelData[$provider]) && $provider !== 'repo')
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

    @foreach ($providers as $provider)
        @if (empty($hotelData[$provider]) && $provider !== 'repo')
            @continue
        @endif
        <div x-show="activeTab === '{{ $provider }}'" class="p-4 border rounded-lg shadow-sm bg-white">
            @foreach ($hotelData[$provider] as $section => $items)
                <div x-data="{ open: true }" class="mt-2 p-3 bg-gray-50/50">
                    @if (is_array($items))
                        <div @click="open = !open" class="cursor-pointer flex items-center">
                            <span x-text="open ? '▼' : '▶'" class="mr-2"></span>
                            <h3 class="text-base font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $section)) }}</h3>
                        </div>
                        <div x-show="open" x-transition class="mt-2">
                            <ul class="list-disc list-inside mt-1 text-gray-600">
                                @include('dashboard.hotel_repository.modal-compare-recursive-list', ['data' => $items, 'level' => 1, 'section' => $section])
                            </ul>
                        </div>
                    @else
                        <div class="inline-flex items-center space-x-2">
                            <h3 class="text-base font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $section)) }}</h3>
                            <p class="text-base text-gray-600">{!! $items !!}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach

    @foreach($providers as $provider)
        @if (empty($hotelData[$provider]) && $provider !== 'repo')
            @continue
        @endif
        <div x-data="{ open: false }" x-show="activeTab === '{{ $provider }}'" class="mb-3 p-3 bg-gray-50/50">
            <div @click="open = !open" class="cursor-pointer flex items-center">
                <span x-text="open ? '▼' : '▶'" class="mr-2"></span>
                <h3 class="text-base font-medium text-gray-700">JSON Data</h3>
            </div>
            <div x-show="open" class="mt-2">
                <pre style="white-space: pre-wrap;">
                    {{ json_encode($hotelData[$provider], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                </pre>
            </div>
        </div>
    @endforeach
</div>
