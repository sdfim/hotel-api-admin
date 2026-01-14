@php
    $logoTm = public_path('images/emails/firm-logo-pdf.png');
    $staticImage = public_path('images/emails/pdf-confirmation-static-img.jpg');
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Client Confirmation</title>

    @include('pdf.partials.styles')
    <style>
        /* Specific overrides for Client Confirmation */
        h1 {
            font-size: 36px;
        }

        .pricing-header {
            font-size: 24px;
        }

        .total-row td {
            font-size: 20px;
        }

        .welcome-text,
        .welcome-signature {
            font-size: 16px;
        }
    </style>

</head>

<body>
    {{-- PAGE 1: Welcome Greeting --}}
    <div class="page page-break">
        <div style="margin-bottom: 40px;">
            <img src="{{ $logoTm }}" alt="<?= env('APP_NAME') ?>" class="logo-tm">
        </div>

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="55%" valign="top">
                    <div class="welcome-title">Your Journey Awaits</div>
                    <div class="welcome-text">
                        <p>Hello,</p>
                        <p>
                            We are delighted to confirm your upcoming stay with us. On the following page, you will find
                            a summary of your itinerary to help you prepare for a seamless and relaxing experience.
                        </p>
                        <p>
                            We’re here to help you slow down, unwind, and enjoy a stay shaped by the gentle rhythm of
                            tide and time.
                        </p>
                        <div class="welcome-signature">
                            Warm regards,<br>
                            {{ env('APP_NAME') }} Concierge
                        </div>
                    </div>
                </td>
                <td width="45%" class="welcome-image-cell">
                    @if ($staticImage)
                        <img src="{{ $staticImage }}" alt="Welcome" class="welcome-image">
                    @endif
                </td>
            </tr>
        </table>

        <div style="margin-top: 60px;" class="contact-info">
            <div class="contact-title">{{ env('APP_NAME') }} Tours</div>
            <div>Toll Free: 1-800-292-9446 (US) | 0-800-096-9367 (UK) | 800-543-7044 (MEX)</div>
            <div>Email: support@vidanta.com</div>
        </div>
    </div>

    {{-- PAGE 2: Booking Summary --}}
    <div class="page">
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr valign="top">
                <td width="65%">
                    <div class="header-title">
                        <h1>Reservation Details</h1>
                        <h2>{{ $hotelName }}</h2>
                        @if ($hotelAddress)
                            <div class="hotel-address">{{ $hotelAddress }}</div>
                        @endif
                    </div>

                    @if ($heroImageRaw)
                        <img src="{{ $heroImageRaw }}" class="hotel-image-main" alt="Hotel image">
                    @elseif ($heroImage)
                        <img src="{{ $heroImage }}" class="hotel-image-main" alt="Hotel image">
                    @endif
                </td>

                <td width="35%" class="sidebar">
                    <div style="text-align:right; margin-bottom: 30px;">
                        <img src="{{ $logoTm }}" alt="<?= env('APP_NAME') ?>" class="logo-tm"
                            style="display: inline-block;">
                    </div>

                    @if ($confirmationNumber)
                        <div class="confirm-block">
                            <span class="confirm-label">Confirmation Number</span>
                            <span class="confirm-value">{{ $confirmationNumber }}</span>
                        </div>
                    @endif

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
                    <td width="60%" style="padding-right: 30px;">
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
                        @if ($secondaryImageRaw)
                            <img src="{{ $secondaryImageRaw }}" class="hotel-image-main" alt="Resort view">
                        @elseif ($secondaryImage)
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
                    <td class="total-label">Est. Total Price</td>
                    <td class="total-value">{{ $currency }} {{ number_format($totalPrice, 2) }}</td>
                </tr>

                @if ($advisorCommission > 0)
                    <tr class="pricing-row commission-row">
                        <td class="pricing-label">Advisor Commission</td>
                        <td class="pricing-value">{{ $currency }} {{ number_format($advisorCommission, 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <table width="100%" cellspacing="0" cellpadding="0" class="contact-info">
            <tr>
                <td width="60%">
                    <div class="contact-title">{{ env('APP_NAME') }} Concierge</div>
                    <div>Toll Free: 1-800-292-9446 (US) | 0-800-096-9367 (UK) | 800-543-7044 (MEX)</div>
                    <div>Email: support@vidanta.com</div>
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