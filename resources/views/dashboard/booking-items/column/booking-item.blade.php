@php

    $booking_item = $getRecord()->booking_item;
    $str =  '<a href=' . route('booking-items.show', $booking_item ) .' target="_blank" class="p-2" style="color: #007bff;">' . $booking_item . '</a><br>';

@endphp

{!! $str !!}
