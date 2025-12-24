@php
    // Dynamic hotel name (fallback for safety)
    $hotelName = $hotelName ?? '[Hotel Name]';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> {{ env('APP_NAME') }}– Advisor Confirmation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- Serif font fallback for emails --}}
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
            font-weight: 300;
        }
    </style>
</head>
<body>

{{-- Full width wrapper --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding: 24px 12px;">

            {{-- Main card --}}
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width: 680px; overflow: hidden;">

                <tr>
                    <td
                        bgcolor="#E9EDE7"
                        background="{{ asset('images/email-backgrounds/wave-bg.png') }}"
                        style="
                            padding: 40px 48px 72px 48px;
                            font-family: 'Playfair Display', Georgia, serif;
                            color: #263A3A;
                            font-size: 16px;
                            line-height: 1.6;
                            background-image: url('{{ asset('images/email-backgrounds/wave-bg.png') }}');
                            background-repeat: no-repeat;
                            background-position: center top;
                            background-size: cover;
                        "
                    >


                    {{-- Logo row --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="margin-bottom: 48px;">
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

                        {{-- Main text --}}
                        <p>Hello,</p>

                        <p>
                            We are delighted to confirm your client’s upcoming stay at
                            <strong>{{ $hotelName }}</strong>. Attached, you’ll find the booking confirmation,
                            including a summary of their stay details along with a clear breakdown of your commission.
                        </p>

                        <p>
                            Should you have any questions or additional requests, our concierge team will be happy
                            to assist. Simply reply to this email or contact us at
                            <a href="mailto:support@vidanta.com"
                               style="color:#263A3A; text-decoration:underline;">
                                support@vidanta.com
                            </a>.
                        </p>

                        <p>
                            We’re delighted to help your clients slow down, unwind, and experience travel touched
                            by tide and time.
                        </p>

                        <p style="margin-top: 40px;">
                            Warm regards,<br>
                             {{ env('APP_NAME') }}Concierge
                        </p>

                        {{-- Bottom "Thank you" --}}
                        <div style="text-align:center; margin-top: 64px;">
                            <div style="font-size: 26px; line-height: 1.4;">
                                Thank you so much!
                            </div>
                            <div style="font-size: 16px; margin-top: 4px;">
                                 <?= env('APP_NAME'); ?>
                            </div>
                        </div>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
