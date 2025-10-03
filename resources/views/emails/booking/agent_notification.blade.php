@php
    use Illuminate\Support\Arr;
    use Carbon\Carbon;

    $hotelName = Arr::get($hotel, 'product.name');
    $hotelAddress = implode(', ', Arr::get($hotel, 'address'));
    $hotelGiata = Arr::get($hotel, 'giata_code');
    $rating = Arr::get($hotel, 'star_rating', 0);

    $checkin = Carbon::parse(Arr::get($searchRequest, 'checkin'))->format('m/d/Y');
    $checkout = Carbon::parse(Arr::get($searchRequest, 'checkout'))->format('m/d/Y');

    $roomsCount = count($rooms);
    $adultsCount = collect(Arr::get($searchRequest, 'occupancy', []))->sum('adults');
    $childrenCount = collect(Arr::get($searchRequest, 'occupancy', []))->sum(fn($o) => count(Arr::get($o, 'children_ages', [])));

    $grandTotal = 0;
    $currency = Arr::get($rooms, '0.currency', 'USD');

    function generateStarRating(int $rating): string {
        $html = '';
        for ($i = 0; $i < $rating; $i++) {
            $html .= '<span style="color: #FFC107; font-size: 20px;">★</span>';
        }
        for ($i = $rating; $i < 5; $i++) {
            $html .= '<span style="color: #ccc; font-size: 20px;">★</span>';
        }
        return $html;
    }
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmed by Client</title>
    <style>
        body { margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4; }
        .container {
            background:#fff;
            border-radius:8px;
            padding:20px;
            width:700px;
            margin:0 auto;
        }
        .logo { height:50px; }
        .hotel-title { margin:0 0 10px 0; font-size:20px; color:#333; }
        .hotel-address { margin:0; color:#666; }
        .info-table { width:100%; text-align:center; }
        .room-block { border:1px solid #eee; border-radius:6px; padding:10px; margin-bottom:10px; }
        .info-block {
            background-color: #f7f7f7;
            border-radius: 8px;
            padding: 15px 10px;
            flex: 1;
            margin: 0 5px;
            text-align: center;
        }
        .info-block:first-child { margin-left: 0; }
        .info-block:last-child { margin-right: 0; }
        .info-label { margin:0; color:#777; font-size: 14px; }
        .info-data { margin:0; font-weight:bold; font-size: 16px; }

        .total-price { text-align:right; font-size:16px; font-weight:bold; }
        .footer { padding:20px; color:#999; font-size:12px; text-align:center; }
        .confirmed-block { background:#e6ffe6; border-radius:8px; padding:15px; margin-bottom:20px; color:#2e7d32; font-size:16px; font-weight:bold; text-align:center; }
    </style>
</head>
<body>
<div style="padding:20px 0; text-align:center;">
    <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="Logo" class="logo">
</div>
<div class="container">
    <div class="confirmed-block">
        Quote has been <strong>confirmed by the client</strong>.<br>
        Please proceed with the next steps.
    </div>
    <h4>Quote Number: {{ $quoteNumber ?? 'N/A' }}</h4>
    <h2 class="hotel-title">{{ $hotelName ?? 'Hotel' }}</h2>
    <p class="hotel-address">📍 {{ $hotelAddress }}</p>

    <div style="margin-bottom: 20px;">
        {!! generateStarRating($rating) !!}
    </div>
    <div style="padding:20px 0; display: flex; justify-content: space-between;">
        <div class="info-block" style="flex-grow: 1;">
            <p class="info-label">Check-in</p>
            <p class="info-data">{{ $checkin }}</p>
        </div>

        <div class="info-block" style="flex-grow: 1;">
            <p class="info-label">Check-out</p>
            <p class="info-data">{{ $checkout }}</p>
        </div>

        <div class="info-block" style="flex-grow: 1.5;">
            <p class="info-label">Rooms & Guests</p>
            <p class="info-data">
                {{ $roomsCount }} Room(s), {{ $adultsCount }} Adults, {{ $childrenCount }} Children
            </p>
            <p style="margin: 5px 0 0 0; color:#777; font-size: 12px; font-weight: normal;">
            </p>
        </div>
    </div>
    <h3 style="margin:0 0 10px 0; font-size:18px; color:#333;">Rooms & Rates</h3>
    @foreach($rooms as $k => $room)
        @php
            $occupancy = Arr::get($searchRequest, "occupancy.$k");
            $grandTotal += Arr::get($room, 'total_price', 0);
        @endphp
        <div class="room-block">
            <p><strong>Room:</strong> {{ Arr::get($room, 'room_name', Arr::get($room, 'room_code')) }}</p>
            <p><strong>Rate:</strong> {{ Arr::get($room, 'rate_code') }}</p>
            @if($occupancy)
                <p><strong>Guests:</strong>
                    {{ Arr::get($occupancy, 'adults', 0) }} Adults
                    @if(count(Arr::get($occupancy, 'children_ages', [])) > 0)
                        , {{ count(Arr::get($occupancy, 'children_ages', [])) }} Children
                        (ages: {{ implode(', ', Arr::get($occupancy, 'children_ages', [])) }})
                    @endif
                </p>
            @endif
            <p>
                <strong>Price:</strong>
                {{ number_format(Arr::get($room, 'total_price', 0), 2) }} {{ Arr::get($room, 'currency', $currency) }}
                <span style="color:#777; font-size:12px;">
                        (Net: {{ number_format(Arr::get($room, 'total_net', 0), 2) }},
                        Tax: {{ number_format(Arr::get($room, 'total_tax', 0), 2) }},
                        Fees: {{ number_format(Arr::get($room, 'total_fees', 0), 2) }})
                    </span>
            </p>
        </div>
    @endforeach
    <p class="total-price">
        Total Booking Price: {{ number_format($grandTotal, 2) }} {{ $currency }}
    </p>
</div>
<div class="footer">
    Thank you!<br>{{ config('app.name') }}
</div>
</body>
</html>

