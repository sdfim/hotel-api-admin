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

    const CANCELLATION_ID_TYPES = [18, 10, 8];

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
        $bookingDataFromFile = $bookData ? json_decode(Storage::get($bookData->response_path), true) : [];

        $confirmationNumbers = self::processConfirmationNumbersAndSupplier($dataResponse['ReservationsList'], $bookingDataFromFile);

        $cancellationNumber = null;
        if ($status === 'cancelled') {
            $cancellationNumber = self::processCancellationNumber($confirmationNumbers);
        }

        $supplierBookId = Arr::get($confirmationNumbers, '0.@attributes.ResID_Value', '');
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
        $responseModel->setCancellationNumber($cancellationNumber);
        $responseModel->setBillingContact(Arr::get($bookRequest, 'booking_contact.address', []));
        $responseModel->setBillingEmail(Arr::get($bookRequest, 'booking_contact.email', ''));
        $responseModel->setBillingPhone(Arr::get($bookRequest, 'booking_contact.phone', []));

        return $responseModel->toRetrieveArray();
    }

    /**
     * @param $bookingFromResponseData
     * @param $bookingDataFromFile
     * @return array{inputConfirmationNumbers: array, supplierBookId: string}
     */
    private static function processConfirmationNumbersAndSupplier($bookingFromResponseData, $bookingDataFromFile): array
    {
        $confirmationsFromFile = Arr::get($bookingDataFromFile, 'HotelReservations.HotelReservation.ResGlobalInfo.HotelReservationIDs.HotelReservationID', []);
        $confirmationsFromResponse = Arr::get($bookingFromResponseData, 'HotelReservation.ResGlobalInfo.HotelReservationIDs.HotelReservationID', []);

        $inputConfirmationNumbers = self::mergeConfirmationNumbersBothSources($confirmationsFromResponse,$confirmationsFromFile );

        return array_map(function ($item) {
            return [
                'confirmation_number' => $item['@attributes']['ResID_Value'],
                'type' => self::CONFIRMATION[$item['@attributes']['ResID_Type']] ?? $item['@attributes']['ResID_Type'],
                'type_id' => $item['@attributes']['ResID_Type'],
            ];
        }, $inputConfirmationNumbers);
    }

    private static function mergeConfirmationNumbersBothSources($responseConfirmationNumbers, $fileConfirmationNumbers): array
    {
        $mergedArray = [];

        // Creating array with default elements based on the file results
        foreach ($fileConfirmationNumbers as $reservation) {
            $type = $reservation['@attributes']['ResID_Type'];
            $mergedArray[$type] = $reservation;
        }

        // Replacing elements if it exists
        foreach ($responseConfirmationNumbers as $confirmationNumber) {
            $type = $confirmationNumber['@attributes']['ResID_Type'];
            $mergedArray[$type] = $confirmationNumber;
        }

        // Clean indexes
        $result = array_values($mergedArray);

        return $result;
    }

    /**
     * @param $confirmationNumbersList
     * @return string|null
     */
    private static function processCancellationNumber($confirmationNumbersList): ?string
    {
        // With this foreach we evaluate each cancellation type in priority order, CANCELLATION_ID_TYPES 18 > 10 > 8
        foreach (self::CANCELLATION_ID_TYPES as $cancellationType)
        {
            $filteredConfirmationNumbers = array_filter($confirmationNumbersList, fn ($confirmationNumber) => intval($confirmationNumber['type_id']) === $cancellationType);

            if ($cancellationNumber = Arr::get(array_values($filteredConfirmationNumbers), '0.confirmation_number'))
            {
                return str_contains($cancellationNumber, '$') ? $cancellationNumber : "CXL_$cancellationNumber";
            }
        }

        return null;
    }
}
