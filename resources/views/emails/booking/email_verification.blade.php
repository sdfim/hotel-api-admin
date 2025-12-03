@php
    use Illuminate\Support\Arr;
    use Carbon\Carbon;

    /** Basic hotel info */
    $hotelName    = Arr::get($hotel, 'product.name', 'Hotel Name');
    $hotelAddress = implode(', ', Arr::get($hotel, 'address', []));
    $heroImage    = $hotel->product?->hero_image
        ? Storage::url($hotel->product->hero_image)
        : asset('images/email-backgrounds/hotel-placeholder.png');

    /** Dates */
    $checkin  = Carbon::parse(Arr::get($searchRequest, 'checkin'))->format('m/d/Y');
    $checkout = Carbon::parse(Arr::get($searchRequest, 'checkout'))->format('m/d/Y');

    /** Guests */
    $roomsCount    = count($rooms);
    $adultsCount   = collect(Arr::get($searchRequest, 'occupancy', []))->sum('adults');
    $childrenCount = collect(Arr::get($searchRequest, 'occupancy', []))
        ->sum(fn ($o) => count(Arr::get($o, 'children_ages', [])));

    $guestInfo = $roomsCount.' Room(s), '.$adultsCount.' Adults, '.$childrenCount.' Children';

    /** Currency and totals */
    $currency           = Arr::get($rooms, '0.currency', 'USD');
    $subtotal           = 0;
    $taxes              = 0;
    $fees               = 0;
    $advisorCommission  = 0;

    foreach ($rooms as $room) {
        $subtotal          += Arr::get($room, 'total_net', 0);
        $taxes             += Arr::get($room, 'total_tax', 0);
        $fees              += Arr::get($room, 'total_fees', 0);
        $advisorCommission += Arr::get($room, 'agent_commission', 0);
    }

    $totalPrice = $subtotal + $taxes + $fees;

    /** Rate Type summary (по первой комнате) */
    $firstRoom        = $rooms[0] ?? null;
    $refundableUntil  = '';
    $mealPlanSummary  = '';

    if ($firstRoom) {
        $cancellationPolicies = Arr::get($firstRoom, 'cancellation_policies', []);
        foreach ($cancellationPolicies as $policy) {
            if (Arr::get($policy, 'description') === 'General Cancellation Policy') {
                $penaltyStartDate = Arr::get($policy, 'penalty_start_date');
                if ($penaltyStartDate) {
                    $refundableUntil = 'Refundable until ' .
                        Carbon::parse($penaltyStartDate)->format('m/d/Y');
                    break;
                }
            }
        }

        if (!empty(Arr::get($firstRoom, 'meal_plans_available'))) {
            $mealPlanSummary = Arr::get($firstRoom, 'meal_plans_available');
        } elseif (!empty($hotel->hotel_board_basis)) {
            $mealPlanSummary = is_array($hotel->hotel_board_basis)
                ? implode(', ', $hotel->hotel_board_basis)
                : $hotel->hotel_board_basis;
        } else {
            $mealPlanSummary = 'All-Inclusive Meal Plan';
        }
    }

    /** Perks */
    $perks = $perks ?? [];
@endphp

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terra Mare – Quote Confirmation</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Serif font --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
        }
        table { border-collapse: collapse; }
        img {
            border: 0;
            display: block;
            line-height: 0;
        }
        p {
            margin: 0 0 24px 0;
            font-weight: 300;
        }

        @media only screen and (max-width: 600px) {
            .wrapper      { padding: 16px 8px !important; }
            .card-padding { padding: 28px 20px 0 20px !important; }
            .inner-pad    { padding: 0 20px 32px 20px !important; }
            .pricing-card { padding: 24px 20px !important; }
            .button-primary {
                font-size: 18px !important;
                padding: 18px 28px !important;
            }
        }
    </style>
