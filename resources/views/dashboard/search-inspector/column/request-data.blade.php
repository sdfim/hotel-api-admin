@if($getRecord()->request)
    @php
        $str = '';

        if ($getRecord()->search_type === 'hotel') {
            $fields = json_decode($getRecord()->request, true);
            $rooms = [];

            foreach ($fields as $key => $item) {
                if ($key == 'occupancy') {
                    foreach ($item as $occupancy) {
                        $roomDetails = [];
                        if (isset($occupancy['adults'])) {
                            $roomDetails[] = 'Adults: ' . $occupancy['adults'];
                        }
                        if (!empty($occupancy['room_code'])) {
                            $roomDetails[] = 'Room: ' . $occupancy['room_code'];
                        }
                        if (!empty($occupancy['rate_code'])) {
                            $roomDetails[] = 'Rate: ' . $occupancy['rate_code'];
                        }
                        $rooms[] = implode(', ', $roomDetails);
                    }
                }
            }

            $arr = Arr::dot($fields);

            $destinationIcon = '<i class="fa-solid fa-house" style="color: #4466f0;"></i>';
            $ratingIcon = '<i class="fa-solid fa-star"></i>';
            $rating = '';
            $str0 = '';

            foreach ($arr as $key => $value) {
                if ($key === 'rating') {
                    foreach (range(0, $value) as $item) {
                        $rating .= $ratingIcon;
                    }
                }
                $str0 = '';
                $str1 = '';

                if ($key === 'destination') {
                    $str1 = $destinationIcon . ' ' . $value . ' <span style="color: #FFD700;"> ' . $rating . '</span>';
                } elseif ($key === 'place') {
                    $str1 = $destinationIcon . ' ' . $value;
                }
                if ($key === 'checkin') {
                    $str0 = $value . " - ";
                }
                if ($key === 'checkout') {
                    $str0 .= $value;
                }
            }

            $str = '<div class="p-2 text-slate-900 dark:text-white text-sm font-medium tracking-tight">
                        <p>' . $str0 . ' ' . $str1 . '</p>
                        ' . implode('<br>', $rooms) . '
                    </div>';
        }
    @endphp
@endif
{!! $str !!}
