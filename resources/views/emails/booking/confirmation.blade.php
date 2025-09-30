@php
    use Illuminate\Support\Arr;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;

    $tripName = Arr::get($hotel, 'product.name', 'Beautiful Bali with Malaysia');

    // Даты из оригинального запроса
    // Форматируем даты как в изображении (например, 29 July 2022)
    $checkin = Carbon::parse(Arr::get($searchRequest, 'checkin', now()))->format('d F Y');
    $checkout = Carbon::parse(Arr::get($searchRequest, 'checkout', now()->addDays(7)))->format('d F Y');
    $displayDate = Carbon::now()->format('d F Y'); // Дата, когда была сделана бронь/отправлено письмо

    // Подсчет гостей (как в оригинале)
    $adultsCount = collect(Arr::get($searchRequest, 'occupancy', []))->sum('adults') ?: 2;
    $childrenCount = collect(Arr::get($searchRequest, 'occupancy', []))->sum(fn($o) => count(Arr::get($o, 'children_ages', []))) ?: 1;
    $guestsCount = $adultsCount + $childrenCount;

    // Сумма и валюта (как в оригинале)
    $grandTotal = Arr::get($rooms, '0.total_price', 1200);
    $currency = Arr::get($rooms, '0.currency', 'USD');

    // --- Дополнительные переменные, необходимые для дизайна по картинке ---
    $bookingConfirmation = Arr::get($bookingMeta->booking_item_data, 'bookingId', 'BS-58678'); // Используем $booking, если доступен
    $bookedBy = Arr::get($bookingMeta->booking_item_data, 'main_guest.Surname', 'Frances') . ' '
        . Arr::get($bookingMeta->booking_item_data, 'main_guest.GivenName', 'Guerrero'); // Используем реальное имя пользователя, если доступно
    $paymentMethod = 'Credit card'; // Используем реальный метод оплаты

    if ($hotel?->product?->hero_image) {
            $imagePath = $hotel->product->hero_image;
            if (config('filesystems.default') === 's3') {
                $heroImageUrl = rtrim(config('image_sources.sources.s3'), '/').'/'.ltrim($imagePath, '/');
            } else {
                $heroImageUrl = rtrim(config('image_sources.sources.local'), '/').'/storage/'.ltrim($imagePath, '/');
            }
        } else {
            $heroImageUrl = 'https://placehold.co/750x300/F1F5F9/273549?text=Your+Trip+Image';
        }

    $downloadUrl = '#'; // URL для загрузки PDF (замените на реальный)
    $shareUrl = '#'; // URL для функции "Поделиться"
