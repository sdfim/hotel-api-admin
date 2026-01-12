@php
    use Illuminate\Support\Facades\Storage;

    /** Base data from pdfData (for both pages) */
    $hotelName    = $hotelData['name']    ?? 'Hotel Name';
    $hotelAddress = $hotelData['address'] ?? '';

    $heroImage = $hotelPhotoPath ?? Storage::url('hotel.webp');
    $secondaryImage = $roomPhotoPath ?? Storage::url('hotel.webp');

    $totalNet    = $total_net   ?? 0;
    $totalTax    = $total_tax   ?? 0;
    $totalFees   = $total_fees  ?? 0;
    $totalPrice  = $total_price ?? ($totalNet + $totalTax + $totalFees);
    $subtotal    = $subtotal    ?? ($totalPrice - $totalTax - $totalFees);

    $agencyName  = $agency['booking_agent']       ?? env('APP_NAME') . ' Tours';
    $agencyEmail = $agency['booking_agent_email'] ?? 'support@vidanta.com';

    /** Optional fields */
    $checkin            = $checkin            ?? null;
    $checkout           = $checkout           ?? null;
    $guestInfo          = $guest_info         ?? null;
    $mainRoomName       = $main_room_name     ?? null;
    $rateRefundable     = $rate_refundable    ?? null;
    $rateMealPlan       = $rate_meal_plan     ?? null;
    $perks              = $perks              ?? [];
    $currency           = $currency           ?? 'USD';
    $taxesAndFees       = $taxes_and_fees     ?? ($totalTax + $totalFees);
    $confirmationNumber = $confirmation_number ?? null;

    // Static assets
    $logoTm      = asset('images/emails/firm-logo-pdf.png');
    $bgWave      = asset('images/email-backgrounds/wave-bg.png'); // Сохранено, но стиль игнорируется
    $staticImage = asset('images/emails/pdf-confirmation-static-img.png');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Confirmation</title>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 28px;
            font-weight: 500;
        }

        .italic-title {
            font-style: italic;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 32px;
            font-weight: normal;
            margin-bottom: 20px;
        }

        .page {
            padding: 40px;
        }

        .page-wave {
            /* Удаляем старый фон волны, чтобы использовать чистый белый фон */
            background-image: none !important;
            background-color: #FFFFFF !important;

            /* Force the container to fill the whole PDF page */
            min-height: 1040px;
            box-sizing: border-box;
        }

        h1 {
            /* Бронзовый, крупный, serif заголовок */
            font-size: 36px;
            font-weight: normal;
            color: #C29C75;
            font-family: Georgia, serif;
            margin: 0 0 5px 0;
        }

        .hotel-address {
            font-size: 13px;
            color: #888;
            margin-bottom: 20px;
        }

        .hero-image-static {
            width: 460px;
            height: auto;
            display: block;
        }

        .hotel-image-main {
            width: 100%;
            height: auto;
            display: block;
            border: 1px solid #EAEAEA;
        }

        .logo-tm-big {
            width: 200px;
            height: auto;
            display: block;
        }

        .logo-tm {
            width: 78px;
            height: auto;
            display: block;
        }

        .sidebar-block {
            text-align: right;
        }

        .pill-wide {
            /* Бронзовый фон для главного блока */
            background: #C29C75;
            color: #FFFFFF;
            border-radius: 4px;
            padding: 12px 14px;
            font-size: 14px;
            text-align: center;
            margin-bottom: 12px;
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

        .pill-wide .pill-title {
            color: #FBF9F6;
            letter-spacing: 2px;
        }

        .pill-wide .pill-value {
            color: #FFFFFF;
            font-size: 18px;
            font-weight: bold;
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
            /* Бронзовый акцент */
            background: #FBF9F6;
            border: 1px solid #C29C75;
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
            color: #1C1B1B;
            font-weight: bold;
        }

        .big-spacer { height: 30px; }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

{{-- PAGE 1: welcome page, matching the Figma layout --}}
<div class="page page-break">
    <table width="100%" cellspacing="0" cellpadding="0">
        {{-- Top: left logo, right hero image --}}
        <tr valign="middle">
            <td width="32%" style="padding-right: 30px;">
                {{-- Logo --}}
                <img style="margin-top: 350px;" src="{{ $logoTm }}" alt=" <?= env('APP_NAME'); ?>" class="logo-tm-big">
            </td>

            <td width="68%">
                @if($staticImage)
                    <img src="{{ $staticImage }}" alt="Welcome image" class="hero-image-static">
                @endif
            </td>
        </tr>

        {{-- Bottom: left contacts, right text --}}
        <tr valign="top">
            <td width="32%" style="padding-right: 30px;">
                <div style="margin-top: 400px; line-height: 1.6;">
                    <div style="font-size: 20px; font-weight: bold; color: #1C1B1B;"> {{ env('APP_NAME') }}Tours</div>
                    <div style="font-size: 14px;">1-800-292-9446 (US)</div>
                    <div style="font-size: 14px;">0-800-096-9367 (UK)</div>
                    <div style="font-size: 14px;">800-543-7044 (MEX)</div>
                    <div style="font-size: 14px;">support@vidanta.com</div>
                </div>
            </td>

            <td width="68%">
                <div style="margin-top: 40px; font-size: 20px; max-width: 420px; line-height: 1.7; font-family: Georgia, serif;">
                    <p style="font-style: italic; color: #C29C75;">Hello,</p>

                    <p>
                        We are delighted to confirm your upcoming stay with us. On the
                        next page you’ll find a summary of your itinerary to help you
                        prepare for a seamless and relaxing experience.
                    </p>

                    <p>
                        We’re here to help you slow down, unwind, and enjoy a stay shaped
                        by the gentle rhythm of tide and time.
                    </p>

                    <p style="margin-top: 26px;">
                        Warm regards,<br>
                        {{ env('APP_NAME') }}Concierge
                    </p>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- PAGE 2: summary – same layout as advisor, but WITHOUT Advisor Commission --}}
