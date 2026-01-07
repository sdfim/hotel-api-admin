@extends('emails.vidanta_layout')

@section('title', env('APP_NAME') . ' – Your Booking Confirmation')

@section('content')
    <h1 style="font-size: 32px; line-height: 40px; margin-bottom: 25px; text-align: center; font-style: italic;">
        Welcome to {{ env('APP_NAME') }}
    </h1>

    <p style="text-align: center; margin-bottom: 40px; font-size: 16px;">
        Hello, we are delighted to confirm your upcoming stay at <strong>{{ $hotelName }}</strong>. Your reservation is now
        finalized.
    </p>

    <div
        style="margin-bottom: 40px; padding: 40px; background-color: #FBF9F6; border-top: 4px solid #C29C75; text-align: center;">
        <p style="font-size: 15px; margin-bottom: 25px; line-height: 26px;">
            We are preparing everything for your arrival to ensure your stay is truly exceptional. Please find your detailed
            confirmation document attached to this email.
        </p>
        <div
            style="display: inline-block; padding: 12px 25px; border: 1px solid #C29C75; color: #C29C75; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 2px;">
            Booking Confirmed
        </div>
    </div>

    <p style="text-align: center; margin-bottom: 40px; line-height: 26px;">
        Should you wish to customize your stay with private transfers, dining reservations, or curated experiences, please
        do not hesitate to reach out.
    </p>

    <div style="text-align: center; padding-top: 30px; border-top: 1px solid #EAEAEA;">
        <h3 style="font-size: 20px; margin-bottom: 10px; font-style: italic;">Travel Touched by Tide and Time</h3>
        <p style="font-size: 13px; color: #888;">{{ env('APP_NAME') }} Concierge Team</p>
    </div>
@endsection