@endphp

    <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Confirmation - {{ $bookingConfirmation }}</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f4f4f4;
            color: #1f2937;
        }

        .wrapper {
            padding: 20px 0;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            width: 90%;
            max-width: 750px;
            margin: 0 auto;
            overflow: hidden;
        }

        .hero-image {
            width: 100%;
            height: auto;
            display: block;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .content {
            padding: 32px 28px;
            text-align: center;
        }

        .congrats-title {
            font-size: 32px;
            color: #111827;
            margin: 12px 0 6px;
            font-weight: 700;
        }

        .trip-name {
            font-size: 20px;
            color: #4f46e5;
            margin: 18px 0 28px;
            font-weight: 600;
        }

        .detail-grid {
            display: flex;
            flex-wrap: wrap;
            text-align: left;
        }

        .detail-item {
            width: 50%;
            display: flex;
            align-items: center;
        }

        .detail-label {
            color: #6b7280;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            width: 50%;
        }

        .detail-value {
            color: #111827;
            font-size: 15px;
            font-weight: 600;
            width: 50%;
        }

        /* Блок комнат */
        .room-section-title {
            width: 100%;
            font-size: 16px;
            color: #111827;
            margin: 24px 0 12px;
            font-weight: 600;
            text-align: left;
        }

        .room-block {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 8px 0 16px;
            text-align: left;
            width: 100%;
            box-sizing: border-box;
            line-height: 1.5;
        }

        .room-block strong {
            color: #4f46e5;
        }

        .room-price-details {
            color: #6b7280;
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }

        .total-price-row {
            width: 100%;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
            margin-top: 12px;
        }

        .footer-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            padding: 18px 0 0;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.25s ease;
            font-size: 14px;
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #4f46e5;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        /* Адаптивность */
        @media (max-width: 600px) {
            .content {
                padding: 24px 20px;
            }

            .detail-item {
                width: 100%;
            }

            .detail-label,
            .detail-value {
                width: auto;
                flex-grow: 1;
            }

            .footer-actions {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
            }

            .icon {
                filter: grayscale(100%) brightness(0) invert(40%);
                opacity: 0.7;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <!-- Блок изображения -->
        <img src="{{ $heroImageUrl }}" alt="Trip Destination" class="hero-image">

        <div class="content">
            <p style="margin: 0;">
                <span style="font-size: 24px;">🎉</span>
                <span class="congrats-title">Congratulations!</span>
                <span style="font-size: 24px;">🎉</span>
            </p>
            <p style="margin: 5px 0 10px 0; color: #555;">Your trip has been booked</p>
            <h2 class="trip-name">{{ $tripName }}</h2>

            <div class="detail-grid">
                <div class="detail-item">
                    <p class="detail-label">
                        <img src="{{ asset('build/images/email-book-confirmation/id-badge.svg') }}" alt="Booking ID" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Booking ID:
                    </p>
                    <p class="detail-value">{{ $bookingConfirmation }}</p>
                </div>

                <div class="detail-item">
                    <p class="detail-label">
                        <img src="{{ asset('build/images/email-book-confirmation/calendar-check.svg') }}" alt="Date Booked" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Date Booked:
                    </p>
                    <p class="detail-value">{{ $displayDate }}</p>
                </div>

                <div class="detail-item">
                    <p class="detail-label">
                        <img src="{{ asset('build/images/email-book-confirmation/user.svg') }}" alt="Booked by" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Booked by:
                    </p>
                    <p class="detail-value">{{ $bookedBy }}</p>
                </div>

                <div class="detail-item">
                    <p class="detail-label">
                        <img src="{{ asset('build/images/email-book-confirmation/calendar.svg') }}" alt="Tour Date" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Tour Date:
                    </p>
                    <p class="detail-value">{{ $checkin }} - {{ $checkout }}</p>
                </div>

                <div class="detail-item">
                    <p class="detail-label">
                        <img src="{{ asset('build/images/email-book-confirmation/credit-card.svg') }}" alt="Payment Method" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Payment Method:
                    </p>
                    <p class="detail-value">{{ $paymentMethod }}</p>
                </div>

                <div class="detail-item">
                    <p class="detail-label">
                        <img src="{{ asset('build/images/email-book-confirmation/users.svg') }}" alt="Guests" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Guests:
                    </p>
                    <p class="detail-value">{{ $guestsCount }}</p>
                </div>

                <p class="room-section-title">Rooms & Rates</p>
                @foreach($rooms as $k => $room)
                    @php
                        $occupancy = Arr::get($searchRequest, "occupancy.$k");
                    @endphp
                    <div class="detail-item" style="width: 100%; padding: 0;">
                        <div class="room-block">
                            <p style="margin: 0 0 8px 0;"><strong>{{ Arr::get($room, 'room_name', 'Room ' . ($k + 1)) }}</strong></p>
                            @if(isset($room['rates']) && is_array($room['rates']))
                                @foreach($room['rates'] as $rate)
                                    <div style="margin-bottom: 10px;">
                                        <p style="margin: 0;">
                                            <span style="font-size: 14px; color: #555;">Rate:</span> {{ Arr::get($rate, 'rate_code') }}
                                        </p>
                                        <p style="margin: 0;">
                                            <span style="font-size: 14px; color: #555;">Price:</span> {{ number_format(Arr::get($rate, 'total_price', 0), 2) }} {{ Arr::get($rate, 'currency', $currency) }}
                                        </p>
                                        <span class="room-price-details">
                                            (Net: {{ number_format(Arr::get($rate, 'total_net', 0), 2) }},
                                            Tax: {{ number_format(Arr::get($rate, 'total_tax', 0), 2) }},
                                            Fees: {{ number_format(Arr::get($rate, 'total_fees', 0), 2) }})
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <p style="margin: 0;">
                                    <span style="font-size: 14px; color: #555;">Rate:</span> {{ Arr::get($room, 'rate_code') }}
                                </p>
                                <p style="margin: 0;">
                                    <span style="font-size: 14px; color: #555;">Price:</span> {{ number_format(Arr::get($room, 'total_price', 0), 2) }} {{ Arr::get($room, 'currency', $currency) }}
                                </p>
                                <span class="room-price-details">
                                    (Net: {{ number_format(Arr::get($room, 'total_net', 0), 2) }},
                                    Tax: {{ number_format(Arr::get($room, 'total_tax', 0), 2) }},
                                    Fees: {{ number_format(Arr::get($room, 'total_fees', 0), 2) }})
                                </span>
                            @endif
                            @if($occupancy)
                                <p style="margin: 5px 0;">
                                    <span style="font-size: 14px; color: #555;">Guests:</span>
                                    {{ Arr::get($occupancy, 'adults', 0) }} Adults
                                    @if(count(Arr::get($occupancy, 'children_ages', [])) > 0)
                                        , {{ count(Arr::get($occupancy, 'children_ages', [])) }} Children
                                        (ages: {{ implode(', ', Arr::get($occupancy, 'children_ages', [])) }})
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
                <div class="detail-item total-price-row">
                    <p class="detail-label" style="font-size: 16px;">
                        <img src="{{ asset('build/images/email-book-confirmation/dollar-sign.svg') }}" alt="Total Price" class="icon" style="width:20px; height:20px; margin-right:8px; vertical-align: middle;"> Total Price:
                    </p>
                    <p class="detail-value" style="color: #4f46e5; font-size: 18px;">
                        {{ number_format($grandTotal, 2) }} {{ $currency }}
                    </p>
                </div>

            </div>

            <div class="footer-actions">
                <a href="{{ $shareUrl }}" class="btn btn-secondary">
                    <img src="{{ asset('build/images/email-book-confirmation/user.svg') }}" alt="Share" class="icon" style="width:18px; height:18px; margin-right:5px; vertical-align: middle;"> Share
                </a>
                <a href="{{ $downloadUrl }}" class="btn btn-primary">
                    <img src="{{ asset('build/images/email-book-confirmation/calendar-check.svg') }}" alt="Download PDF" class="icon" style="width:18px; height:18px; margin-right:5px; vertical-align: middle;"> Download PDF
                </a>
            </div>

        </div>
    </div>
</div>
</body>
</html>
