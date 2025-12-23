@php
    $payment_url = $payment_url ?? '#';
    $hotelName   = $hotelName ?? '[Hotel Name]';
@endphp

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> <?= env('APP_NAME'); ?> – Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- Serif font for email --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        /* Basic email reset */
        body {
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
        }
        img {
            border: 0;
            display: block;
            line-height: 0;
        }
        p {
            margin: 0 0 28px 0;
        }

        @media only screen and (max-width: 480px) {
            .tm-payment-bottom {
                padding-top: 0 !important;
                padding-left: 20px !important;
                padding-right: 20px !important;
                background-size: 180% auto !important;
            }
        }
    </style>
</head>
<body>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding: 24px 12px;">

            {{-- Main card --}}
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width: 680px; overflow: hidden; font-weight: 300;">

                {{-- Top green block --}}
                <tr>
                    <td bgcolor="#C7D5C7"
                        style="padding: 40px 48px 24px 48px;
                               font-family: 'Playfair Display', Georgia, serif;
                               color: #263A3A;
                               font-size: 16px;
                               line-height: 1.6;">

                        {{-- Logo --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="margin-bottom: 40px;">
                            <tr>
                                <td align="right">
                                    <img
                                        src="{{ asset('images/terra-mare-logo.png') }}"
                                        alt=" <?= env('APP_NAME'); ?>"
                                        width="238"
                                        style="height:auto;"
                                    >
                                </td>
                            </tr>
                        </table>

                        <p>Hello,</p>

                        <p>
                            We’re delighted to finalize the arrangements for your upcoming stay at
                            <strong>{{ $hotelName }}</strong>. Attached, you’ll find a summary of the proposed
                            details for your review.
                        </p>

                        <p>
                            To complete your booking, please use the button below to access our secure payments
                            page and provide your payment details within 24 hours.
                        </p>

                        <p>
                            We look forward to curating an unforgettable escape for you.
                        </p>

                        <p style="margin-top: 40px;">
                            Warm regards,<br>
                             <?= env('APP_NAME'); ?> Concierge
                        </p>

                    </td>
                </tr>

                {{-- Bottom light section with wave divider as background --}}
                <tr>
                    <td
                        class="tm-payment-bottom"
                        bgcolor="#E9EDE7"
                        background="{{ asset('images/email-backgrounds/wave-divider.png') }}"
                        style="
                            font-family: 'Playfair Display', Georgia, serif;
                            color:#263A3A;
                            text-align:center;
                            padding: 0 32px 48px 32px;
                            background-image: url('{{ asset('images/email-backgrounds/wave-divider.png') }}');
                            background-repeat: no-repeat;
                            background-position: center top;
                            background-size: 100% auto;
                        "
                    >

                        {{-- Pay Now button --}}
                        <table role="presentation" align="center" cellpadding="0" cellspacing="0"
                               style="margin-bottom: 40px;">
                            <tr>
                                <td
                                    align="center"
                                    bgcolor="#263A3A"
                                    style="
                                        background-color:#263A3A;
                                        border-radius: 18px;
                                        padding: 16px 32px;
                                    "
                                >
                                    <a href="{{ $payment_url }}"
                                       style="
                                           font-size: 20px;
                                           color: #FFFFFF;
                                           text-decoration: none;
                                           display: inline-block;
                                           font-family: 'Playfair Display', Georgia, serif;
                                       ">
                                        Pay Now
                                    </a>
                                </td>
                            </tr>
                        </table>

                        {{-- Bottom "Thank you" text --}}
                        <div style="font-size: 26px; line-height: 1.4; padding-top: 40px;">
                            Thank you so much!
                        </div>
                        <div style="font-size: 16px; margin-top: 4px;">
                             <?= env('APP_NAME'); ?>
                        </div>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
