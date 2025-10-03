@php
    $payment_url = $payment_url ?? '';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Payment</title>
    <style>
        body { margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4; }
        .container {
            background:#fff;
            border-radius:8px;
            padding:32px;
            width:500px;
            margin:0 auto;
            text-align:center;
        }
        .logo { height:50px; margin-bottom:24px; }
        .pay-btn {
            background:#4f46e5;
            color:#fff;
            text-decoration:none;
            padding:14px 32px;
            border-radius:6px;
            font-weight:bold;
            font-size:18px;
            display:inline-block;
            margin-top:24px;
        }
        .footer { padding:20px; color:#999; font-size:12px; text-align:center; }
    </style>
</head>
<body>
<div style="padding:20px 0; text-align:center;">
    <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="Logo" class="logo">
</div>
<div class="container">
    <h2>Your booking is ready</h2>
    <p>Please pay using the following link:</p>
    <a href="{{ $payment_url }}" class="pay-btn" target="_blank">Pay Now</a>
</div>
<div class="footer">
    Thank you!<br>{{ config('app.name') }}
</div>
</body>
</html>

