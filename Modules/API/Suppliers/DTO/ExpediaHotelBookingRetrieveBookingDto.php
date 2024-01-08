<?php
declare(strict_types=1);

namespace Modules\API\Suppliers\DTO;

use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;

class ExpediaHotelBookingRetrieveBookingDto
{
    /**
     * @return array $filters
     */
    public static function ExpediaRetrieveBookingToHotelBookResponseModel(array $filters, array $dataResponse): array
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

        $ResponseModel = new ResponseModel();
        $ResponseModel->setStatus($saveResponse['status']);
        $ResponseModel->setBookingId($saveResponse['booking_id']);
        $ResponseModel->setBookringItem($saveResponse['booking_item']);
        $ResponseModel->setSupplier($saveResponse['supplier']);
        $ResponseModel->setHotelName($saveResponse['hotel_name']);

        $ResponseModel->setRooms($rooms);

        $ResponseModel->setCancellationTerms($saveResponse['cancellation_terms']);
        $ResponseModel->setRate($saveResponse['rate']);
        $ResponseModel->setTotalPrice($saveResponse['total_price']);
        $ResponseModel->setTotalTax($saveResponse['total_tax']);
        $ResponseModel->setTotalFees($saveResponse['total_fees']);
        $ResponseModel->setTotalNet($saveResponse['total_net']);
        $ResponseModel->setAffiliateServiceCharge($saveResponse['affiliate_service_charge']);
        $ResponseModel->setCurrency($saveResponse['currency']);
        $ResponseModel->setPerNightBreakdown($saveResponse['per_night_breakdown']);
        $ResponseModel->setBoardBasis('');
//        $ResponseModel->setRoomName('');
//        $ResponseModel->setRoomType('');
        $ResponseModel->setQuery($query);
        $ResponseModel->setSupplierBookId($dataResponse['itinerary_id'] ?? '');
        $ResponseModel->setBillingContact($dataResponse['billing_contact'] ?? '');
        $ResponseModel->setBillingEmail($dataResponse['email'] ?? '');
        $ResponseModel->setBillingPhone($dataResponse['phone'] ?? '');


        return $ResponseModel->toRetrieveArray();
    }
}
