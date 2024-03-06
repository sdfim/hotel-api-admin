<?php

namespace Modules\API\Suppliers\DTO\HBSI;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;

class HbsiHotelBookingRetrieveBookingDto
{    public static function RetrieveBookingToHotelBookResponseModel(array $filters, array $dataResponse): array
    {

        $status = $dataResponse['ReservationsList']['HotelReservation']['@attributes']['ResStatus'] ?? '';
        $status = match ($status) {
            'Book' => 'booked',
            'Cancel' => 'cancelled',
            default => 'unknown',
        };
        $bookData = ApiBookingInspectorRepository::getBookItemsByBookingItem($filters['booking_item']);
        $saveResponse = json_decode(Storage::get($bookData->client_response_path), true);
        $saveOriginal = json_decode(Storage::get(str_replace('.json', '.original.json', $bookData->response_path)), true);
        $mainGuest = json_decode($saveOriginal['main_guest'], true);
        $query = ApiSearchInspectorRepository::getRequest($filters['search_id']);
        $bookRequest = json_decode($bookData->request, true);

        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $bookingItemData = json_decode($bookingItem->booking_item_data, true);

        $supplierBookId = json_decode(Storage::get($bookData->response_path), true)['HotelReservations']['HotelReservation']['ResGlobalInfo']['HotelReservationIDs']['HotelReservationID'][0]['@attributes']['ResID_Value'] ?? '';

        $rooms[] = [
            'checkin' => $query['checkin'],
            'checkout' => $query['checkout'],
            'number_of_adults' => $bookingItemData['rate_occupancy']
                ? explode('-', $bookingItemData['rate_occupancy'])[0]
                : 0,
            'given_name' => $mainGuest['PersonName']['GivenName'],
            'family_name' => $mainGuest['PersonName']['Surname'],
            'room_name' => $saveResponse['rooms']['room_name'] ?? '',
            'room_type' => $saveResponse['room_type'] ?? '',
        ];

        $responseModel = new ResponseModel();
        $responseModel->setStatus($status);
        $responseModel->setBookingId($saveResponse['booking_id']);
        $responseModel->setBookringItem($saveResponse['booking_item']);
        $responseModel->setSupplier($saveResponse['supplier']);
        $responseModel->setHotelName($saveResponse['hotel_name']);

        $responseModel->setRooms($rooms);

        $cancellationTerms = is_array($saveResponse['cancellation_terms'])
            ? $saveResponse['cancellation_terms'] : [$saveResponse['cancellation_terms']];
        $responseModel->setCancellationTerms($cancellationTerms);
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
        $responseModel->setQuery($bookRequest);
        $responseModel->setSupplierBookId($supplierBookId);
        $responseModel->setBillingContact($bookRequest['booking_contact']['address'] ?? []);
        $responseModel->setBillingEmail($bookRequest['booking_contact']['email'] ?? '');
        $responseModel->setBillingPhone($bookRequest['booking_contact']['phone'] ?? []);


        return $responseModel->toRetrieveArray();
    }

}
