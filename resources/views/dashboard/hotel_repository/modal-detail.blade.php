@php
    use Modules\HotelContentRepository\Services\HotelService;

    /** @var HotelService $hotelService */
    $hotelService = app(HotelService::class);
    $detailResponse = $hotelService->getDetailRespose($giataCode);
@endphp

@foreach ($detailResponse as $section => $items)
    <div x-data="{ open: true }" class="p-3 bg-gray-50/50">
        @if (is_array($items))
            <div @click="open = !open" class="cursor-pointer flex items-center">
                <span x-text="open ? '▼' : '▶'" class="mr-2"></span>
                <h3 class="text-base font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $section)) }}</h3>
            </div>
            <div x-show="open" class="text-base">
                @if ($section === 'images' && is_array($items))
                    @component('dashboard.hotel_repository.modal-slider-images', ['items' => $items])
                    @endcomponent
                @else
                    <ul class="list-disc list-inside text-base text-gray-600">
                        @include('dashboard.hotel_repository.modal-compare-recursive-list', ['data' => $items, 'level' => 1, 'section' => $section])
                    </ul>
                @endif
            </div>
        @else
            <div class="inline-flex items-center space-x-2">
                <h3 class="text-base font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $section)) }}</h3>
                <p class="text-base text-gray-600">{!! $items !!}</p>
            </div>
        @endif
    </div>
@endforeach

<div x-data="{ open: false }" class="mb-1 p-3 bg-gray-50/50">
    <div @click="open = !open" class="cursor-pointer flex items-center">
        <span x-text="open ? '▼' : '▶'" class="mr-2"></span>
        <h3 class="text-base font-medium text-gray-700">JSON Data</h3>
    </div>
    <div x-show="open" class="text-base">
        <pre style="white-space: pre-wrap;">
            {{ json_encode($detailResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
        </pre>
    </div>
</div>
