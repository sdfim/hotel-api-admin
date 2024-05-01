<?php

namespace Modules\API\Suppliers\DTO\HBSI;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;

class HbsiHotelBookingRetrieveBookingDto
{
    private const CONFIRMATION = [
        '8' => 'HBSI',
        '10' => 'Synxis',
        '14' => 'Own',
        '3' => 'UltimateJet',
    ];

    public static function RetrieveBookingToHotelBookResponseModel(array $filters, array $dataResponse): array
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

        //region Confirmation Numbers
        $bookingData = json_decode(Storage::get($bookData->response_path), true);
        $inputConfirmationNumbers = $bookingData['HotelReservations']['HotelReservation']['ResGlobalInfo']['HotelReservationIDs']['HotelReservationID'] ?? [];
        $supplierBookId = Arr::get($inputConfirmationNumbers, '0.@attributes.ResID_Value', '');

        $confirmationNumbers = array_map(function ($item) {
            return [
                'confirmation_number' => $item['@attributes']['ResID_Value'],
                'type' => self::CONFIRMATION[$item['@attributes']['ResID_Type']] ?? $item['@attributes']['ResID_Type'],
                'type_id' => $item['@attributes']['ResID_Type'],
            ];
        }, $inputConfirmationNumbers);
        //endregion

        $rooms[] = [
            'checkin' => Arr::get($dataResponse, 'ReservationsList.HotelReservation.RoomStays.RoomStay.TimeSpan.@attributes.Start'),
            'checkout' => Arr::get($dataResponse, 'ReservationsList.HotelReservation.RoomStays.RoomStay.TimeSpan.@attributes.End'),
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
        $responseModel->setConfirmationNumbers($confirmationNumbers);
        $responseModel->setBillingContact($bookRequest['booking_contact']['address'] ?? []);
        $responseModel->setBillingEmail($bookRequest['booking_contact']['email'] ?? '');
        $responseModel->setBillingPhone($bookRequest['booking_contact']['phone'] ?? []);


        return $responseModel->toRetrieveArray();
    }

}
