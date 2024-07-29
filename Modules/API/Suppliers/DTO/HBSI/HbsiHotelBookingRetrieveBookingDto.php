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
        $saveResponse = $bookData ? json_decode(Storage::get($bookData->client_response_path), true) : [];
        $saveOriginal = $bookData ? json_decode(Storage::get(str_replace('.json', '.original.json', $bookData->response_path)), true) : [];
        $mainGuest = json_decode(Arr::get($saveOriginal, 'main_guest', ''), true);
        $query = ApiSearchInspectorRepository::getRequest($filters['search_id']);
        $bookRequest = json_decode($bookData?->request ?? '', true) ?? [];

        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $bookingItemData = json_decode($bookingItem?->booking_item_data ?? '', true);

        //region Confirmation Numbers
        $bookingData = $bookData ? json_decode(Storage::get($bookData->response_path), true) : [];
        $inputConfirmationNumbers = Arr::get($bookingData, 'HotelReservations.HotelReservation.ResGlobalInfo.HotelReservationIDs.HotelReservationID', []);
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
            'number_of_adults' => Arr::get($bookingItemData, 'rate_occupancy')
                ? explode('-', Arr::get($bookingItemData, 'rate_occupancy.0'))
                : 0,
            'given_name' => Arr::get($mainGuest, 'PersonName.GivenName'),
            'family_name' => Arr::get($mainGuest, 'PersonName.Surname'),
            'room_name' => $saveResponse['rooms']['room_name'] ?? '',
            'room_type' => $saveResponse['room_type'] ?? '',
        ];

        $responseModel = new ResponseModel();
        $responseModel->setStatus($status);
        $responseModel->setBookingId(Arr::get($saveResponse, 'booking_id', ''));
        $responseModel->setBookringItem(Arr::get($saveResponse, 'booking_item', ''));
        $responseModel->setSupplier(Arr::get($saveResponse, 'supplier', ''));
        $responseModel->setHotelName(Arr::get($saveResponse, 'hotel_name', ''));

        $responseModel->setRooms($rooms);

        $cancellationTerms = is_array(Arr::get($saveResponse, 'cancellation_terms', []))
            ? Arr::get($saveResponse, 'cancellation_terms', []) : [Arr::get($saveResponse, 'cancellation_terms')];
        $responseModel->setCancellationTerms($cancellationTerms);
        $responseModel->setRate(Arr::get($saveResponse, 'rate', ''));
        $responseModel->setTotalPrice(Arr::get($saveResponse, 'total_price', 0));
        $responseModel->setTotalTax(Arr::get($saveResponse, 'total_tax', 0));
        $responseModel->setTotalFees(Arr::get($saveResponse, 'total_fees', 0));
        $responseModel->setTotalNet(Arr::get($saveResponse, 'total_net', 0));
        $responseModel->setMarkup(Arr::get($saveResponse, 'markup', 0));
        $responseModel->setCurrency(Arr::get($saveResponse, 'markup', ''));
        $responseModel->setPerNightBreakdown(Arr::get($saveResponse, 'per_night_breakdown', 0));
        $responseModel->setBoardBasis('');
        //        $responseModel->setRoomName('');
        //        $responseModel->setRoomType('');
        $responseModel->setQuery($bookRequest);
        $responseModel->setSupplierBookId($supplierBookId);
        $responseModel->setConfirmationNumbers($confirmationNumbers);
        $responseModel->setBillingContact(Arr::get($bookRequest, 'booking_contact.address', []));
        $responseModel->setBillingEmail(Arr::get($bookRequest, 'booking_contact.email', ''));
        $responseModel->setBillingPhone(Arr::get($bookRequest, 'booking_contact.phone', []));

        return $responseModel->toRetrieveArray();
    }
}
