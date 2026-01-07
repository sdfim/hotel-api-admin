@extends('booking.vidanta_landing_layout')

@section('title', 'Already Verified - ' . env('APP_NAME'))

@section('content')
    <h1 class="font-playfair italic text-5xl text-vidantaBlack mb-8">Already Verified</h1>

    <div class="space-y-6 text-vidantaBlack/80 leading-relaxed font-light text-lg">
        <p>
            This booking has already been verified. No further action is required.
        </p>
        <p>
            If you believe this is an error, please contact our support team.
        </p>
    </div>

    <div class="mt-12 flex justify-center">
        <div class="w-12 h-[1px] bg-vidantaBronze"></div>
    </div>
@endsection