<div class="page page-wave">

    {{-- TOP: left (title + main image), right (logo + green blocks) --}}
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
                <div class="sidebar-block">
                    <div style="text-align:right; margin-bottom: 24px;">
                        <img src="{{ $logoTm }}" alt=" <?= env('APP_NAME'); ?>" class="logo-tm">
                    </div>

                    @if($confirmationNumber)
                        <div class="pill-wide">
                            <span class="pill-title">Confirmation Number</span>
                            <span class="pill-value">{{ $confirmationNumber }}</span>
                        </div>
                    @endif

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
                </div>
            </td>
        </tr>
    </table>

    <div class="big-spacer"></div>

    {{-- MIDDLE: perks on the left, secondary image on the right --}}
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr valign="top">
            <td width="60%" style="padding-right:26px;">
                <div style="font-size: 24px; margin: 0 0 10px 0; font-family: Georgia, serif; color: #1C1B1B;"> {{ env('APP_NAME') }}Exclusive Perks:</div>

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

    {{-- BOTTOM: contacts on the left, pricing on the right (WITHOUT Advisor Commission) --}}
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 40px;">
        <tr valign="top">
            <td width="50%" style="padding-right:26px;">
                <div class="contact-block">
                    <div class="contact-name"> {{ env('APP_NAME') }}Tours</div>
                    <div>1-800-292-9446 (US)</div>
                    <div>0-800-096-9367 (UK)</div>
                    <div>800-543-7044 (MEX)</div>
                    <div>{{ $agencyEmail }}</div>
                </div>
            </td>

            <td width="50%" align="right">
                <div class="pricing-card" style="width: 360px;">
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
                        {{-- Advisor Commission is intentionally NOT shown in client PDF --}}
                    </table>

                </div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
