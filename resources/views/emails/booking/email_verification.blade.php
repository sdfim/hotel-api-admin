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

    $guestInfo = $roomsCount . ' Room(s), ' . $adultsCount . ' Adults, ' . $childrenCount . ' Children';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Terra Mare Hotel - Payment Confirmation</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">
    <style>

        body {
            margin: 0;
            padding: 0;
            font-family: 'Playfair Display', Georgia, serif !important;
            font-size: calc(1em * 1.2);
        }

        .container {
            border-radius: 8px;
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Playfair Display', Georgia, serif !important;
            font-size: inherit;
            font-weight: 300;
            background: #fff url('{{ asset('build/images/bg-mail-tm.png') }}') no-repeat center top;
            background-size: cover;
        }

        h1, h2, h3, h4, h5, h6, p, ul, li, div, span, a {
            font-family: 'Playfair Display', Georgia, serif !important;
            font-size: inherit;
            font-weight: 300;
        }

        p {
            line-height: 1.6;
            margin-bottom: 32px;
        }
    </style>
<body>
<div class="container">
    <div style="padding:90px;">
        <div style="display: flex; justify-content: flex-end; align-items: center;">
            <img src="{{ asset('build/images/logo-tm.png') }}" style="height:90px;">
        </div>

        <p>
            Hello,
        </p>
        <p>
            We are delighted to assist you with the first step of your booking. Below you’ll find a summary of your
            quote that can
            be easily accepted or denied.
        </p>
        <p>
            Should you have any questions, dietary preferences, or additional requests, our concierge team will be happy
            to assist you.
            We look forward to arranging an unforgettable escape for you.
        </p>
        <p>
            Warm regards,
            Terra Mare Concierge
        </p>

        <h2 style="font-size:40px; margin-top:24px; margin-bottom: 0 !important;  color:#19332c; font-weight:500;">{{ $hotelName }}</h2>
        <p style="margin:4px 0 0 0; color:#4b635c;">{{ $hotelAddress }}</p>
        <div style="margin:8px 0 0 0;">{!! generateStarRating($rating) !!}</div>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
            <tr>
                <td align="center" style="padding:0;">
                    <img src="{{ Storage::url($hotel->product?->hero_image) }}" style="width:55%; border-radius:30px; height:380px; max-height:380px; object-fit:cover; display:block;">
                </td>
            </tr>
        </table>


        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center"
               style="margin: 74px auto; width: 80%; max-width: 100%;">
            <tr>
                <td align="center" style="padding: 0 15px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                        <tr>
                            <td align="center" style="border-radius:30px; background:#c7d5c7; padding:25px;">
                                <div style="font-size:22px; margin-bottom: 5px;">Check-in</div>
                                <div style="font-size:33px;  color:#19332C;">{{ $checkin }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td align="center" style="padding: 0 15px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                        <tr>
                            <td align="center" style="border-radius:30px; background:#c7d5c7; padding:25px;">
                                <div style="font-size:22px; margin-bottom: 5px;">Check-out</div>
                                <div style="font-size:33px;  color:#19332C;">{{ $checkout }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td align="center" style="padding: 0 15px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                        <tr>
                            <td align="center" style="border-radius:30px; background:#c7d5c7; padding:25px;">
                                <div style="font-size:22px; margin-bottom: 5px;">Rooms & Guests</div>
                                <div style="font-size:33px;  color:#19332C;">
                                    {{ $guestInfo }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <h3 style="color:#19332c; font-size:33px; margin:34px 0 10px;">Terra Mare Exclusive Perks:</h3>
        <ul style="color:#4b635c; line-height:1.8; margin-bottom:16px;">
            <li>$250 USD Hotel credit</li>
            <li>Upgrade upon arrival, subject to availability</li>
            <li>Complimentary daily breakfast for 2</li>
            <li>Early check-in/late check-out, subject to availability</li>
        </ul>

        {{-- Room blocks with new styles, dynamic loop --}}
        <h3 style="color:#19332c; font-size:33px; margin:33px 0 28px;">Rooms & Rates:</h3>

        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%; margin-bottom: 38px">
            <tr>
                <td style="width:50%; vertical-align:top; padding-right: 7%;">
                <div style="color:#246155; font-size: 28px;margin-bottom: 15px;">Room Information</div>
                </td>
                <td style="width:41%; vertical-align:top; padding-left: 2%;">
                    <div style="color:#246155; font-size: 28px;margin-bottom: 15px;">Rate Information</div>
                </td>
            </tr>
        </table>

        @foreach($rooms as $k => $room)
            @php
                $occupancy = Illuminate\Support\Arr::get($searchRequest, "occupancy.$k");
                $grandTotal += Illuminate\Support\Arr::get($room, 'total_price', 0);
                $cancellationPolicies = Illuminate\Support\Arr::get($room, 'cancellation_policies', []);
                $refundableUntil = '';
                foreach ($cancellationPolicies as $policy) {
                    if (Illuminate\Support\Arr::get($policy, 'description') === 'General Cancellation Policy') {
                        $penaltyStartDate = Illuminate\Support\Arr::get($policy, 'penalty_start_date');
                        if ($penaltyStartDate) {
                            $refundableUntil = 'Refundable until ' . \Carbon\Carbon::parse($penaltyStartDate)->addDays(30)->format('m/d/Y');
                            break;
                        }
                    }
                }
                $rateType = '';
                if (!empty(Illuminate\Support\Arr::get($room, 'meal_plans_available'))) {
                    $rateType = Illuminate\Support\Arr::get($room, 'meal_plans_available');
                } elseif (!empty($hotel->hotel_board_basis)) {
                    $rateType = is_array($hotel->hotel_board_basis) ? implode(', ', $hotel->hotel_board_basis) : $hotel->hotel_board_basis;
                } else {
                    $rateType = 'All-Inclusive Meal Plan';
                }
                $netPrice = number_format(Illuminate\Support\Arr::get($room, 'total_net', 0), 2);
                $taxesFees = number_format(Illuminate\Support\Arr::get($room, 'total_tax', 0) + Illuminate\Support\Arr::get($room, 'total_fees', 0), 2);
                $agentCommission = number_format(Illuminate\Support\Arr::get($room, 'agent_commission', 0), 2);
                $totalPrice = number_format(Illuminate\Support\Arr::get($room, 'total_price', 0), 2);
                $currentCurrency = Illuminate\Support\Arr::get($room, 'currency', $currency);
                $minHeight = '400px';
                $imageHeight = '380px';
                $roomImage = $room?->room_image ?? $hotel?->product?->hero_image;
            @endphp
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                   style="margin-bottom: 88px;">
                <tr>
                    <td style="width:50%; vertical-align:top; padding-right: 7%;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                               style="height: 100%;">
                            <tr>
                                <td style="padding: 0; vertical-align: top;">
                                    <img src="{{ Storage::url($roomImage) }}"
                                         style="width:100%; border-radius:30px; display:block; height: {{ $imageHeight }}; max-height: {{ $imageHeight }}; object-fit: cover;">
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-top:8px; vertical-align: top;">
                                    <div style="font-size:28px; color:#246155;">
                                        Room Type: {{ Illuminate\Support\Arr::get($room, 'room_name', Illuminate\Support\Arr::get($room, 'room_code', 'Terrace Grand Suite')) }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:41%; vertical-align:top; padding-left: 2%;">
                        <div
                            style="background:#c7d5c7; border-radius:30px; padding:25px; min-height: {{ $minHeight }}; height: 100%; box-sizing: border-box;">
                            <span style="font-size:28px; display: block; margin-bottom: 5px;">Rate Type:</span>
                            <span style="font-size:20px; display: block;">{{ $refundableUntil }}</span>
                            <span style="font-size:20px; display: block; margin-bottom: 25px;">{{ $rateType }}</span>

                            @if($occupancy)
                                <span style="font-size:20px; display: block; margin-bottom: 20px;">
                                    Guests: {{ Illuminate\Support\Arr::get($occupancy, 'adults', 0) }} Adults
                                    @if(count(Illuminate\Support\Arr::get($occupancy, 'children_ages', [])) > 0)
                                        , {{ count(Illuminate\Support\Arr::get($occupancy, 'children_ages', [])) }}
                                        Children
                                        (ages: {{ implode(', ', Illuminate\Support\Arr::get($occupancy, 'children_ages', [])) }}
                                        )
                                    @endif
                                </span>
                            @endif

                            <span
                                style="font-size:28px; display: block; margin-top: 35px; margin-bottom: 15px;">Pricing:</span>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                   style="font-size:20px;">
                                <tr>
                                    <td style="padding-bottom: 5px; vertical-align: top; width: 60%;">Net Price:</td>
                                    <td align="right"
                                        style="padding-bottom: 5px; vertical-align: top; width: 40%;">
                                        ${{ $netPrice }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 5px; vertical-align: top;">Taxes & Fees:</td>
                                    <td align="right"
                                        style="padding-bottom: 5px; vertical-align: top;">
                                        ${{ $taxesFees }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 5px; vertical-align: top; border-bottom: 1px solid #777;">
                                        Advisor Commission:
                                    </td>
                                    <td align="right"
                                        style="padding-bottom: 5px; vertical-align: top; border-bottom: 1px solid #777;">
                                        ${{ $agentCommission }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 15px; font-weight:bold; color:#194c39; font-size: 25px; vertical-align: top;">
                                        Total Price:
                                    </td>
                                    <td align="right"
                                        style="padding-top: 15px; font-weight:bold; color:#194c39; font-size: 25px; vertical-align: top;">
                                        ${{ $totalPrice }}</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        @endforeach

        <div style="text-align:center; margin-top:30px;">
            <a href="{{ $verificationUrl }}"
               style="background: #263a3a;
                    border-radius: 30px;
                    color: #fff;
                    font-size: 35px;
                    padding: 35px 45px;
                    text-decoration: none;
                    display: inline-block;">
                Confirm Quote
            </a>
        </div>
        <div style="text-align:center; font-size:18px; margin:32px 0 0;">
            Thank you for your request!<br>Terra Mare
        </div>
    </div>
</div>

</body>
</html>
