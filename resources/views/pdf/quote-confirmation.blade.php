@php
    use Illuminate\Support\Facades\Storage;

    $hotelName    = $hotelData['name']    ?? 'Hotel Name';
    $hotelAddress = $hotelData['address'] ?? '';

    $heroImage = $hotelPhotoPath ?? Storage::url('hotel.webp');
    $secondaryImage = $roomPhotoPath ?? Storage::url('hotel.webp');

    $totalNet    = $total_net   ?? 0;
    $totalTax    = $total_tax   ?? 0;
    $totalFees   = $total_fees  ?? 0;
    $totalPrice  = $total_price ?? ($totalNet + $totalTax + $totalFees);
    $subtotal    = $subtotal    ?? ($totalPrice - $totalTax - $totalFees);

    $agencyName  = $agency['booking_agent'] ?? (env('APP_NAME') . ' Tours');
    $agencyEmail = $agency['booking_agent_email'] ?? 'support@vidanta.com';

    $checkin        = $checkin         ?? null;
    $checkout       = $checkout        ?? null;
    $guestInfo      = $guest_info      ?? null;
    $mainRoomName   = $main_room_name  ?? null;
    $rateRefundable = $rate_refundable ?? null;
    $rateMealPlan   = $rate_meal_plan  ?? null;
    $perks          = $perks           ?? [];
    $currency       = $currency        ?? 'USD';
    $taxesAndFees   = $taxes_and_fees  ?? ($totalTax + $totalFees);

    // Static assets - Vidanta branding
    $logoTm = public_path('images/firm-logo.png');
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
            font-family: Georgia, "Times New Roman", serif;
            font-size: 13px;
            line-height: 1.5;
            color: #1C1B1B;
            background-color: #F7F7F7;
        }

        .page {
            padding: 40px 40px 36px 40px;
        }

        h1 {
            /* Бронзовый, крупный, serif заголовок — курсив */
            font-size: 32px;
            font-weight: normal;
            font-style: italic;
            color: #C29C75;
            font-family: Georgia, serif;
            margin: 0 0 15px 0;
            letter-spacing: 0.5px;
        }

        h2 {
            /* Название отеля — uppercase, тёмный */
            font-size: 22px;
            font-weight: normal;
            font-family: Georgia, serif;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #1C1B1B;
            margin: 0 0 8px 0;
        }

        .hotel-address {
            font-size: 13px;
            color: #888;
            text-transform: none; /* Убран uppercase */
            letter-spacing: 0;
            margin-bottom: 20px;
        }

        .hotel-image-main {
            width: 100%;
            height: auto;
            display: block;
            border: 1px solid #EAEAEA;
        }

        .logo-tm {
            width: 160px; /* Увеличенный логотип */
            height: auto;
            display: block;
        }

        .pill-row {
            margin-bottom: 12px;
        }

        .pill {
            /* Более чистый блок */
            background: #FBF9F6;
            border: 1px solid #E5E0D8;
            border-radius: 4px;
            padding: 10px 12px;
            text-align: center;
            font-size: 13px;
        }
        .pill-title {
            /* Стиль Label */
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 4px;
        }
        .pill-value {
            /* Стиль Value */
            display: block;
            font-size: 16px;
            font-family: Georgia, serif;
            font-weight: normal;
            color: #1C1B1B;
        }

        .card-vertical {
            /* Более чистый блок */
            background: #FBF9F6;
            border: 1px solid #E5E0D8;
            border-radius: 4px;
            padding: 12px 14px;
            text-align: center;
            font-size: 13px;
            margin-bottom: 12px;
        }
        .card-vertical-title {
            /* Стиль Label */
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 6px;
        }
        .card-vertical-line {
            /* Стиль Value */
            display: block;
            font-size: 13px;
            font-family: Georgia, serif;
            font-weight: normal;
            color: #1C1B1B;
        }

        ul {
            margin: 6px 0 0 18px;
            padding: 0;
        }
        ul li {
            margin-bottom: 4px;
            font-size: 12px;
        }

        .contact-block {
            font-size: 14px;
            line-height: 1.6;
        }
        .contact-name {
            font-size: 18px;
            font-weight: bold;
            color: #1C1B1B;
            margin-bottom: 8px;
        }

        .pricing-card {
            /* Бронзовая полоса более выразительна */
            background: #FBF9F6;
            border: 1px solid #E5E0D8;
            border-left: 6px solid #C29C75;
            border-radius: 4px;
            padding: 20px 24px;
            font-size: 12px;
        }
        .pricing-title {
            /* Бронзовый заголовок */
            text-align: left;
            font-size: 20px;
            font-weight: normal;
            font-family: Georgia, serif;
            color: #C29C75;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #E5E0D8;
        }

        .pricing-row td {
            padding: 4px 0;
        }
        .pricing-label {
            text-align: left;
            font-size: 16px;
        }
        .pricing-value {
            text-align: right;
            font-size: 16px;
            font-weight: 500;
        }

        .pricing-total {
            /* Бронзовый разделитель */
            border-top: 2px solid #C29C75;
            padding-top: 10px;
            margin-top: 10px;
        }

        .pricing-total .pricing-label {
            color: #1C1B1B;
            font-weight: bold;
        }

        .pricing-total .pricing-value {
            color: #1C1B1B; /* Итоговая цена черная/бронзовая */
            font-weight: bold;
        }

        .big-spacer { height: 30px; }
    </style>
</head>
<body>
<div class="page">

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr valign="top">

            <td width="64%" style="padding-right: 26px;">
                <h1>Your Curated Quote</h1>
                <h2>{{ $hotelName }}</h2>

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
                <div style="font-size:24px; margin: 0 0 10px 0; font-family: Georgia, serif; color: #1C1B1B;"> {{ env('APP_NAME') }}Exclusive Perks:</div>

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
                    <div class="contact-name">{{ env('APP_NAME') }} Tours</div>
                    <div>1-800-292-9446 (US)</div>
                    <div>0-800-096-9367 (UK)</div>
                    <div>800-543-7044 (MEX)</div>
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
