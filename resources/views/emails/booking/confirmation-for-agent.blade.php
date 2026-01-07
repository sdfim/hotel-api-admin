@extends('emails.vidanta_layout')

@section('title', env('APP_NAME') . ' – Agent Booking Confirmation')

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
        Booking Confirmed
    </h1>

    <p style="text-align: center; margin-bottom: 40px; font-size: 16px;">
        Hello, your client's reservation at <strong>{{ $tripName }}</strong> has been successfully confirmed.
    </p>

    <div style="margin-bottom: 40px; padding: 30px; border: 1px solid #C29C75; background-color: #FBF9F6;">
        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 10px 0;">
                    <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Resort</p>
                    <p style="font-size: 15px; font-weight: 500;">{{ $tripName }}</p>
                </td>
                <td align="right" style="padding: 10px 0;">
                    <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Booking ID</p>
                    <p style="font-size: 15px; font-weight: 500;">{{ $bookingConfirmation }}</p>
                </td>
            </tr>
            <tr>
                <td style="padding: 20px 0 10px 0;">
                    <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Travel Dates
                    </p>
                    <p style="font-size: 14px; font-weight: 400;">{{ $checkin }} – {{ $checkout }}</p>
                </td>
                <td align="right" style="padding: 20px 0 10px 0;">
                    <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Total Sale</p>
                    <p style="font-size: 14px; font-weight: 500; color: #C29C75;">{{ $currency }}
                        {{ number_format($grandTotal, 2) }}</p>
                </td>
            </tr>
        </table>
    </div>

    <h3
        style="font-size: 18px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #EAEAEA; padding-bottom: 10px;">
        Guest Information</h3>
    <p style="margin-bottom: 40px; font-size: 14px;">
        Number of Guests: {{ $guestsCount }}<br>
        Accommodations: {{ count($rooms) }} Room(s)
    </p>

    <p style="text-align: center; margin-top: 40px; line-height: 24px;">
        We have attached the official confirmation document for your files. Please ensure your client has all necessary
        travel documentation prior to their arrival.
    </p>

    <div style="text-align: center; padding-top: 30px; border-top: 1px solid #EAEAEA; margin-top: 40px;">
        <p style="font-size: 13px; color: #888;">Questions? Reach out to our advisor support team.</p>
        <p style="font-size: 14px; font-weight: 500; color: #1C1B1B;">{{ env('APP_NAME') }} Advisor Relations</p>
    </div>
@endsection
