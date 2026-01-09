@extends('emails.vidanta_layout')

@section('title', env('APP_NAME') . ' – Trip Confirmation')

@section('php_logic')
    @php
        use Illuminate\Support\Arr;
        use Carbon\Carbon;
        use Illuminate\Support\Facades\Storage;

        $tripName = Arr::get($hotel, 'product.name', 'Your Luxury Stay');
        $checkin = Carbon::parse(Arr::get($searchRequest, 'checkin', now()))->format('d F Y');
        $checkout = Carbon::parse(Arr::get($searchRequest, 'checkout', now()->addDays(7)))->format('d F Y');

        $adultsCount = collect(Arr::get($searchRequest, 'occupancy', []))->sum('adults') ?: 2;
        $childrenCount = collect(Arr::get($searchRequest, 'occupancy', []))->sum(fn($o) => count(Arr::get($o, 'children_ages', []))) ?: 0;
        $guestsCount = $adultsCount + $childrenCount;

        $grandTotal = 0;
        foreach ($rooms as $room) {
            $grandTotal += Arr::get($room, 'total_price', 0);
        }
        $currency = Arr::get($rooms, '0.currency', 'USD');

        $bookingConfirmation = Arr::get($bookingMeta->booking_item_data ?? [], 'bookingId', 'N/A');

        if ($hotel?->product?->hero_image) {
            $heroImageUrl = Storage::url($hotel->product->hero_image);
        } else {
            $heroImageUrl = asset('images/email-backgrounds/hotel-placeholder.png');
        }
    @endphp
@endsection

@section('content')
    @yield('php_logic')

    <h1 style="font-size: 32px; line-height: 40px; margin-bottom: 25px; text-align: center; font-style: italic;">
        Congratulations!
    </h1>

    <p style="text-align: center; margin-bottom: 40px; font-size: 16px;">
        Your luxury escape has been successfully booked. We are thrilled to welcome you.
    </p>

    <div style="margin-bottom: 40px;">
        <img src="{{ $heroImageUrl }}" alt="{{ $tripName }}" width="520"
            style="width: 100%; max-width: 520px; height: auto; margin: 0 auto;">
    </div>

    <div style="text-align: center; margin-bottom: 40px;">
        <h2 style="font-size: 24px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 2px;">{{ $tripName }}
        </h2>
        <div style="width: 50px; height: 2px; background-color: #C29C75; margin: 20px auto;"></div>
    </div>

    {{-- Summary Table --}}
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
        style="margin-bottom: 40px; background-color: #FBF9F6; padding: 30px;">
        <tr>
            <td style="padding: 5px 0;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Booking ID</p>
                <p style="font-size: 14px; font-weight: 500;">{{ $bookingConfirmation }}</p>
            </td>
            <td style="padding: 5px 0;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Travel Dates</p>
                <p style="font-size: 14px; font-weight: 500;">{{ $checkin }} – {{ $checkout }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 0 5px 0;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Guests</p>
                <p style="font-size: 14px; font-weight: 500;">{{ $guestsCount }} Person(s)</p>
            </td>
            <td style="padding: 20px 0 5px 0;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Order Status</p>
                <p style="font-size: 14px; font-weight: 500; color: #C29C75;">CONFIRMED</p>
            </td>
        </tr>
    </table>

    <h3
        style="font-size: 18px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #EAEAEA; padding-bottom: 10px;">
        Rooms & Rates</h3>
    @foreach($rooms as $k => $room)
        <div style="margin-bottom: 25px; border-left: 3px solid #C29C75; padding-left: 20px;">
            <p style="font-size: 16px; font-weight: 500; color: #1C1B1B; margin-bottom: 5px;">
                {{ Arr::get($room, 'room_name', 'Room ' . ($k + 1)) }}</p>
            <p style="font-size: 13px; color: #888; margin-bottom: 5px;">Rate Code: {{ Arr::get($room, 'rate_code') }}</p>
            <p style="font-size: 14px; font-weight: 400;">
                {{ $currency }} {{ number_format(Arr::get($room, 'total_price', 0), 2) }}
            </p>
        </div>
    @endforeach

    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
        style="margin-top: 40px; border-top: 2px solid #1C1B1B; padding-top: 20px;">
        <tr>
            <td style="font-size: 18px; font-family: 'Playfair Display', serif; font-weight: 600;">Total Amount</td>
            <td align="right"
                style="font-size: 22px; font-family: 'Playfair Display', serif; font-weight: 600; color: #C29C75;">
                {{ $currency }} {{ number_format($grandTotal, 2) }}</td>
        </tr>
    </table>

    <p style="text-align: center; margin-top: 60px; font-size: 13px; color: #888;">
        A detailed confirmation PDF has been attached to this email for your records.<br>
        We look forward to hosting you soon.
    </p>
@endsection
