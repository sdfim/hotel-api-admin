<?php

declare(strict_types=1);

namespace Modules\API\Suppliers\DTO\Expedia;

use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;

class ExpediaHotelBookingRetrieveBookingDto
{
    /**
     * @return array $filters
     */
    public static function RetrieveBookingToHotelBookResponseModel(array $filters, array $dataResponse): array
    {
        $bookData = ApiBookingInspectorRepository::getBookItemsByBookingItem($filters['booking_item']);
        $saveResponse = json_decode(Storage::get($bookData->client_response_path), true);
        $query = ApiSearchInspectorRepository::getRequest($filters['search_id']);

        $rooms = [];
        foreach ($dataResponse['rooms'] as $room) {
            $rooms[] = [
                'status' => $room['status'],
                'checkin' => $room['checkin'],
                'checkout' => $room['checkout'],
                'number_of_adults' => $room['number_of_adults'],
                'given_name' => $room['given_name'],
                'family_name' => $room['family_name'],
                'room_name' => $room['room_name'] ?? $saveResponse['rooms']['room_name'] ?? '',
                'room_type' => $room['room_type'] ?? '',
            ];
        }

        $responseModel = new ResponseModel();
        $responseModel->setStatus('unknown');
        $responseModel->setBookingId($filters['booking_id']);
        $responseModel->setBookringItem($filters['booking_item']);
        $responseModel->setSupplier('Expedia');
        $responseModel->setHotelName(Arr::get($saveResponse, 'hotel_name', ''));

        $responseModel->setRooms($rooms);

        $responseModel->setCancellationTerms(Arr::get($saveResponse, 'cancellation_terms', []));
        $responseModel->setRate(Arr::get($saveResponse, 'rate', ''));
        $responseModel->setTotalPrice(Arr::get($saveResponse, 'total_price', 0));
        $responseModel->setTotalTax(Arr::get($saveResponse, 'total_tax', 0));
        $responseModel->setTotalFees(Arr::get($saveResponse, 'total_fees', 0));
        $responseModel->setTotalNet(Arr::get($saveResponse, 'total_net', 0));
        $responseModel->setMarkup(Arr::get($saveResponse, 'markup', 0));
        $responseModel->setCurrency(Arr::get($saveResponse, 'currency', ''));
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
