
@php
    $record = json_decode($getRecord()->reservation_contains, true);

	$search_id = $record['search_id'];
    $content =  'search_id: <a href=' . route('search-inspector.show', $search_id ) .' target="_blank" style="color: #007bff;">' . $search_id . '</a><br>';
	$content .= 'booking_item:<b> ' . $record['booking_item']. "</b><br>";
	$content .= 'booking_id:<b> ' . $record['booking_id'] . "</b>";

	unset($record['search_id']);
	unset($record['booking_item']);
	unset($record['booking_id']);

	$tooltip = '';
    if (is_array($record)) {
        foreach ($record as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $key => $item) {
					$tooltip .= "$key: $item <br> ";
				}			
			} else if ($key !== 'hotel_images') $tooltip .= "$key: $value <br> ";
        }
    }
	
@endphp

<div x-data="{ tooltip: false }" style="padding: 5px;">
    <p @mouseover="tooltip = true" @mouseout="tooltip = false" class="text-right" style="line-height: 2;">
        <span class="hover:bg-gray-80/80">{!! $content !!}</span>
    </p>

    <div x-show="tooltip" x-cloak
         class="absolute z-50 p-2 bg-gray-800 text-white rounded shadow"
         style="top: 50%; left: 50%; transform: translate(-50%, -50%); width: 60%; font-size: 120%; padding: 2rem;"
    >
        {!! $tooltip !!}
    </div>
</div>

