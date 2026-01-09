@extends('booking.vidanta_landing_layout')

@section('title', 'Not Found - ' . env('APP_NAME'))

@section('content')
    <h1 class="font-playfair italic text-5xl text-vidantaBlack mb-8">Verification Error</h1>

    <div class="space-y-6 text-vidantaBlack/80 leading-relaxed font-light text-lg">
        <p>
            We could not find the booking verification record you are looking for.
        </p>
        <p>
            This may be due to an expired link or an incorrect URL. Please ensure you are using the correct link from your
            official confirmation email.
        </p>
    </div>

    <div class="mt-12 flex justify-center">
        <div class="w-12 h-[1px] bg-vidantaBronze text-red-500"></div>
    </div>
@endsection