@php
    use Illuminate\Support\Facades\Storage;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: rgb(244, 240, 237);
            background-image: url('{{ Storage::url('images/bg-pdf-tm.png') }}');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #000000;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .container {
            margin: 60px 50px 50px 60px;
        }

        .row {
            width: 100%;
            margin-bottom: 32px;
            margin-top: 32px;
            overflow: hidden;
        }
        .col-1 {
            width: 25%;
            float: left;
            box-sizing: border-box;
            padding-right: 20px;
        }
        .col-2 {
            width: 70%;
            float: left;
            box-sizing: border-box;
        }

        .row-header {
            width: 100%;
            margin-bottom: 32px;
            display: table;
            table-layout: fixed;
        }
        .col-1-cell,
        .col-2-cell {
            float: none;
            display: table-cell;
            vertical-align: bottom;
            box-sizing: border-box;
        }
        .col-1-cell {
            width: 25%;
            padding-right: 20px;
        }
        .col-2-cell {
            width: 75%;
            padding-left: 20px;
        }

        .logo {
            width: 130px;
            display: block;
        }
        .hotel-photo {
            width: 98%;
            display: block;
        }

        .greeting-text {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #000000;
        }
        .greeting-text p {
            margin: 0 0 10px 0;
        }
        .agent-block {
            font-size: 11px;
        }
        .agent-block strong {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }
        .agent-block p {
            margin: 0;
            line-height: 1.6;
        }
        .commission-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #CCCCCC;
            font-size: 14px;
            margin-bottom: 8px;
            table-layout: fixed;
            margin-left: 0;
        }

        .commission-table th,
        .commission-table td {
            border: 1px solid #CCCCCC;
            padding: 8px;
            text-align: left;
        }

        .commission-table th {
            font-weight: normal;
            width: 65%;
            padding-left: 10px;
            color: #444444;
        }

        .commission-table td {
            text-align: right;
            width: 35%;
            padding-right: 10px;
            color: #444444;
        }

        .commission-table th,
        .commission-table td {
            background-color: transparent;
        }

        .commission-table tbody tr:last-child th,
        .commission-table tbody tr:last-child td {
            font-weight: bold;
            background-color: #E6F0E6;
            color: #000000;
        }

        .hotel-summary h2 {
            font-family: 'Bodoni Moda', 'Times New Roman', serif;
            font-size: 24px;
            font-weight: normal;
            margin: 0 0 15px 0;
        }

        .col-2-content-shift {
            padding-left: 0 !important;
        }

        .row-tight {
            margin-top: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Секция 1: Логотип и Фото -->
    <div class="row-header">
        <div class="col-1-cell">
            <img src="{{ Storage::url('images/logo-tm.png') }}" class="logo" alt="Terra Mare">
        </div>
        <div class="col-2-cell">
            <img src="{{ $hotelPhotoPath ?? Storage::url($hotel->product?->hero_image) }}" class="hotel-photo" alt="Hotel Photo">
        </div>
    </div>

    <!-- Секция 2: Адрес Отеля (Сдвинут влево) -->
    <div class="row clearfix row-tight">
        <div class="col-1">
        </div>
        <div class="col-2 col-2-content-shift">
            <div style="margin-top: 10px;">
                <strong>{{ $hotelData['name'] ?? 'Grand Velas Riviera Maya' }}</strong><br>
                {{ $hotelData['address'] ?? 'Carretera Cancun Tulum, Playa del Carmen/Playacar, Quintana Roo, MX' }}
            </div>
        </div>
    </div>

    <!-- Секция 3: Приветствие (Сдвинуто влево) -->
    <div class="row clearfix">
        <div class="col-1">
        </div>
        <div class="col-2 col-2-content-shift">
            <div class="greeting-text">
                <p style="margin-top: 20px;">Dear {{ $customerName ?? 'Mr Therese Stroman' }},</p>
                <p>We are delighted to confirm your upcoming stay with us.<br>
                    Below is a summary of your itinerary to help you prepare for
                    a seamless and relaxing experience.</p>
                <p>Should you have any questions, dietary preferences, or
                    additional requests, our concierge team will be happy to
                    assist you. Simply reply to this email, or contact us at
                    {{ $agency['booking_agent_email'] ?? 'test-api-user@terramare.com' }}.</p>
                <p>We look forward to welcoming you soon for an unforgettable
                    escape in Mexico.</p>
                <p>Warm regards, Terra Mare Concierge</p>
            </div>
        </div>
    </div>

    <!-- Секция 4: Заголовок Hotel Summary (Сдвинут влево) -->
    <div class="row clearfix row-tight">
        <div class="col-1">
        </div>
        <div class="col-2 col-2-content-shift">
            <div class="hotel-summary">
                <h2>Hotel Summary (Paid In USD):</h2>
            </div>
        </div>
    </div>

    <!-- Секция 5: Booking Agent и Таблица (Таблица сдвинута влево) -->
    <div class="row clearfix row-tight">
        <div class="col-1">
            <div class="agent-block">
                <strong>Booking Agent</strong>
                <p>
                    {{ $agency['booking_agent'] ?? 'test-api-user' }}<br>
                    {{ $agency['booking_agent_email'] ?? 'test-api-user@terramare.com' }}
                </p>
            </div>
        </div>
        <div class="col-2 col-2-content-shift">
            <table class="commission-table">
                <tbody>
                <tr>
                    <th>Total Net</th>
                    <td>${{ number_format($total_net ?? 6086.00, 2) }}</td>
                </tr>
                <tr>
                    <th>Total Tax</th>
                    <td>${{ number_format($total_tax ?? 0.00, 2) }}</td>
                </tr>
                <tr>
                    <th>Total Fees</th>
                    <td>${{ number_format($total_fees ?? 0.00, 2) }}</td>
                </tr>
                <tr>
                    <th>Total Price</th>
                    <td><strong>${{ number_format($total_price ?? 6086.00, 2) }}</strong></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
