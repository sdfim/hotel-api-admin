<?php

namespace Modules\API\Suppliers\Transformers\HotelTrader;

use Illuminate\Support\Arr;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel as ResponseModel;

class HotelTraderiHotelBookingRetrieveBookingTransformer
{
    public static function RetrieveBookingToHotelBookResponseModel(array $data): array
    {
        $status = Arr::get($data, 'rooms.0.cancelled', false) ? 'cancelled' : 'booked';

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

        /** @var ResponseModel $responseModel */
        $responseModel = app(ResponseModel::class);
        $responseModel->setStatus($status);
        $responseModel->setBookingId(Arr::get($data, 'clientConfirmationCode', ''));
        $responseModel->setBookringItem(Arr::get($data, 'clientConfirmationCode', ''));
        $responseModel->setSupplier('HotelTrader');
        $responseModel->setHotelName(Arr::get($property, 'propertyName', ''));
        $responseModel->setRooms($rooms);
        $responseModel->setCancellationTerms(Arr::get($roomsData[0], 'cancellationPolicies', []));
        $responseModel->setRate(Arr::get($roomsData[0], 'rates.grossPrice', 0));
        $responseModel->setTotalPrice(Arr::get($data, 'aggregateGrossPrice', 0));
        $responseModel->setTotalTax(Arr::get($data, 'aggregateTax', 0));
        $responseModel->setTotalFees(0);
        $responseModel->setTotalNet(Arr::get($data, 'aggregateNetPrice', 0));
        $responseModel->setMarkup(0);
        $responseModel->setCurrency(Arr::get($roomsData[0], 'rates.currency', 'USD'));
//        $responseModel->setPerNightBreakdown(json_encode(Arr::get($roomsData[0], 'rates.dailyPrice', [])));
        $responseModel->setPerNightBreakdown(0.0);
        $responseModel->setBoardBasis(Arr::get($roomsData[0], 'mealplanOptions.mealplanDescription', ''));
        $responseModel->setQuery([]);
        $responseModel->setSupplierBookId(Arr::get($data, 'htConfirmationCode', ''));
        $responseModel->setConfirmationNumbers([
            [
                'confirmation_number' => Arr::get($data, 'htConfirmationCode', ''),
                'type' => 'HotelTrader',
                'type_id' => 'HT',
            ],
        ]);
        $responseModel->setCancellationNumber(Arr::get($roomsData[0], 'crsCancelConfirmationCode', null));
        $responseModel->setBillingContact([]);
        $responseModel->setBillingEmail('');
        $responseModel->setBillingPhone([]);

        return $responseModel->toRetrieveArray();
    }
}