</head>
<body>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" class="wrapper" style="padding:24px 12px;">

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;">

                {{-- TOP GREEN INTRO --}}
                <tr>
                    <td bgcolor="#C7D5C7"
                        class="card-padding"
                        style="
                            padding:40px 32px 0 32px;
                            font-family:'Playfair Display', Georgia, serif;
                            color:#263A3A;
                            font-size:16px;
                            line-height:1.6;
                        ">
                        {{-- Logo --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:36px;">
                            <tr>
                                <td align="right">
                                    <img src="{{ asset('images/terra-mare-logo.png') }}"
                                         alt="Terra Mare"
                                         width="238"
                                         style="height:auto;">
                                </td>
                            </tr>
                        </table>

                        <p>Hello,</p>

                        <p>
                            We’re delighted to assist you with the first step of your booking. Below, you’ll find
                            an advisor-friendly summary of your quote that can be easily accepted or declined.
                            A client-ready presentation is also attached for effortless sharing with your traveler.
                        </p>

                        <p>
                            Should you have any questions, dietary preferences, or additional requests, our
                            concierge team will be happy to assist you. We look forward to arranging an
                            unforgettable escape for your client.
                        </p>

                        <p style="margin-top:32px;">
                            Warm regards,<br>
                            Terra Mare Concierge
                        </p>
                    </td>
                </tr>

                {{-- MAIN SECTION WITH WAVE BG --}}
                <tr>
                    <td bgcolor="#E9EDE7"
                        background="{{ asset('images/email-backgrounds/wave-bg.png') }}"
                        style="
                            font-family:'Playfair Display', Georgia, serif;
                            color:#263A3A;
                            font-size:16px;
                            line-height:1.6;
                            background-image:url('{{ asset('images/email-backgrounds/wave-bg.png') }}');
                            background-repeat:no-repeat;
                            background-position:center top;
                            background-size:cover;
                        ">
                        {{-- Wave divider --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="line-height:0; padding:0;">
                                    <img src="{{ asset('images/email-backgrounds/wave-divider.png') }}"
                                         alt=""
                                         width="680"
                                         style="width:100%; height:auto; display:block;">
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="inner-pad"
                               style="padding:0 32px 40px 32px;">
                            <tr>
                                <td style="padding: 0 10px">

                                    {{-- BOOKING DETAILS BELOW --}}
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
                                        <tr>
                                            <td align="center"
                                                style="font-size:12px; font-weight:400; letter-spacing:0.12em; text-transform:uppercase;">
                                                Booking Details Below
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Hotel name + address --}}
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                        <tr>
                                            <td align="center" style="font-size:32px; line-height:1.4;">
                                                {{ $hotelName }}
                                            </td>
                                        </tr>
                                        @if($hotelAddress)
                                            <tr>
                                                <td align="center"
                                                    style="font-size:16px; margin-top:4px; color:#4b635c;">
                                                    {{ $hotelAddress }}
                                                </td>
                                            </tr>
                                        @endif
                                    </table>

                                    {{-- Hero image --}}
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                        <tr>
                                            <td align="center">
                                                <img src="{{ $heroImage }}"
                                                     alt="{{ $hotelName }}"
                                                     style="width:100%; max-width:616px; height:auto; border-radius:28px; object-fit:cover;">
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Check-in / Check-out / Guests (без зелёных блоков) --}}
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                           style="margin-bottom:40px;">
                                        <tr>
                                            <td align="center" style="padding:0 8px;">
                                                <div style="font-size:17px; margin-bottom:4px;">
                                                    Check-In
                                                </div>
                                                <div style="font-size:20px;">
                                                    {{ $checkin }}
                                                </div>
                                            </td>
                                            <td align="center" style="padding:0 8px;">
                                                <div style="font-size:17px; margin-bottom:4px;">
                                                    Check-Out
                                                </div>
                                                <div style="font-size:20px;">
                                                    {{ $checkout }}
                                                </div>
                                            </td>
                                            <td align="center" style="padding:0 8px;">
                                                <div style="font-size:17px; margin-bottom:4px;">
                                                    Rooms &amp; Guests
                                                </div>
                                                <div style="font-size:18px;">
                                                    {{ $guestInfo }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Perks pills --}}
                                    @if(!empty($perks))
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                               style="margin-bottom:32px;">
                                            <tr>
                                                <td align="center" style="padding-bottom:16px;">
                                                    <div style="font-size:22px;">
                                                        Terra Mare Exclusive Perks:
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center">
                                                    @foreach($perks as $perk)
                                                        <span style="
                                                            display:inline-block;
                                                            margin:4px 4px;
                                                            padding:6px 10px;
                                                            border-radius:999px;
                                                            border:1px solid #263A3A;
                                                            font-size:12px;
                                                            white-space:nowrap;
                                                        ">
                                                            {{ $perk }}
                                                        </span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        </table>
                                    @endif

                                    {{-- Rate Type + Pricing card --}}
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                           style="margin-bottom:40px;">
                                        <tr>
                                            <td align="center">
                                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                                       style="max-width:540px;">
                                                    <tr>
                                                        <td class="pricing-card"
                                                            style="
                                                                background:#C7D5C7;
                                                                border-radius:30px;
                                                                padding:28px 32px;
                                                            ">

                                                            {{-- Rate Type --}}
                                                            <div style="font-size:24px; text-align:center; margin-bottom:8px;">
                                                                Rate Type:
                                                            </div>
                                                            @if($refundableUntil)
                                                                <div style="font-size:20px; text-align:center;">
                                                                    {{ $refundableUntil }}
                                                                </div>
                                                            @endif
                                                            @if($mealPlanSummary)
                                                                <div style="font-size:20px; text-align:center; margin-bottom:20px;">
                                                                    {{ $mealPlanSummary }}
                                                                </div>
                                                            @endif

                                                            {{-- Pricing --}}
                                                            <div style="font-size:24px; text-align:center; margin:8px 0 16px;">
                                                                Pricing:
                                                            </div>

                                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                                                   style="font-size:20px;">
                                                                <tr>
                                                                    <td style="padding:4px 0;">Subtotal:</td>
                                                                    <td align="right" style="padding:4px 0;">
                                                                        {{ $currency }} {{ number_format($subtotal, 2) }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding:4px 0;">Taxes &amp; Fees:</td>
                                                                    <td align="right" style="padding:4px 0;">
                                                                        {{ $currency }} {{ number_format($taxes + $fees, 2) }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2"
                                                                        style="padding-top:8px; border-top:1px solid #263A3A;"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding:8px 0; font-size:24px;">
                                                                        Total Price:
                                                                    </td>
                                                                    <td align="right" style="padding:8px 0; font-size:22px; font-weight:500;">
                                                                        {{ $currency }} {{ number_format($totalPrice, 2) }}
                                                                    </td>
                                                                </tr>
                                                                @if($advisorCommission > 0)
                                                                    <tr>
                                                                        <td style="padding-top:20px; font-size:18px;">
                                                                            Advisor Commission:
                                                                        </td>
                                                                        <td align="right" style="padding-top:20px; font-size:18px;">
                                                                            {{ $currency }} {{ number_format($advisorCommission, 2) }}
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Confirm button --}}
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                           style="margin: 60px 0;">
                                        <tr>
                                            <td align="center">
                                                <a href="{{ $verificationUrl }}"
                                                   class="button-primary"
                                                   style="
                                                        background:#263A3A;
                                                        border-radius:14px;
                                                        color:#FFFFFF;
                                                        font-size:20px;
                                                        padding:10px 20px;
                                                        text-decoration:none;
                                                        display:inline-block;
                                                   ">
                                                    Confirm Quote
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Bottom text --}}
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:40px;">
                                        <tr>
                                            <td align="center" style="font-size:26px; padding-bottom:16px;">
                                                Thank you for your request!
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="font-size:16px; max-width:540px; padding: 0 50px">
                                                Should you wish to decline this quote, please reply to this message – we’ll be
                                                happy to prepare a new proposal that better suits your client’s needs.
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="font-size:20px; padding-top:18px;">
                                                Terra Mare
                                            </td>
                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
