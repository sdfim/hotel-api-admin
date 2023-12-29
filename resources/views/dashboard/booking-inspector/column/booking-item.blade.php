@php
    if($getRecord()->booking_item != null){
        $booking_item = $getRecord()->booking_item;
        $str =  '<a href=' . route('booking-items.show', $booking_item ) .' target="_blank" style="color: #007bff;">' . $booking_item . '</a><br>';
    }else{
        $str = '';
    }

@endphp

<div class="m-5">{!! $str !!}</div>
