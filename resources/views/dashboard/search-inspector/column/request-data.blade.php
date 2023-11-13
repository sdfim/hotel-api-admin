@if($getRecord()->request)
    @php
        $fields = json_decode($getRecord()->request, true);
		$rooms = [];
		foreach ($fields as $key => $item) {
			if ($key == 'occupancy') {
				foreach ($item as $key => $value) {
					if (isset($value['children_ages'])) $rooms[] = $value['adults'] . '-' . implode(',',$value['children_ages']);
					else $rooms[] = $value['adults'];
				}
			}
		}
		$arr = Arr::dot($fields);
		$str = '';
		$destinationIcon = '<i class="fa-solid fa-house" style="color: #4466f0;"></i>';
		$ratingIcon = '<i class="fa-solid fa-star"></i>';
		$rating = '';
		foreach ($arr as $key => $value) {
			if ($key === 'rating') {
				foreach (range(1, $value) as $item) {
					$rating .= $ratingIcon;
				}
			}
			if ($key === 'destination') $str1 =  $destinationIcon . ' ' . $value  . ' <span style="color: #FFD700;"> ' . $rating . '</span>';		
			if ($key === 'checkin') $str0 = $value . " - ";
			if ($key === 'checkout') $str0 .= $value;
		}
		$str = '<div><p>' . $str0 . ' ' . $str1 . '</p><p>rooms: ' . json_encode($rooms) . '</p></div>';
    @endphp
    
@endif
{!! $str !!}