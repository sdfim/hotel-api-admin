<?php

namespace Modules\API\Suppliers\HotelTrader\Transformers;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Services\HotelContentApiTransformerService;

class HotelTraderiHotelBookingRetrieveBookingTransformer
{
    public static function RetrieveBookingToHotelBookResponseModel(array $filters, array $data): array
    {
        $status = Arr::get($data, 'rooms.0.cancelled', false) ? 'cancelled' : 'booked';

        $bookData = ApiBookingInspectorRepository::getBookItemsByBookingItem($filters['booking_item']);
        $saveResponse = $bookData ? json_decode(Storage::get($bookData->client_response_path), true) : [];
        $bookRequest = json_decode($bookData?->request ?? '', true) ?? [];

        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $bookingPricingData = json_decode($bookingItem?->booking_pricing_data ?? '', true);

        $passengersData = ApiBookingInspectorRepository::getChangePassengers($filters['booking_id'], $filters['booking_item']);
        $guests = json_decode($passengersData->request, true)['rooms'];

        $property = Arr::get($data, 'propertyDetails', []);
        $roomsData = Arr::get($data, 'rooms', []);
        $rooms = [];
        $k = 0;
        foreach ($roomsData as $room) {
            $rooms[] = [
                'checkin' => Arr::get($room, 'checkInDate'),
                'checkout' => Arr::get($room, 'checkOutDate'),
                'number_of_adults' => count(array_filter(Arr::get($room, 'guests', []), fn ($g) => Arr::get($g, 'adult', false))),
                'given_name' => Arr::get($room, 'guests.0.firstName'),
                'family_name' => Arr::get($room, 'guests.0.lastName'),
                'room_name' => Arr::get($room, 'roomName'),
                'room_type' => Arr::get($room, 'rateplanTag', ''),
                'passengers' => $guests[$k] ?? [],
            ];
            $k++;
        }

        preg_match('/^(.*?)\s*\((\d+)\)$/', $saveResponse['hotel_name'], $matches);
        $name = $matches[1] ?? '';
        $giata_code = $matches[2] ?? '';

        $hotel = Hotel::where('giata_code', $giata_code)->first();

        if ($hotel?->product?->hero_image) {
            $imagePath = $hotel->product->hero_image;
            $hotelImage = Storage::url($imagePath);
        } else {
            $hotelImage = '';
        }

        $hotelAddress = $hotel?->address;
        $depositInformation = Arr::get($saveResponse, 'deposits', []);

        $attributes = app(HotelContentApiTransformerService::class)->getHotelAttributes($hotel);

        $roomsData = Arr::get($saveResponse, 'rooms', []);
        $mealPlans = [];
        if (is_array($roomsData)) {
            if (array_key_exists('meal_plan', $roomsData) && ! is_array($roomsData['meal_plan'])) {
                $mealPlans[] = $roomsData['meal_plan'];
            } else {
                foreach ($roomsData as $room) {
                    if (is_array($room) && isset($room['meal_plan'])) {
                        $mealPlans[] = $room['meal_plan'];
                    }
                }
            }
        }
        $mealPlans = array_values(array_filter(array_unique($mealPlans), fn ($v) => $v !== null && $v !== ''));
        if (empty($mealPlans)) {
            $mealPlans = $hotel->hotel_board_basis;
        }

        /** @var ResponseModel $responseModel */
        $responseModel = app(ResponseModel::class);
        $responseModel->setStatus($status);
        $responseModel->setBookingId(Arr::get($saveResponse, 'booking_id', ''));
        $responseModel->setBookringItem(Arr::get($saveResponse, 'booking_item', ''));
        $responseModel->setSupplier(Arr::get($saveResponse, 'supplier', ''));
        $responseModel->setHotelName($name);
        $responseModel->setHotelImage($hotelImage);
        $responseModel->setHotelAddress($hotelAddress);
        $responseModel->setHotelMealPlans($mealPlans);

        $responseModel->setAmenities($attributes);

        $responseModel->setRooms($rooms);

        $responseModel->setNonRefundable(Arr::get($bookingPricingData, 'non_refundable', true));

        $cancellationTerms = is_array(Arr::get($saveResponse, 'cancellation_terms', []))
            ? Arr::get($saveResponse, 'cancellation_terms', []) : [Arr::get($saveResponse, 'cancellation_terms')];
        $responseModel->setCancellationTerms($cancellationTerms);
        $depositInformation = ! empty($depositInformation) ? $depositInformation : \App\Repositories\ApiBookingItemRepository::getDeposits($filters['booking_item']);
        $responseModel->setDepositInformation($depositInformation);
        $responseModel->setRate(Arr::get($saveResponse, 'rate', ''));
        $responseModel->setTotalPrice(Arr::get($saveResponse, 'total_price', 0));
        $responseModel->setTotalTax(Arr::get($saveResponse, 'total_tax', 0));
        $responseModel->setTotalFees(Arr::get($saveResponse, 'total_fees', 0));
        $responseModel->setTotalNet(Arr::get($saveResponse, 'total_net', 0));
        $responseModel->setCurrency(Arr::get($saveResponse, 'currency', ''));
        $responseModel->setPerNightBreakdown(Arr::get($saveResponse, 'per_night_breakdown', 0));
        $responseModel->setPerNightBreakdown(0.0);
        $responseModel->setBoardBasis(Arr::get($saveResponse, 'meal_plan', ''));

        $responseModel->setQuery($bookRequest);
        $responseModel->setSupplierBookId(Arr::get($data, 'htConfirmationCode', ''));
        $responseModel->setConfirmationNumbers([
            [
                'confirmation_number' => Arr::get($data, 'htConfirmationCode', ''),
                'type' => 'HotelTrader',
                'type_id' => 'HT',
            ],
        ]);
        $responseModel->setCancellationNumber('');
        $responseModel->setBillingContact(Arr::get($bookRequest, 'booking_contact.address', []));
        $responseModel->setBillingEmail(Arr::get($bookRequest, 'booking_contact.email', ''));
        $responseModel->setBillingPhone(Arr::get($bookRequest, 'booking_contact.phone', []));

        return $responseModel->toRetrieveArray();
    }
}
