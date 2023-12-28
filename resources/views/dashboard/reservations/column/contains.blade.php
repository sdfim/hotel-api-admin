@php
    $record = json_decode($getRecord()->reservation_contains, true);

	$search_id = $record['search_id'];
	$content = '<span  class="text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">booking_item: <a href=' . route('booking-items.show', $record['booking_item'] ) .' target="_blank" style="color: #007bff;">' . $record['booking_item']. "</a></span><br>";
	$content .= '<span  class="text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">booking_id:<b> ' . $record['booking_id'] . "</b></span><br>";
	$content .=  '<span  class="text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">search_id: <a href=' . route('search-inspector.show', $search_id ) .' target="_blank" style="color: #007bff;">' . $search_id . '</a></span>';

	unset($record['search_id']);
	unset($record['booking_item']);
	unset($record['booking_id']);

	$array = [];
    if (is_array($record)) {
        foreach ($record as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $key => $item) {
					if ($key === 'booking_item') continue;
					$array[$key] = $item;
				}
			} else if ($key !== 'hotel_images') $array[$key] = $value;
		}
    }

	$orderTooltip = ['type', 'supplier', 'total_net', 'total_tax', 'total_fees', 'total_price', 'affiliate_service_charge'];
	$orderedArray = array_replace(array_flip($orderTooltip), $array);
	$tooltipCol1 = '<div class="grid grid-cols-2"><div>';
	$tooltipCol2 = '</div><div>';
	$addDiv = true;
	foreach ($orderedArray as $key => $value) {
		if (in_array($key, $orderTooltip)) {
			$tooltipCol1 .= '<span style="word-wrap: break-word">' . $key . ': <b>' . $value . '</b></span><br>';
		} else {
			$tooltipCol2 .= '<span style="word-wrap: break-word">' . $key . ': <b>' . $value . '</b></span><br>';
		}
	}
	$tooltip = $tooltipCol1 . $tooltipCol2 . '</div></div>';

@endphp

<div x-data="{ tooltip: false }" style="padding: 5px;">
    <p @mouseover="tooltip = true" @mouseout="tooltip = false" class="text-right" style="line-height: 1.5;">
        <span class="hover:bg-gray-80/80">{!! $content !!}</span>
    </p>

    <div x-show="tooltip" x-cloak
         class="absolute z-50 p-2 bg-gray-800 text-white rounded shadow"
         style="top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70%; font-size: 120%; padding: 2rem;"
    >
        {!! $tooltip !!}
    </div>
</div>

