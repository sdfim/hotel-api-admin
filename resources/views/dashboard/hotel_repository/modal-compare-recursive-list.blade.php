@foreach ($data as $key => $value)
    @php
        $backgroundClass = $level % 2 === 0 ? 'bg-gray-50/50' : 'bg-white';
    @endphp

    @if (is_array($value) && count($value) > 0)
        <div x-data="{ open: true }" class="border border-gray-300 rounded-lg p-2 my-2 {{ $backgroundClass }}">
            @if (!is_numeric($key))
                <h4 @click="open = !open" class="text-sm text-gray-800 mb-2 cursor-pointer">
                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                </h4>
            @else
                <h4 @click="open = !open" class="text-sm text-gray-800 mb-2 cursor-pointer">
                    {{ ucfirst($section) }} {{ $key+1 }}
                </h4>
            @endif
                <div x-show="open">
                    @if ($key === 'images')
                        @component('dashboard.hotel_repository.modal-slider-images', ['items' => $value])
                        @endcomponent
                    @else
                        @include('dashboard.hotel_repository.modal-compare-recursive-list', [
                            'data' => $value,
                            'level' => $level + 1,
                            'section' => $key
                        ])
                    @endif
                </div>
        </div>
    @elseif (!empty($value))
        <div class="p-2 text-gray-600 {{ $backgroundClass }}">
        @if (!is_numeric($key))
            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
        @endif
            {!! $value->value ?? $value !!}
    </div>
    @endif
@endforeach
