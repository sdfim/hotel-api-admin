@php
    use Illuminate\Support\Facades\Storage;

    $hotelName    = $hotelData['name']    ?? 'Hotel Name';
    $hotelAddress = $hotelData['address'] ?? '';

    $heroImage = $hotelPhotoPath
        ?: ($hotel->product?->hero_image
            ? Storage::url($hotel->product->hero_image)
            : Storage::url('hotel.webp'));

    $secondaryImage = $heroImage;

    $totalNet    = $total_net   ?? 0;
    $totalTax    = $total_tax   ?? 0;
    $totalFees   = $total_fees  ?? 0;
    $totalPrice  = $total_price ?? ($totalNet + $totalTax + $totalFees);
    $subtotal    = $subtotal    ?? ($totalPrice - $totalTax - $totalFees);

    $agencyName  = $agency['booking_agent']       ?? ' {{ env('APP_NAME') }}Tours';
    $agencyEmail = $agency['booking_agent_email'] ?? 'support@terramaretours.com';

    $checkin        = $checkin         ?? null;
    $checkout       = $checkout        ?? null;
    $guestInfo      = $guest_info      ?? null;
    $mainRoomName   = $main_room_name  ?? null;
    $rateRefundable = $rate_refundable ?? null;
    $rateMealPlan   = $rate_meal_plan  ?? null;
    $perks          = $perks           ?? [];
    $currency       = $currency        ?? 'USD';
    $taxesAndFees   = $taxes_and_fees  ?? ($totalTax + $totalFees);

    $logoTm = asset('images/emails/terra-mare-logo-pdf.png');
    $bgWave = asset('images/email-backgrounds/wave-bg.png');
