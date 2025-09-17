<?php

namespace Modules\API\Suppliers\Transformers\HotelTrader;

use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;
use Modules\HotelContentRepository\Models\Hotel;

class HotelTraderiHotelBookingRetrieveBookingTransformer
{
    public static function RetrieveBookingToHotelBookResponseModel(array $filters, array $data): array
    {
        $status = Arr::get($data, 'rooms.0.cancelled', false) ? 'cancelled' : 'booked';

        $bookData = ApiBookingInspectorRepository::getBookItemsByBookingItem($filters['booking_item']);
        $saveResponse = $bookData ? json_decode(Storage::get($bookData->client_response_path), true) : [];
        $bookRequest = json_decode($bookData?->request ?? '', true) ?? [];

        $property = Arr::get($data, 'propertyDetails', []);
        $roomsData = Arr::get($data, 'rooms', []);
        $rooms = [];
        foreach ($roomsData as $room) {
            $rooms[] = [
                'checkin' => Arr::get($room, 'checkInDate'),
                'checkout' => Arr::get($room, 'checkOutDate'),
                'number_of_adults' => count(array_filter(Arr::get($room, 'guests', []), fn ($g) => Arr::get($g, 'adult', false))),
                'given_name' => Arr::get($room, 'guests.0.firstName'),
                'family_name' => Arr::get($room, 'guests.0.lastName'),
                'room_name' => Arr::get($room, 'roomName'),
                'room_type' => Arr::get($room, 'rateplanTag', ''),
                'passengers' => Arr::get($room, 'guests', []),
            ];
        }

        preg_match('/^(.*?)\s*\((\d+)\)$/', $saveResponse['hotel_name'], $matches);
        $name = $matches[1] ?? '';
        $giata_code = $matches[2] ?? '';

        $hotel = Hotel::where('giata_code', $giata_code)->first();

        if ($hotel?->product?->hero_image) {
            $imagePath = $hotel->product->hero_image;
            if (config('filesystems.default') === 's3') {
                $hotelImage = rtrim(config('image_sources.sources.s3'), '/').'/'.ltrim($imagePath, '/');
            } else {
                $hotelImage = rtrim(config('image_sources.sources.local'), '/').'/storage/'.ltrim($imagePath, '/');
            }
        } else {
            $hotelImage = '';
        }

        $hotelAddress = $hotel?->address;
        $depositInformation = Arr::get($saveResponse, 'deposits', []);

        /** @var ResponseModel $responseModel */
        $responseModel = app(ResponseModel::class);
        $responseModel->setStatus($status);
        $responseModel->setBookingId(Arr::get($saveResponse, 'booking_id', ''));
        $responseModel->setBookringItem(Arr::get($saveResponse, 'booking_item', ''));
        $responseModel->setSupplier(Arr::get($saveResponse, 'supplier', ''));
        $responseModel->setHotelName(Arr::get($saveResponse, 'hotel_name', ''));
        $responseModel->setHotelImage($hotelImage);
        $responseModel->setHotelAddress($hotelAddress);

        $responseModel->setRooms($rooms);

        $cancellationTerms = is_array(Arr::get($saveResponse, 'cancellation_terms', []))
            ? Arr::get($saveResponse, 'cancellation_terms', []) : [Arr::get($saveResponse, 'cancellation_terms')];
        $responseModel->setCancellationTerms($cancellationTerms);
        $responseModel->setDepositInformation($depositInformation);
        $responseModel->setRate(Arr::get($saveResponse, 'rate', ''));
        $responseModel->setTotalPrice(Arr::get($saveResponse, 'total_price', 0));
        $responseModel->setTotalTax(Arr::get($saveResponse, 'total_tax', 0));
        $responseModel->setTotalFees(Arr::get($saveResponse, 'total_fees', 0));
        $responseModel->setTotalNet(Arr::get($saveResponse, 'total_net', 0));
        $responseModel->setMarkup(Arr::get($saveResponse, 'markup', 0));
        $responseModel->setCurrency(Arr::get($saveResponse, 'markup', ''));
        $responseModel->setPerNightBreakdown(Arr::get($saveResponse, 'per_night_breakdown', 0));
        $responseModel->setPerNightBreakdown(0.0);
        $responseModel->setBoardBasis(Arr::get($roomsData[0], 'mealplanOptions.mealplanDescription', ''));

        $responseModel->setQuery($bookRequest);
        $responseModel->setSupplierBookId(Arr::get($data, 'htConfirmationCode', ''));
        $responseModel->setConfirmationNumbers([
            [
                'confirmation_number' => Arr::get($data, 'htConfirmationCode', ''),
                'type' => 'HotelTrader',
                'type_id' => 'HT',
            ],
        ]);
        $responseModel->setCancellationNumber(Arr::get($roomsData[0], 'crsCancelConfirmationCode', null));
        $responseModel->setBillingContact(Arr::get($bookRequest, 'booking_contact.address', []));
        $responseModel->setBillingEmail(Arr::get($bookRequest, 'booking_contact.email', ''));
        $responseModel->setBillingPhone(Arr::get($bookRequest, 'booking_contact.phone', []));

        return $responseModel->toRetrieveArray();
    }
}
