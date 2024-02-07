<?php
declare(strict_types=1);

namespace Modules\API\Suppliers\DTO\Expedia;

use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiSearchInspectorRepository;
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
        $responseModel->setStatus($saveResponse['status']);
        $responseModel->setBookingId($saveResponse['booking_id']);
        $responseModel->setBookringItem($saveResponse['booking_item']);
        $responseModel->setSupplier($saveResponse['supplier']);
        $responseModel->setHotelName($saveResponse['hotel_name']);

        $responseModel->setRooms($rooms);

        $responseModel->setCancellationTerms($saveResponse['cancellation_terms']);
        $responseModel->setRate($saveResponse['rate']);
        $responseModel->setTotalPrice($saveResponse['total_price']);
        $responseModel->setTotalTax($saveResponse['total_tax']);
        $responseModel->setTotalFees($saveResponse['total_fees']);
        $responseModel->setTotalNet($saveResponse['total_net']);
        $responseModel->setAffiliateServiceCharge($saveResponse['affiliate_service_charge']);
        $responseModel->setCurrency($saveResponse['currency']);
        $responseModel->setPerNightBreakdown($saveResponse['per_night_breakdown']);
        $responseModel->setBoardBasis('');
//        $responseModel->setRoomName('');
//        $responseModel->setRoomType('');
        $responseModel->setQuery($query);
        $responseModel->setSupplierBookId($dataResponse['itinerary_id'] ?? '');
        $responseModel->setBillingContact($dataResponse['billing_contact'] ?? '');
        $responseModel->setBillingEmail($dataResponse['email'] ?? '');
        $responseModel->setBillingPhone($dataResponse['phone'] ?? '');


        return $responseModel->toRetrieveArray();
    }
}
