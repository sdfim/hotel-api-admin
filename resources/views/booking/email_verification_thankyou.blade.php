@extends('booking.vidanta_landing_layout')

@section('title', 'Confirmation - ' . env('APP_NAME'))

@section('content')
    <h1 class="font-playfair italic text-5xl text-vidantaBlack mb-8">Thank You</h1>

    <div class="space-y-6 text-vidantaBlack/80 leading-relaxed font-light text-lg">
        <p>
            Your quote has been successfully approved.
        </p>
        <p>
            We will now proceed with preparing the final documents. Your client will receive a secure payment link shortly
            to finalize the reservation.
        </p>
        <p>
            You will receive a confirmation email once the process is complete.
        </p>
    </div>

    <div class="mt-12 flex justify-center">
        <div class="w-12 h-[1px] bg-vidantaBronze"></div>
    </div>
@endsection