<?php

namespace Modules\API\Suppliers\Oracle\Transformers;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Services\HotelContentApiTransformerService;

class OracleHotelBookingRetrieveBookingTransformer
{
    public static function RetrieveBookingToHotelBookResponseModel(array $filters, array $dataResponse): array
    {
        $status = $dataResponse['ReservationsList']['HotelReservation']['@attributes']['ResStatus'] ?? '';
        $status = match ($status) {
            'Book' => 'booked',
            'Cancel' => 'cancelled',
            default => 'unknown',
        };
        $bookData = ApiBookingInspectorRepository::getBookItemsByBookingItem($filters['booking_item']);
        $saveResponse = $bookData ? json_decode(Storage::get($bookData->client_response_path), true) : [];
        $saveOriginal = $bookData ? json_decode(Storage::get(str_replace('.json', '.original.json', $bookData->response_path)), true) : [];
        $mainGuest = json_decode(Arr::get($saveOriginal, 'main_guest', ''), true);
        $query = ApiSearchInspectorRepository::getRequest($filters['search_id']);
        $bookRequest = json_decode($bookData?->request ?? '', true) ?? [];

        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $bookingItemData = json_decode($bookingItem?->booking_item_data ?? '', true);
        $bookingPricingData = json_decode($bookingItem?->booking_pricing_data ?? '', true);
        // region Confirmation Numbers
        $bookingDataFromFile = $bookData ? json_decode(Storage::get($bookData->response_path), true) : [];

        $confirmationNumbers = self::processConfirmationNumbersAndSupplier($dataResponse['ReservationsList'], $bookingDataFromFile);

        $cancellationNumber = null;
        if ($status === 'cancelled') {
            $cancellationNumber = self::processCancellationNumber($confirmationNumbers);
        }

        $supplierBookId = Arr::get($confirmationNumbers, '0.@attributes.ResID_Value', '');
        // endregion

        $passengersData = ApiBookingInspectorRepository::getChangePassengers($filters['booking_id'], $filters['booking_item']);
        $guests = json_decode($passengersData->request, true)['rooms'];

        $dataStays = [];
        if (array_key_first($dataResponse['ReservationsList']['HotelReservation']['RoomStays']['RoomStay']) == '@attributes') {
            $dataStays[] = $dataResponse['ReservationsList']['HotelReservation']['RoomStays']['RoomStay'];
        } else {
            $dataStays = $dataResponse['ReservationsList']['HotelReservation']['RoomStays']['RoomStay'];
        }

        $rooms = [];
        $k = 0;
        foreach ($dataStays as $room) {
            $rooms[] = [
                'checkin' => Arr::get($room, 'TimeSpan.@attributes.Start'),
                'checkout' => Arr::get($room, 'TimeSpan.@attributes.End'),
                'number_of_adults' => count($guests[$k] ?? []),
                'given_name' => Arr::get($mainGuest, 'PersonName.GivenName'),
                'family_name' => Arr::get($mainGuest, 'PersonName.Surname'),
                'room_name' => $saveResponse['rooms']['room_name'] ?? '',
                'room_type' => $saveResponse['room_type'] ?? '',
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
        $responseModel->setDepositInformation($depositInformation);
        $responseModel->setRate(Arr::get($saveResponse, 'rate', ''));
        $responseModel->setTotalPrice(Arr::get($saveResponse, 'total_price', 0));
        $responseModel->setTotalTax(Arr::get($saveResponse, 'total_tax', 0));
        $responseModel->setTotalFees(Arr::get($saveResponse, 'total_fees', 0));
        $responseModel->setTotalNet(Arr::get($saveResponse, 'total_net', 0));
        $responseModel->setCurrency(Arr::get($saveResponse, 'currency', ''));
        $responseModel->setPerNightBreakdown(Arr::get($saveResponse, 'per_night_breakdown', 0));
        $responseModel->setBoardBasis('');
        //        $responseModel->setRoomName('');
        //        $responseModel->setRoomType('');
        $responseModel->setQuery($bookRequest);
        $responseModel->setSupplierBookId($supplierBookId);
        $responseModel->setConfirmationNumbers($confirmationNumbers);
        $responseModel->setCancellationNumber($cancellationNumber);
        $responseModel->setBillingContact(Arr::get($bookRequest, 'booking_contact.address', []));
        $responseModel->setBillingEmail(Arr::get($bookRequest, 'booking_contact.email', ''));
        $responseModel->setBillingPhone(Arr::get($bookRequest, 'booking_contact.phone', []));

        return $responseModel->toRetrieveArray();
    }
}
