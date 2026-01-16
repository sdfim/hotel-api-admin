@extends('emails.vidanta_layout')

@section('title', env('APP_NAME') . ' – Quote Confirmation')

@section('content')
    {{-- Header Content --}}
    <h1 style="font-size: 32px; line-height: 40px; margin-bottom: 25px; text-align: center; font-style: italic;">
        Your Luxury Escape Awaits
    </h1>

    <p style="text-align: center; margin-bottom: 40px; font-size: 16px;">
        Hello, we’re delighted to assist you with your booking. Below is a summary of your curated quote for your upcoming stay.
    </p>

    {{-- Hero Image --}}
    @php
        $heroImage = $hotel->product?->hero_image
            ? \Illuminate\Support\Facades\Storage::url($hotel->product->hero_image)
            : asset('images/email-backgrounds/hotel-placeholder.jpg');
        $hotelName = $hotel->product?->name ?? 'Hotel Name';
        $hotelAddress = implode(', ', $hotel->address ?? []);
    @endphp
    <div style="margin-bottom: 40px;">
        <img src="{{ $heroImage }}" alt="{{ $hotelName }}" width="520" style="width: 100%; max-width: 520px; height: auto; margin: 0 auto; border: 1px solid #EAEAEA;">
    </div>

    {{-- Hotel Information --}}
    <div style="text-align: center; margin-bottom: 40px;">
        <h2 style="font-size: 24px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 2px;">{{ $hotelName }}</h2>
        <p style="color: #C29C75; font-weight: 400; font-size: 13px; text-transform: uppercase;">{{ $hotelAddress }}</p>
    </div>

    {{-- Details Table --}}
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 40px; border-top: 1px solid #EAEAEA; border-bottom: 1px solid #EAEAEA; padding: 30px 0;">
        <tr>
            <td align="center" style="width: 33.3%;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 5px;">Check-In</p>
                <p style="font-size: 16px; font-weight: 500; font-family: 'Playfair Display', serif;">{{ $checkinDate }}</p>
            </td>
            <td align="center" style="width: 33.3%;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 5px;">Check-Out</p>
                <p style="font-size: 16px; font-weight: 500; font-family: 'Playfair Display', serif;">{{ $checkoutDate }}</p>
            </td>
            <td align="center" style="width: 33.3%;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 5px;">Guests</p>
                <p style="font-size: 16px; font-weight: 500; font-family: 'Playfair Display', serif;">{{ $guestInfo }}</p>
            </td>
        </tr>
    </table>

    {{-- Perks --}}
    @if(!empty($perks))
        <div style="margin-bottom: 40px; padding: 30px; background-color: #FBF9F6; border: 1px solid #F3EDE4;">
            <h3 style="font-size: 18px; margin-bottom: 20px; text-align: center; letter-spacing: 1px;">EXCLUSIVE AMENITIES</h3>
            <ul style="margin: 0; padding: 0; list-style: none; text-align: center;">
                @foreach($perks as $perk)
                    <li style="display: list-item; font-family: 'Montserrat', sans-serif; font-size: 13px; color: #1C1B1B; margin-bottom: 8px; font-weight: 300;">
                        — {{ $perk }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Pricing --}}
    <div style="margin-bottom: 50px;">
        <h3 style="font-size: 18px; margin-bottom: 20px; text-align: center; letter-spacing: 1px;">PRICING SUMMARY</h3>
        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="font-family: 'Montserrat', sans-serif; font-size: 14px;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #F0F0F0; color: #888;">Subtotal</td>
                <td align="right" style="padding: 10px 0; border-bottom: 1px solid #F0F0F0; font-weight: 400;">{{ $currency }} {{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #F0F0F0; color: #888;">Taxes & Fees</td>
                <td align="right" style="padding: 10px 0; border-bottom: 1px solid #F0F0F0; font-weight: 400;">{{ $currency }} {{ number_format($taxes + $fees, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 20px 0; font-size: 18px; font-family: 'Playfair Display', serif; font-weight: 600;">Total Price</td>
                <td align="right" style="padding: 20px 0; font-size: 18px; font-family: 'Playfair Display', serif; font-weight: 600;">{{ $currency }} {{ number_format($totalPrice, 2) }}</td>
            </tr>
            @if($advisorCommission > 0)
                <tr>
                    <td style="padding: 10px 0; color: #C29C75; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Advisor Commission</td>
                    <td align="right" style="padding: 10px 0; color: #C29C75; font-weight: 500;">{{ $currency }} {{ number_format($advisorCommission, 2) }}</td>
                </tr>
            @endif
        </table>
    </div>

    {{-- Call to Action --}}
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 40px;">
        <tr>
            <td align="center">
                <a href="{{ $verificationUrl }}" style="background-color: #1C1B1B; color: #FFFFFF; padding: 18px 40px; font-family: 'Montserrat', sans-serif; font-size: 14px; font-weight: 500; letter-spacing: 2px; text-transform: uppercase; display: inline-block;">
                    Confirm Quote
                </a>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding-top: 20px;">
                <a href="{{ $denyUrl }}" style="color: #888; font-size: 12px; text-decoration: underline; font-family: 'Montserrat', sans-serif;">
                    Decline Quote
                </a>
            </td>
        </tr>
    </table>

    <p style="text-align: center; color: #888; font-size: 12px; line-height: 20px;">
        Should you have any questions, please reply directly to this email.<br>
        We look forward to arranging an unforgettable escape for you.
    </p>

@endsection
