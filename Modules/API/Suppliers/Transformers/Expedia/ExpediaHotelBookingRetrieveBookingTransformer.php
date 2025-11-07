<?php

declare(strict_types=1);

namespace Modules\API\Suppliers\Transformers\Expedia;

use App\Models\Property;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;

class ExpediaHotelBookingRetrieveBookingTransformer
{
    /**
     * @return array $filters
     */
    public static function RetrieveBookingToHotelBookResponseModel(array $filters, array $dataResponse): array
    {
        $bookData = ApiBookingInspectorRepository::getBookItemsByBookingItem($filters['booking_item']);
        $saveResponse = json_decode(Storage::get($bookData->client_response_path), true);
        $query = ApiSearchInspectorRepository::getRequest($filters['search_id']);

        $passengersData = ApiBookingInspectorRepository::getPassengers($filters['booking_id'], $filters['booking_item']);
        $guests = json_decode($passengersData->request, true)['rooms'];

        $itemPricingData = ApiBookingItemRepository::getItemPricingData($filters['booking_item']);
        $itemData = ApiBookingItemRepository::getItemData($filters['booking_item']);
        $status = ApiBookingInspectorRepository::isCancel($filters['booking_item']) ? 'cancelled' : 'booked';
        $hotelId = Arr::get($itemData, 'hotel_id', null);
        $property = $hotelId ? Property::where('code', $hotelId)->first() : null;
        $hotelName = $property ? $property->name : '';

        $cancellationTerms = [];

        $rooms = [];
        foreach ($dataResponse['rooms'] as $k => $room) {
            $rooms[] = [
                'status' => $room['status'],
                'checkin' => $room['checkin'],
                'checkout' => $room['checkout'],
                'number_of_adults' => $room['number_of_adults'],
                'given_name' => $room['given_name'],
                'family_name' => $room['family_name'],
                'room_name' => $room['room_name'] ?? $saveResponse['rooms']['room_name'] ?? '',
                'room_type' => $room['room_type'] ?? '',
                'passengers' => $guests[$k] ?? [],
            ];

            foreach (Arr::get($room, 'rate.cancel_penalties', []) as $penalty) {
                $cancellationTerms[] = ['penalty_start_date' => Arr::get($penalty, 'start')];
            }
        }

        /** @var ResponseModel $responseModel */
        $responseModel = app(ResponseModel::class);
        $responseModel->setStatus($status);
        $responseModel->setBookingId($filters['booking_id']);
        $responseModel->setBookringItem($filters['booking_item']);
        $responseModel->setSupplier('Expedia');
        $responseModel->setHotelName($hotelName ?? Arr::get($saveResponse, 'hotel_name', ''));

        $responseModel->setRooms($rooms);

        $responseModel->setCancellationTerms($cancellationTerms);
        $responseModel->setRate(Arr::get($saveResponse, 'rate', ''));
        $responseModel->setTotalPrice(Arr::get($itemPricingData, 'total_price', 0));
        $responseModel->setTotalTax(Arr::get($itemPricingData, 'total_tax', 0));
        $responseModel->setTotalFees(Arr::get($itemPricingData, 'total_fees', 0));
        $responseModel->setTotalNet(Arr::get($itemPricingData, 'total_net', 0));
        $responseModel->setCurrency(Arr::get($itemPricingData, 'currency', ''));
        $responseModel->setPerNightBreakdown(Arr::get($saveResponse, 'per_night_breakdown', 0));
        $responseModel->setBoardBasis('');
        //        $responseModel->setRoomName('');
        //        $responseModel->setRoomType('');
        $responseModel->setQuery($query);

        $responseModel->setSupplierBookId($dataResponse['itinerary_id'] ?? '');
        $responseModel->setConfirmationNumbers([
            'confirmation_number' => $responseModel->getSupplierBookId(),
            'type' => 'Expedia',
        ]);
        $responseModel->setBillingContact($dataResponse['billing_contact'] ?? '');
        $responseModel->setBillingEmail($dataResponse['email'] ?? '');
        $responseModel->setBillingPhone($dataResponse['phone'] ?? '');

        return $responseModel->toRetrieveArray();
    }
}
