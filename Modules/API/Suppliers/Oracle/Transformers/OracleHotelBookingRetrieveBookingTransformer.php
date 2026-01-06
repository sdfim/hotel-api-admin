<?php

namespace Modules\API\Suppliers\Oracle\Transformers;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Services\HotelContentApiTransformerService;

class OracleHotelBookingRetrieveBookingTransformer
{
    /**
     * Карта преобразования статусов Oracle в статусы вашей системы.
     * Используется 'computedReservationStatus' из ответа Oracle.
     */
    private const ORACLE_STATUS_MAP = [
        'CheckedOut' => 'checked_out',
        'Canceled' => 'cancelled',
        'Cancelled' => 'cancelled',
        'NoShow' => 'no_show',
        'Confirmed' => 'booked',
        'Reserved' => 'booked',
        'InHouse' => 'booked',
        'Waitlisted' => 'pending',
    ];

    /**
     * Преобразует ответ от Oracle API о получении бронирования
     * в модель ответа HotelRetrieveBookingResponseModel.
     */
    public static function RetrieveBookingToHotelBookResponseModel(array $filters, array $dataResponse): array
    {
        // 1. Извлечение списка резерваций
        // FIX: Использование корректного пути 'reservations.reservation' вместо 'reservations.reservationInfo'
        $reservations = Arr::get($dataResponse, 'reservations.reservation', []);

        // Если в ответе только одна резервация (объект вместо массива), заворачиваем ее в массив для foreach
        if (is_array($reservations) && !isset($reservations[0]) && count($reservations) > 0) {
            $reservations = [$reservations];
        }

        if (empty($reservations)) {
            /** @var ResponseModel $responseModel */
            $responseModel = app(ResponseModel::class);
            $responseModel->setStatus('unknown');

            return $responseModel->toRetrieveArray();
        }

        // 2. Извлечение сохраненных данных (для цены, налога, условий и контактных данных)
        $bookData = ApiBookingInspectorRepository::getBookItemsByBookingItem($filters['booking_item']);
        $saveResponse = $bookData ? json_decode(Storage::get($bookData->client_response_path), true) : [];
        $bookRequest = json_decode($bookData?->request ?? '', true) ?? [];
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $bookingPricingData = json_decode($bookingItem?->booking_pricing_data ?? '', true) ?? [];

        // Получаем данные о пассажирах из репозитория
        $passengersData = ApiBookingInspectorRepository::getChangePassengers($filters['booking_id'], $filters['booking_item']);
        $roomsFromRequest = json_decode(Arr::get($passengersData, 'request', '{"rooms": []}'), true)['rooms'] ?? [];

        // 3. Агрегация данных и формирование структуры "rooms"
        $totalPrice = 0.0;
        // FIX: Проверяем, что первый элемент существует
        $firstReservation = Arr::get($reservations, 0, []);
        $currency = Arr::get($firstReservation, 'roomStay.rateAmount.currencyCode', Arr::get($saveResponse, 'currency', 'USD'));
        $confirmationNumbers = [];
        $supplierBookId = null;
        $overallStatus = 'unknown';
        $rooms = [];

        $mainGuestContact = Arr::get($bookRequest, 'booking_contact') ?? [];

        foreach ($reservations as $i => $reservation) {
            $currentStatus = Arr::get($reservation, 'computedReservationStatus');
            $status = self::ORACLE_STATUS_MAP[$currentStatus] ?? 'unknown';

            // Находим ID резервации (supplierBookId) и номер подтверждения (ConfirmationNumber)
            $reservationId = Arr::first(Arr::get($reservation, 'reservationIdList', []),
                fn ($item) => $item['type'] === 'Reservation')['id'] ?? null;
            $confirmationNumber = Arr::first(Arr::get($reservation, 'reservationIdList', []),
                fn ($item) => $item['type'] === 'Confirmation')['id'] ?? null;

            if ($confirmationNumber) {
                $confirmationNumbers[] = [
                    'confirmation_number' => $confirmationNumber,
                    'type' => 'Confirmation',
                    'type_id' => 'CN',
                ];
            }

            // Используем ID первой резервации как общий SupplierBookId
            if ($supplierBookId === null && $reservationId) {
                $supplierBookId = $reservationId;
            }

            // Суммируем цену (rateAmount)
            $totalPrice += Arr::get($reservation, 'roomStay.total.amountBeforeTax', 0.0);

            // Определяем общий статус (приоритет: cancelled > checked_out > booked/pending > no_show)
            if ($status === 'cancelled') {
                $overallStatus = 'cancelled';
            } elseif ($overallStatus !== 'cancelled' && $status === 'checked_out') {
                $overallStatus = 'checked_out';
            } elseif ($overallStatus !== 'cancelled' && $overallStatus !== 'checked_out' && in_array($status, ['booked', 'pending'])) {
                if (! in_array($overallStatus, ['booked', 'pending'])) {
                    $overallStatus = $status;
                }
            } elseif ($overallStatus === 'unknown' && $status === 'no_show') {
                $overallStatus = 'no_show';
            }

            // Формирование элемента комнаты
            $guest = Arr::get($reservation, 'reservationGuests.0.profileInfo.profile.customer') ?? [];

            // Получаем список пассажиров для текущей комнаты
            $roomPassengers = Arr::get($roomsFromRequest, $i);

            // FIX: Если нет пассажиров в запросе (инспекторе), создаем минимальный массив пассажиров
            if (empty($roomPassengers) || ! is_array($roomPassengers)) {
                // Извлекаем имя из personName, если оно есть
                $primaryName = Arr::first(Arr::get($guest, 'personName', []), fn($name) => Arr::get($name, 'nameType') === 'Primary') ?? [];

                // Создаем массив, содержащий хотя бы одного гостя (основного)
                $roomPassengers = [[
                    'given_name' => Arr::get($primaryName, 'givenName'),
                    'family_name' => Arr::get($primaryName, 'surname'),
                    // Дополнительные поля (title, age) могут быть пустыми, если их нет
                ]];
            }

            $rooms[] = [
                'checkin' => Arr::get($reservation, 'roomStay.arrivalDate'),
                'checkout' => Arr::get($reservation, 'roomStay.departureDate'),
                'number_of_adults' => Arr::get($reservation, 'roomStay.guestCounts.adults', 0),
                'given_name' => Arr::get($roomPassengers[0], 'given_name'), // Берем имя из первого пассажира
                'family_name' => Arr::get($roomPassengers[0], 'family_name'), // Берем фамилию из первого пассажира
                'room_name' => Arr::get($reservation, 'roomStay.currentRoomInfo.roomType') ?? Arr::get($reservation, 'roomStay.roomType'), // Добавил fallback
                'room_type' => Arr::get($reservation, 'roomStay.ratePlanCode'), // Используем RatePlanCode как RoomType
                'passengers' => $roomPassengers, // Теперь это всегда массив
            ];
        }

        // 4. Поиск и метаданные отеля (логика из HotelTrader)
        $hotelNameWithCode = Arr::get($saveResponse, 'hotel_name', Arr::get($firstReservation, 'hotelName', ''));
        preg_match('/^(.*?)\s*\((\d+)\)$/', $hotelNameWithCode, $matches);
        $name = $matches[1] ?? Arr::get($firstReservation, 'hotelName', '');
        $giata_code = $matches[2] ?? null;

        $hotel = null;
        if ($giata_code) {
            $hotel = Hotel::where('giata_code', $giata_code)->first();
        }
        if (! $hotel) {
            // FIX: Используем hotelId, который присутствует в дампе (VIRM)
            $hotel = Hotel::where('giata_code', Arr::get($firstReservation, 'hotelId'))->first();
        }

        $hotelImage = '';
        if ($hotel?->product?->hero_image) {
            $imagePath = $hotel->product->hero_image;
            $hotelImage = Storage::url($imagePath);
        }

        // 5. Meal Plans (логика из HotelTrader)
        $roomsDataSaved = Arr::get($saveResponse, 'rooms', []);
        $mealPlans = [];
        if (is_array($roomsDataSaved)) {
            if (array_key_exists('meal_plan', $roomsDataSaved) && ! is_array($roomsDataSaved['meal_plan'])) {
                $mealPlans[] = $roomsDataSaved['meal_plan'];
            } else {
                foreach ($roomsDataSaved as $room) {
                    if (is_array($room) && isset($room['meal_plan'])) {
                        $mealPlans[] = $room['meal_plan'];
                    }
                }
            }
        }
        $mealPlans = array_values(array_filter(array_unique($mealPlans), fn ($v) => $v !== null && $v !== ''));
        if (empty($mealPlans)) {
            $mealPlans = $hotel?->hotel_board_basis ?? [];
        }

        // 6. Заполнение Response Model

        /** @var ResponseModel $responseModel */
        $responseModel = app(ResponseModel::class);
        $responseModel->setStatus($overallStatus);

        $responseModel->setBookingId(Arr::get($saveResponse, 'booking_id', ''));
        $responseModel->setBookringItem(Arr::get($saveResponse, 'booking_item', ''));
        $responseModel->setSupplier(Arr::get($saveResponse, 'supplier', ''));

        $responseModel->setHotelName($name);
        $responseModel->setHotelImage($hotelImage);
        $responseModel->setHotelAddress($hotel?->address ?? '');
        $responseModel->setHotelMealPlans($mealPlans);
        $responseModel->setAmenities($hotel ? app(HotelContentApiTransformerService::class)->getHotelAttributes($hotel) : []);

        $responseModel->setRooms($rooms);

        $responseModel->setNonRefundable(Arr::get($bookingPricingData, 'non_refundable', true));
        $cancellationTerms = is_array(Arr::get($saveResponse, 'cancellation_terms', []))
            ? Arr::get($saveResponse, 'cancellation_terms', []) : [Arr::get($saveResponse, 'cancellation_terms')];
        $responseModel->setCancellationTerms($cancellationTerms);
        $responseModel->setDepositInformation(Arr::get($saveResponse, 'deposits', []));
        $responseModel->setRate(Arr::get($saveResponse, 'rate', ''));

        $responseModel->setTotalPrice($totalPrice > 0 ? $totalPrice : Arr::get($saveResponse, 'total_price', 0));
        $responseModel->setTotalTax(Arr::get($saveResponse, 'total_tax', 0));
        $responseModel->setTotalFees(Arr::get($saveResponse, 'total_fees', 0));
        $responseModel->setTotalNet(Arr::get($saveResponse, 'total_net', 0));
        $responseModel->setCurrency($currency);
        $responseModel->setPerNightBreakdown(Arr::get($saveResponse, 'per_night_breakdown', 0));
        $responseModel->setBoardBasis(Arr::get($saveResponse, 'meal_plan', ''));

        // Идентификаторы и контактные данные
        $responseModel->setQuery($bookRequest);
        $responseModel->setSupplierBookId($supplierBookId);
        $responseModel->setConfirmationNumbers(array_unique($confirmationNumbers, SORT_REGULAR));
        $responseModel->setCancellationNumber('');

        $responseModel->setBillingContact(Arr::get($mainGuestContact, 'address', []));
        $responseModel->setBillingEmail(Arr::get($mainGuestContact, 'email', ''));
        $responseModel->setBillingPhone(Arr::get($mainGuestContact, 'phone', []));

        return $responseModel->toRetrieveArray();
    }
}
