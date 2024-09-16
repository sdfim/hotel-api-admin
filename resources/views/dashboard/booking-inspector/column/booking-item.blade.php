@php
    if($getRecord()->booking_item != null){
        $booking_item = $getRecord()->booking_item;
        $shortBookingItem = \App\Livewire\Helpers\ViewHelpers::compressString($booking_item);
        $str =  '<a href=' . route('booking-items.show', $booking_item ) .' title="'.$booking_item.'" alt="'.$booking_item.'" target="_blank" style="color: #007bff;">' . $shortBookingItem . '</a><br>';
    }else{
        $str = '';
    }

@endphp

{!! $str !!}
<x-copy-button-icon value="{{ $booking_item }}"></x-copy-button-icon>