@endphp

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quote Details</title>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Times New Roman", Georgia, serif;
            font-size: 13px;
            line-height: 1.4;
            color: #1c2525;
            background-color: #f4f0ed;
            background-image: url('{{ $bgWave }}');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
        }

        .page {
            padding: 40px 40px 36px 40px;
        }

        h1 {
            font-size: 30px;
            font-weight: normal;
            margin: 0 0 6px 0;
        }

        .hotel-address {
            font-size: 14px;
            margin-bottom: 12px;
        }

        .hotel-image-main {
            width: 100%;
            height: auto;
            display: block;
        }

        .logo-tm {
            width: 78px;
            height: auto;
            display: block;
        }

        .pill-row {
            margin-bottom: 10px;
        }

        .pill {
            background: #c7d5c7;
            border-radius: 999px;
            padding: 8px 10px;
            text-align: center;
            font-size: 20px;
        }
        .pill-title {
            display: block;
            font-size: 16px;
            margin-bottom: 3px;
        }
        .pill-value {
            display: block;
            font-size: 18px;
        }

        .card-vertical {
            background: #c7d5c7;
            border-radius: 26px;
            padding: 10px 12px;
            text-align: center;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .card-vertical-title {
            font-size: 16px;
            margin-bottom: 4px;
        }
        .card-vertical-line {
            display: block;
            font-size: 18px;
        }

        ul {
            margin: 6px 0 0 18px;
            padding: 0;
        }
        ul li {
            margin-bottom: 4px;
            font-size: 11px;
        }

        .contact-block {
            font-size: 14px;
            line-height: 1.6;
        }
        .contact-name {
            font-size: 20px;
            margin-bottom: 6px;
        }

        .pricing-card {
            background: #c7d5c7;
            border-radius: 26px;
            padding: 16px 24px;
            font-size: 11px;
        }
        .pricing-title {
            text-align: left;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .pricing-row td {
            padding: 2px 0;
        }
        .pricing-label {
            text-align: left;
            font-size: 16px;
        }
        .pricing-value {
            text-align: right;
            font-size: 16px;
        }

        .pricing-total {
            border-top: 1px solid #263a3a;
            padding-top: 4px;
            margin-top: 4px;
        }

        .big-spacer { height: 26px; }
    </style>
</head>
<body>
    <div class="page">

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr valign="top">

                <td width="64%" style="padding-right: 26px;">
                    <h1>{{ $hotelName }}</h1>

                    @if($hotelAddress)
                        <div class="hotel-address">
                            {{ $hotelAddress }}
                        </div>
                    @endif

                    @if($heroImage)
                        <img src="{{ $heroImage }}" class="hotel-image-main" alt="Hotel image">
                    @endif
                </td>

                <td width="36%" align="right">
                    <div style="text-align:right; margin-bottom: 24px;">
                        <img src="{{ $logoTm }}" alt=" <?= env('APP_NAME'); ?>" class="logo-tm">
                    </div>

                    @if($checkin || $checkout)
                        <table width="100%" cellspacing="0" cellpadding="0" class="pill-row">
                            <tr>
                                <td width="50%" style="padding-right:4px;">
                                    <div class="pill">
                                        <span class="pill-title">Check-In</span>
                                        <span class="pill-value">{{ $checkin ?? '—' }}</span>
                                    </div>
                                </td>
                                <td width="50%" style="padding-left:4px;">
                                    <div class="pill">
                                        <span class="pill-title">Check-Out</span>
                                        <span class="pill-value">{{ $checkout ?? '—' }}</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    @endif

                    @if($rateRefundable || $rateMealPlan)
                        <div class="card-vertical">
                            <div class="card-vertical-title">Rate Type</div>
                            @if($rateRefundable)
                                <span class="card-vertical-line">{{ $rateRefundable }}</span>
                            @endif
                            @if($rateMealPlan)
                                <span class="card-vertical-line">{{ $rateMealPlan }}</span>
                            @endif
                        </div>
                    @endif

                    @if($mainRoomName || $guestInfo)
                        <div class="card-vertical">
                            <div class="card-vertical-title">Rooms &amp; Guests</div>
                            @if($mainRoomName)
                                <span class="card-vertical-line">{{ $mainRoomName }}</span>
                            @endif
                            @if($guestInfo)
                                <span class="card-vertical-line">{{ $guestInfo }}</span>
                            @endif
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="big-spacer"></div>

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr valign="top">

                <td width="60%" style="padding-right:26px;">
                    <div style="font-size:24px; margin: 0 0 10px 0;"> {{ env('APP_NAME') }}Exclusive Perks:</div>

                    @if(!empty($perks))
                        <ul>
                            @foreach($perks as $perk)
                                <li style="font-size: 14px;">{{ $perk }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div style="font-size:16px;">
                            Perks information will be provided upon request.
                        </div>
                    @endif
                </td>

                <td width="40%">
                    @if($secondaryImage)
                        <img src="{{ $secondaryImage }}" class="hotel-image-main" alt="Hotel view">
                    @endif
                </td>
            </tr>
        </table>

        <div class="big-spacer"></div>

        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 40px;">
            <tr valign="top">

                <td width="50%" style="padding-right:26px;">
                    <div class="contact-block">
                        <div class="contact-name"> {{ env('APP_NAME') }}Tours</div>
                        <div >225 Broadway, Fl. 23,</div>
                        <div>New York, NY, 10007, USA</div>
                        <div>+1 (332)-232-8351</div>
                        <div>{{ $agencyEmail }}</div>
                    </div>
                </td>

                <td width="50%" align="right">
                    <div class="pricing-card" style="width: 320px;">
                        <div class="pricing-title">Pricing:</div>

                        <table width="100%" class="pricing-row" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="pricing-label">Reservation Subtotal:</td>
                                <td class="pricing-value">
                                    {{ $currency }} {{ number_format($subtotal, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pricing-label">Taxes &amp; Fees:</td>
                                <td class="pricing-value">
                                    {{ $currency }} {{ number_format($taxesAndFees, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="pricing-total">
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td class="pricing-label" style="font-size: 20px;">Total Price:</td>
                                            <td class="pricing-value" style="font-size: 20px;">
                                                {{ $currency }} {{ number_format($totalPrice, 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
