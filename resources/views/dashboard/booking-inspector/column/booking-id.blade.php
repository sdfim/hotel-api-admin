@php
    $id = $getRecord()->id;
    $booking_id = $getRecord()->booking_id;
    $shortBookingId = \App\Livewire\Helpers\ViewHelpers::compressString($booking_id);
	$str =  '<a href=' . route('booking-inspector.show', $id ) .' title="'.$booking_id.'" alt="'.$booking_id.'" target="_blank" style="color: #007bff;">' . $shortBookingId . '</a>';
@endphp
{!! $str !!}
<x-copy-button-icon value="{{ $booking_id }}"></x-copy-button-icon>
