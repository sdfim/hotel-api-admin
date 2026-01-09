<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0px;
        }

        body {
            margin: 0px;
            padding: 0px;
            /* Обновленный стек шрифтов для чистого, современного вида */
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            color: #1C1B1B;
            background-color: #FFFFFF;
            line-height: 1.6;
            font-size: 12px;
        }

        .header-strip {
            height: 8px;
            /* Более толстая полоса */
            background-color: #C29C75;
            /* Бронзовый акцент */
            width: 100%;
        }

        .container {
            padding: 45px 50px;
        }

        .logo-container {
            text-align: right;
            margin-bottom: 30px;
        }

        .logo {
            width: 150px;
        }

        h1,
        h2,
        h3 {
            font-family: 'Georgia', serif;
            /* Serif для заголовков */
            font-weight: normal;
            margin: 0;
            color: #1C1B1B;
        }

        h1 {
            font-size: 36px;
            /* Более крупный H1 */
            font-style: italic;
            margin-bottom: 5px;
            color: #C29C75;
            /* H1 в бронзовом цвете */
        }

        h2 {
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 15px;
        }

        .text-bronze {
            color: #C29C75;
        }

        .hotel-info {
            margin-bottom: 30px;
        }

        .address {
            font-size: 13px;
            color: #888;
            text-transform: none;
            /* Убран uppercase */
            letter-spacing: 0;
            line-height: 1.4;
        }

        .hero-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
            margin-bottom: 30px;
            border: 4px solid #C29C75;
            /* Бронзовая рамка */
        }

        .details-grid {
            width: 100%;
            margin-bottom: 30px;
            border-top: 1px solid #EAEAEA;
            border-bottom: 1px solid #EAEAEA;
            padding: 20px 0;
        }

        .details-grid td {
            text-align: center;
            width: 33.3%;
        }

        .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 5px;
        }

        .value {
            font-size: 16px;
            font-family: 'Georgia', serif;
        }

        .section-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid #C29C75;
            /* Бронзовый разделитель */
            padding-bottom: 8px;
            margin-bottom: 15px;
            color: #C29C75;
        }

        .perks-container {
            background-color: #FBF9F6;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #C29C75;
        }

        .perk-item {
            font-size: 12px;
            margin-bottom: 6px;
        }

        .pricing-table {
            width: 100%;
            margin-bottom: 30px;
        }

        .pricing-table td {
            padding: 8px 0;
            border-bottom: 1px dashed #EAEAEA;
            font-size: 14px;
        }

        .pricing-table .total {
            font-size: 24px;
            font-family: 'Georgia', serif;
            font-weight: bold;
            border-top: 2px solid #C29C75;
            /* Бронзовый верхний разделитель */
            border-bottom: none;
            padding-top: 15px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #888;
        }

        .footer-tagline {
            font-family: 'Georgia', serif;
            font-style: italic;
            font-size: 16px;
            color: #1C1B1B;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="header-strip"></div>
    <div class="container">
        <div class="logo-container">
            <img src="{{ public_path('images/firm-logo.png') }}" class="logo">
        </div>

        @yield('content')

        <div class="footer">
            <div class="footer-tagline">Travel Touched by Tide and Time</div>
            &copy; {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.
        </div>
    </div>
</body>

</html>