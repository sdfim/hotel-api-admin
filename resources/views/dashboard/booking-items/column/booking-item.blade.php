@php

	$booking_item = $getRecord()->booking_item;
    $str =  '<a href=' . route('booking-items.show', $booking_item ) .' target="_blank" style="color: #007bff;">' . $booking_item . '</a><br>';
	
@endphp

{!! $str !!}