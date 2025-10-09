@php
    $payment_url = $payment_url ?? '';
    $hotelName = $hotelName ?? '[Hotel Name]';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Terra Mare Hotel - Payment Confirmation</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Playfair Display', Georgia, serif !important;
            font-size: calc(1em * 1.2);
            background: #f4f4f4;
        }
        .container {
            border-radius: 8px;
            padding: 40px 60px 80px 60px;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Playfair Display', Georgia, serif !important;
            font-size: inherit;
            font-weight: 300;
            background: #fff url('{{ Storage::url('images/email-book-confirmation/bg-pay.png') }}') no-repeat center top;
            background-size: cover;
            color: #000;
        }
        p {
            line-height: 1.6;
            margin-bottom: 32px;
            font-size: 20px;
        }
        .button {
            display: inline-block;
            padding: 16px 32px;
            background: #19332c;
            color: #fff;
            border-radius: 30px;
            font-size: 33px;
            text-decoration: none;
            margin-top: 32px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Logo aligned right using table for email compatibility -->
    <table role="presentation" width="100%" style="margin-bottom:40px;">
        <tr>
            <td align="right">
                <img src="{{ asset('build/images/logo-tm.png') }}" alt="Terra Mare Hotel" width="108" height="76" style="display: inline-block; border: 0;">
            </td>
        </tr>
    </table>
    <p>Hello,</p>
    <p>
        We are delighted to finalize your upcoming stay with <strong>{{ $hotelName }}</strong>. Below you’ll find a button that will take you to our payments page. We ask that you provide a payment method within 24 hours to confirm the booking. You will also find attached the confirmation packet with all the details of your trip.
    </p>
    <p>
        Should you have any questions, dietary preferences, or additional requests, our concierge team will be happy to assist you. We look forward to arranging an unforgettable escape for you.
    </p>
    <p>
        Warm regards,<br>
        Terra Mare Concierge
    </p>
    <table role="presentation" width="100%" style="margin-top:32px; margin-bottom:32px;">
        <tr>
            <td align="center">
                <a href="{{ $payment_url }}" class="button"
                   style="display:inline-block;
                   padding:25px 45px;
                   background:#19332c;
                   color:#fff;
                   border-radius:30px;
                   font-size:35px;
                   text-decoration:none;"
                >Pay Now</a>
            </td>
        </tr>
    </table>
    <div style="text-align:center; font-size:18px; margin:45px 0 0;">
        Thank you so much!<br>Terra Mare
    </div>
</div>
</body>
</html>
