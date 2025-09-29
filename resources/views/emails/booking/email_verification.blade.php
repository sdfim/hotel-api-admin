@php
    use Illuminate\Support\Arr;
    use Carbon\Carbon;

    $hotelName = Arr::get($hotel, 'product.name');
    $hotelAddress = implode(', ', Arr::get($hotel, 'address'));
    $hotelGiata = Arr::get($hotel, 'giata_code');

    $checkin = Carbon::parse(Arr::get($searchRequest, 'checkin'))->format('m/d/Y');
    $checkout = Carbon::parse(Arr::get($searchRequest, 'checkout'))->format('m/d/Y');

    $roomsCount = count($rooms);
    $adultsCount = collect(Arr::get($searchRequest, 'occupancy', []))->sum('adults');
    $childrenCount = collect(Arr::get($searchRequest, 'occupancy', []))->sum(fn($o) => count(Arr::get($o, 'children_ages', [])));

    $grandTotal = 0;
    $currency = Arr::get($rooms, '0.currency', 'USD');
@endphp

    <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirm your booking</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="padding:20px 0;">
            <img src="{{ URL::asset('build/images/logo-sm.svg') }}" alt="Logo" style="height:50px;">
        </td>
    </tr>
    <tr>
        <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="background:#fff; border-radius:8px; overflow:hidden; padding:20px;">
                <tr>
                    <td>
                        <h2 style="margin:0 0 10px 0; font-size:20px; color:#333;">{{ $hotelName ?? 'Hotel' }}</h2>
                        <p style="margin:0; color:#666;">📍 {{ $hotelAddress }}</p>
{{--                        <p style="margin:0; color:#999;">GIATA Code: {{ $hotelGiata }}</p>--}}
                    </td>
                </tr>

                <tr>
                    <td style="padding:20px 0;">
                        <table width="100%" style="text-align:center;">
                            <tr>
                                <td>
                                    <p style="margin:0; color:#777;">Check-in</p>
                                    <p style="margin:0; font-weight:bold;">{{ $checkin }}</p>
                                </td>
                                <td>
                                    <p style="margin:0; color:#777;">Check-out</p>
                                    <p style="margin:0; font-weight:bold;">{{ $checkout }}</p>
                                </td>
                                <td>
                                    <p style="margin:0; color:#777;">Rooms & Guests</p>
                                    <p style="margin:0; font-weight:bold;">
                                        {{ $roomsCount }} Room(s), {{ $adultsCount }} Adults, {{ $childrenCount }} Children
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <h3 style="margin:0 0 10px 0; font-size:18px; color:#333;">Rooms & Rates</h3>
                        @foreach($rooms as $k => $room)
                            @php
                                $occupancy = Arr::get($searchRequest, "occupancy.$k");
                                $grandTotal += Arr::get($room, 'total_price', 0);
                            @endphp
                            <div style="border:1px solid #eee; border-radius:6px; padding:10px; margin-bottom:10px;">
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
                        <p style="text-align:right; font-size:16px; font-weight:bold;">
                            Total Booking Price: {{ number_format($grandTotal, 2) }} {{ $currency }}
                        </p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:20px;">
                        <a href="{{ $verificationUrl }}" style="background:#4f46e5; color:#fff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold;">
                            Confirm Booking
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td align="center" style="padding:20px; color:#999; font-size:12px;">
            Thank you!<br>{{ config('app.name') }}
        </td>
    </tr>
</table>
</body>
</html>
