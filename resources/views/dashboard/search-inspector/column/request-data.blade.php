@if($getRecord()->request)
    @php
        $fields = json_decode($getRecord()->request, true);
		$arr = Arr::dot($fields);
		$str = '';
		$rooms = '';
		foreach ($arr as $key => $value) {
			if ($key === 'rating') $rating = $value . ' &#9733; ';
			if ($key === 'destination') $str .=  $value .  ' &#x1F3D9; | ' . $rating . " | ";		
			if ($key === 'checkin') $str .= $value . " - ";
			if ($key === 'checkout') $str .= $value . "<br>";
			if (str_contains($key, 'occupancy')) $rooms .=  'a' . $value . "; ";
			if (str_contains($key, 'children')) $rooms .=  'ch' . $value . ", ";
		}
		$str .= "rooms: " . trim($rooms, ', ') . "<br>";

    @endphp
    
@endif
{!! $str !!}