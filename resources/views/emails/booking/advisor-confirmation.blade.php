@extends('emails.vidanta_layout')

@section('title', env('APP_NAME') . ' – Advisor Confirmation')

@section('content')
    <h1 style="font-size: 32px; line-height: 40px; margin-bottom: 25px; text-align: center; font-style: italic;">
        Booking Confirmed
    </h1>

    <p style="text-align: center; margin-bottom: 40px; font-size: 16px;">
        Hello, we are delighted to confirm your client’s upcoming stay at <strong>{{ $hotelName }}</strong>.
    </p>

    <div
        style="margin-bottom: 40px; padding: 40px; background-color: #FBF9F6; border-top: 4px solid #C29C75; text-align: center;">
        <p style="font-size: 14px; margin-bottom: 20px;">
            Your booking is successfully processed. We have attached the full confirmation details, including the stay
            summary and your commission breakdown, as a PDF for your records.
        </p>
        <p style="font-size: 14px; font-weight: 500; color: #C29C75; text-transform: uppercase; letter-spacing: 1px;">
            Attached: AdvisorConfirmation.pdf
        </p>
    </div>

    <p style="text-align: center; margin-bottom: 40px; line-height: 26px;">
        Should you have any questions or additional requests for your client, our concierge team is here to assist you.
        Simply reply to this email or contact us at <a href="mailto:support@vidanta.com"
            style="color: #1C1B1B; font-weight: 500; text-decoration: underline;">support@vidanta.com</a>.
    </p>

    <div style="text-align: center; padding-top: 20px; border-top: 1px solid #EAEAEA;">
        <p style="font-family: 'Playfair Display', serif; font-size: 18px; font-style: italic; color: #888;">
            Thank you for choosing {{ env('APP_NAME') }}.
        </p>
    </div>
@endsection
