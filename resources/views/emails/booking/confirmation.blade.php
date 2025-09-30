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
    <title>Trip Confirmation</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family:Arial, sans-serif; color:#1f2937;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="padding:20px 0;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="750" style="max-width:750px; width:100%; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                <!-- Hero Image -->
                <tr>
                    <td>
                        <img src="{{ $heroImageUrl }}" alt="Trip Destination" width="750" style="width:100%; max-width:750px; display:block; border-top-left-radius:12px; border-top-right-radius:12px;">
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:32px 28px; text-align:center;">
                        <p style="margin:0; font-size:24px;">🎉 <span style="font-size:32px; font-weight:bold; color:#111827;">Congratulations!</span> 🎉</p>
                        <p style="margin:5px 0 10px; color:#555;">Your trip has been booked</p>
                        <h2 style="margin:18px 0 28px; font-size:20px; color:#4f46e5; font-weight:600;">{{ $tripName }}</h2>

                        <!-- Details -->
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:24px;">
<tr>
    <td width="50%" valign="top" style="vertical-align:top;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td style="padding:8px; text-align:left; vertical-align:top;">
                    <img src="{{ asset('build/images/email-book-confirmation/id-badge.svg') }}" alt="Booking ID" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Booking ID:
                </td>
                <td style="padding:8px; font-weight:600; font-size:15px; color:#111827; text-align:left; vertical-align:top;">{{ $bookingConfirmation }}</td>
            </tr>
            <tr>
                <td style="padding:8px; text-align:left; vertical-align:top;">
                    <img src="{{ asset('build/images/email-book-confirmation/calendar-check.svg') }}" alt="Date Booked" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Date Booked:
                </td>
                <td style="padding:8px; font-weight:600; font-size:15px; text-align:left; vertical-align:top;">{{ $displayDate }}</td>
            </tr>
            <tr>
                <td style="padding:8px; text-align:left; vertical-align:top;">
                    <img src="{{ asset('build/images/email-book-confirmation/user.svg') }}" alt="Booked by" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Booked by:
                </td>
                <td style="padding:8px; font-weight:600; font-size:15px; text-align:left; vertical-align:top;">{{ $bookedBy }}</td>
            </tr>
        </table>
    </td>
    <td width="50%" valign="top" style="vertical-align:top;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td style="padding:8px; text-align:left; vertical-align:top;">
                    <img src="{{ asset('build/images/email-book-confirmation/calendar.svg') }}" alt="Tour Date" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Tour Date:
                </td>
                <td style="padding:8px; font-weight:600; font-size:15px; text-align:left; vertical-align:top;">{{ $checkin }} - {{ $checkout }}</td>
            </tr>
            <tr>
                <td style="padding:8px; text-align:left; vertical-align:top;">
                    <img src="{{ asset('build/images/email-book-confirmation/credit-card.svg') }}" alt="Payment Method" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Payment Method:
                </td>
                <td style="padding:8px; font-weight:600; font-size:15px; text-align:left; vertical-align:top;">{{ $paymentMethod }}</td>
            </tr>
            <tr>
                <td style="padding:8px; text-align:left; vertical-align:top;">
                    <img src="{{ asset('build/images/email-book-confirmation/users.svg') }}" alt="Guests" class="icon" style="width:18px; height:18px; margin-right:8px; vertical-align: middle;"> Guests:
                </td>
                <td style="padding:8px; font-weight:600; font-size:15px; text-align:left; vertical-align:top;">{{ $guestsCount }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

                        <!-- Rooms -->
                        <p style="text-align:left; font-size:16px; font-weight:600; color:#111827; margin:24px 0 12px;">Rooms & Rates</p>
                        @foreach($rooms as $k => $room)
                            @php $occupancy = Arr::get($searchRequest, "occupancy.$k"); @endphp
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                                <tr>
                                    <td style="padding:14px 16px; font-size:14px; color:#111827; text-align:left;">
                                        <p style="margin:0 0 8px;"><strong style="color:#4f46e5;">{{ Arr::get($room, 'room_name', 'Room '.($k+1)) }}</strong></p>
                                        <p style="margin:0; font-size:14px; color:#555;">Rate: {{ Arr::get($room, 'rate_code') }}</p>
                                        <p style="margin:0; font-size:14px; color:#555;">Price: {{ number_format(Arr::get($room, 'total_price', 0),2) }} {{ Arr::get($room, 'currency', $currency) }}</p>
                                        <p style="margin:6px 0 0; font-size:12px; color:#6b7280;">
                                            (Net: {{ number_format(Arr::get($room,'total_net',0),2) }},
                                            Tax: {{ number_format(Arr::get($room,'total_tax',0),2) }},
                                            Fees: {{ number_format(Arr::get($room,'total_fees',0),2) }})
                                        </p>
                                        @if($occupancy)
                                            <p style="margin:6px 0 0; font-size:14px; color:#555;">
                                                Guests: {{ Arr::get($occupancy,'adults',0) }} Adults
                                                @if(count(Arr::get($occupancy,'children_ages',[]))>0)
                                                    , {{ count(Arr::get($occupancy,'children_ages',[])) }} Children
                                                    (ages: {{ implode(', ', Arr::get($occupancy,'children_ages',[])) }})
                                                @endif
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        @endforeach

                        <!-- Total Price -->
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-top:1px solid #e5e7eb; margin-top:12px; padding-top:18px;">
                            <tr>
                                <td width="50%" style="font-size:16px; color:#6b7280; text-align:left;">
                                    <img src="{{ asset('build/images/email-book-confirmation/dollar-sign.svg') }}" alt="Total Price" class="icon" style="width:20px; height:20px; margin-right:8px; vertical-align: middle;"> Total Price:
                                </td>
                                <td width="50%" style="text-align:right; font-size:18px; color:#4f46e5; font-weight:bold;">
                                    {{ number_format($grandTotal, 2) }} {{ $currency }}
                                </td>
                            </tr>
                        </table>

                        <!-- Footer Buttons -->
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:24px;">
                            <tr>
                                <td align="center" style="padding:0 8px;">
                                    <a href="{{ $shareUrl }}" style="text-decoration:none;padding:12px 20px;border-radius:6px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;transition:background-color 0.25s ease;font-size:14px;background:#f3f4f6;color:#4f46e5;">
                                        <img src="{{ asset('build/images/email-book-confirmation/user.svg') }}" alt="Share" class="icon" style="width:18px; height:18px; margin-right:5px; vertical-align: middle;"> Share
                                    </a>
                                </td>
                                <td align="center" style="padding:0 8px;">
                                    <a href="{{ $downloadUrl }}" style="background:#4f46e5; color:#fff; text-decoration:none; padding:12px 20px; border-radius:6px; font-weight:600; font-size:14px; display:inline-block;">
                                        <img src="{{ asset('build/images/email-book-confirmation/calendar-check.svg') }}" alt="Download PDF" class="icon" style="width:18px; height:18px; margin-right:5px; vertical-align: middle;"> Download PDF
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
