@php
    $heroImage = $heroImage ?? \Illuminate\Support\Facades\Storage::url('hotel.webp');
    $secondaryImage = $secondaryImage ?? \Illuminate\Support\Facades\Storage::url('hotel.webp');
    $logoTm = public_path('images/firm-logo.png');
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Quote Details</title>

    <style>
        @page {
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1C1B1B;
            background-color: #FFFFFF;
        }

        .page {
            padding: 30px 40px;
        }

        .header-title {
            margin-bottom: 20px;
        }

        h1 {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 32px;
            font-weight: normal;
            font-style: italic;
            color: #C29C75;
            margin: 0 0 5px 0;
            line-height: 1.1;
        }

        h2 {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 18px;
            font-weight: normal;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #1C1B1B;
            margin: 0 0 8px 0;
        }

        .hotel-address {
            font-size: 10px;
            color: #777;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .hotel-image-main {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
            display: block;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .logo-tm {
            width: 110px;
            height: auto;
            display: block;
        }

        .sidebar {
            padding-left: 20px;
        }

        .info-block {
            background-color: #F9F7F3;
            padding: 12px;
            margin-bottom: 10px;
            border-left: 2px solid #C29C75;
        }

        .info-label {
            display: block;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #C29C75;
            margin-bottom: 3px;
            font-weight: bold;
        }

        .info-value {
            display: block;
            font-family: Georgia, serif;
            font-size: 13px;
            color: #1C1B1B;
        }

        .date-container {
            margin-bottom: 15px;
        }

        .date-box {
            padding: 8px 0;
            border-bottom: 1px solid #EAEAEA;
        }

        .date-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #888;
            margin-bottom: 2px;
            display: block;
        }

        .date-value {
            font-family: Georgia, serif;
            font-size: 14px;
            color: #1C1B1B;
            display: block;
        }

        .section-title {
            font-family: Georgia, serif;
            font-size: 18px;
            color: #1C1B1B;
            margin-bottom: 12px;
            border-bottom: 1px solid #EAEAEA;
            padding-bottom: 6px;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        ul li {
            padding: 4px 0;
            border-bottom: 1px solid #F0F0F0;
            font-size: 11px;
            color: #444;
            position: relative;
            padding-left: 12px;
        }

        ul li:before {
            content: "—";
            position: absolute;
            left: 0;
            color: #C29C75;
        }

        .perks-container {
            margin-top: 20px;
        }

        .pricing-section {
            margin-top: 20px;
            background-color: #F9F7F3;
            padding: 15px;
        }

        .pricing-table {
            width: 100%;
        }

        .pricing-header {
            font-family: Georgia, serif;
            font-size: 20px;
            color: #C29C75;
            margin-bottom: 10px;
            font-style: italic;
        }

        .pricing-row td {
            padding: 4px 0;
            font-size: 12px;
        }

        .pricing-label {
            color: #777;
        }

        .pricing-value {
            text-align: right;
            color: #1C1B1B;
            font-weight: bold;
        }

        .total-row td {
            padding-top: 10px;
            border-top: 1px solid #E5E0D8;
            font-size: 18px;
            font-family: Georgia, serif;
        }

        .total-label {
            color: #1C1B1B;
        }

        .total-value {
            color: #C29C75;
            text-align: right;
        }

        .contact-info {
            margin-top: 20px;
            font-size: 11px;
            color: #777;
            line-height: 1.4;
        }

        .contact-title {
            font-weight: bold;
            color: #1C1B1B;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .footer-logo {
            margin-top: 10px;
        }

        .spacer {
            height: 40px;
        }
    </style>
</head>

<body>
    <div class="page">
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr valign="top">
                <td width="65%">
                    <div class="header-title">
                        <h1>Your Curated Quote</h1>
                        <h2>{{ $hotelName }}</h2>
                        @if ($hotelAddress)
                            <div class="hotel-address">{{ $hotelAddress }}</div>
                        @endif
                    </div>

                    @if ($heroImage)
                        <img src="{{ $heroImage }}" class="hotel-image-main" alt="Hotel image">
                    @endif
                </td>

                <td width="35%" class="sidebar">
                    <div style="text-align:right; margin-bottom: 20px;">
                        <img src="{{ $logoTm }}" alt="<?= env('APP_NAME') ?>" class="logo-tm"
                            style="display: inline-block;">
                    </div>

                    <div class="date-container">
                        @if ($checkin)
                            <div class="date-box">
                                <span class="date-label">Check-In</span>
                                <span class="date-value">{{ $checkin }}</span>
                            </div>
                        @endif
                        @if ($checkout)
                            <div class="date-box">
                                <span class="date-label">Check-Out</span>
                                <span class="date-value">{{ $checkout }}</span>
                            </div>
                        @endif
                    </div>

                    @if ($rateRefundable || $rateMealPlan)
                        <div class="info-block">
                            <span class="info-label">Rate Type</span>
                            @if ($rateRefundable)
                                <span class="info-value">{{ $rateRefundable }}</span>
                            @endif
                            @if ($rateMealPlan)
                                <span class="info-value">{{ $rateMealPlan }}</span>
                            @endif
                        </div>
                    @endif

                    @if ($mainRoomName || $guestInfo)
                        <div class="info-block">
                            <span class="info-label">Accommodation</span>
                            @if ($mainRoomName)
                                <span class="info-value">{{ $mainRoomName }}</span>
                            @endif
                            @if ($guestInfo)
                                <span class="info-value">{{ $guestInfo }}</span>
                            @endif
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="perks-container">
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr valign="top">
                    <td width="60%" style="padding-right: 40px;">
                        <div class="section-title">Exclusive Perks</div>
                        @if (!empty($perks))
                            <ul>
                                @foreach ($perks as $perk)
                                    <li>{{ $perk }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p style="color: #777;">Special perks will be detailed upon final reservation.</p>
                        @endif
                    </td>
                    <td width="40%">
                        @if ($secondaryImage)
                            <img src="{{ $secondaryImage }}" class="hotel-image-main" alt="Resort view">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="pricing-section">
            <div class="pricing-header">Investment Summary</div>
            <table class="pricing-table" cellspacing="0" cellpadding="0">
                <tr class="pricing-row">
                    <td class="pricing-label">Reservation Subtotal</td>
                    <td class="pricing-value">{{ $currency }} {{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr class="pricing-row">
                    <td class="pricing-label">Taxes &amp; Fees</td>
                    <td class="pricing-value">{{ $currency }} {{ number_format($taxesAndFees, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="total-label">Estimated Total</td>
                    <td class="total-value">{{ $currency }} {{ number_format($totalPrice, 2) }}</td>
                </tr>
            </table>
        </div>

        <table width="100%" cellspacing="0" cellpadding="0" class="contact-info">
            <tr>
                <td width="60%">
                    <div class="contact-title">{{ env('APP_NAME') }} Concierge</div>
                    <div>Toll Free: 1-800-292-9446 (US) | 0-800-096-9367 (UK) | 800-543-7044 (MEX)</div>
                    <div>Email: {{ $agencyEmail }}</div>
                </td>
                <td width="40%" align="right">
                    <div class="footer-logo">
                        <img src="{{ $logoTm }}" alt="<?= env('APP_NAME') ?>" class="logo-tm"
                            style="display: inline-block; opacity: 0.5;">
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>