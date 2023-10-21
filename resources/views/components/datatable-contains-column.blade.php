@php
	$field = json_decode($getState(), true);
	$tooltip = '';
	$content = '';
	if (is_array($field)) {
		foreach ($field as $key => $value) {
			$tooltip .= $key . ': ' . $value .' <br>';
			if ($key == 'booking_id') {
				$content .= $key . ': ' . $value .' <br>';
			}
		}
	}
@endphp

<div x-data="{ show: false }">
    <span @mouseover="show = true" @mouseout="show = false">{!! $content !!}</span>
    <div x-show="show" class="absolute bg-gray-800 text-white px-2 py-1 rounded-lg mt-2">
        {!! $tooltip !!}
    </div>
</div>