@php
    $id = $getRecord()->id;
    $booking_id = $getRecord()->booking_id;
	$str =  '<a href=' . route('booking-inspector.show', $id ) .' target="_blank" style="color: #007bff;">' . $booking_id . '</a>';
@endphp
{!! $str !!}
