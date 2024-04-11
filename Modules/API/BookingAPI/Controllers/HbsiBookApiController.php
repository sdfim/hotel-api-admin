<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingMetadata;
use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveReservations;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiBookingsMetadata;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ChannelRenository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\API\Suppliers\DTO\HBSI\HbsiHotelBookDto;
use Modules\API\Suppliers\DTO\HBSI\HbsiHotelBookingRetrieveBookingDto;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\Enums\SupplierNameEnum;
use Modules\Enums\TypeRequestEnum;

class HbsiBookApiController extends BaseBookApiController
{
    private const CONFIRMATION = [
        '8' => 'HBSI',
        '10' => 'Synxis',
        '14' => 'Own',
        '3' => 'UltimateJet',
    ];

    public function __construct(
        private readonly HbsiClient       $hbsiClient = new HbsiClient(),
        private readonly HbsiHotelBookDto $hbsiHotelBookDto = new HbsiHotelBookDto(),
    )
    {
    }

    /**
     * @param array $filters
     * @param ApiBookingInspector $bookingInspector
     * @return array|null
     * @throws GuzzleException
     */
    public function book(array $filters, ApiBookingInspector $bookingInspector): array|null
    {
        $booking_id = $bookingInspector->booking_id;
        $filters['search_id'] = $bookingInspector->search_id;
        $filters['booking_item'] = $bookingInspector->booking_item;

        $passengers = BookingRepository::getPassengers($booking_id, $filters['booking_item']);

        if (!$passengers) {
            return [
                'error' => 'Passengers not found.',
                'booking_item' => $filters['booking_item'],
            ];
        } else {
            $passengersArr = $passengers->toArray();
            $dataPassengers = json_decode($passengersArr['request'], true);
        }
        if (!isset($filters['credit_cards'])) {
            return [
                'error' => 'Credit card not found.',
                'booking_item' => $filters['booking_item'],
            ];
        }

        $error = true;
        try {
            Log::info('HbsiBookApiController | book | ' . json_encode($filters));
            $xmlPriceData = $this->hbsiClient->handleBook($filters);

            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $dataResponse = json_decode(json_encode($response), true) ?? [];

            $dataResponseToSave = $dataResponse;
            $dataResponseToSave['original'] = [
                'request' => $xmlPriceData['request'],
                'response' => $xmlPriceData['response']->asXML(),
                'main_guest' => $xmlPriceData['main_guest'],
            ];
            if (!isset($dataResponse['Errors'])) {
                $inputConfirmationNumbers = $dataResponse['HotelReservations']['HotelReservation']['ResGlobalInfo']['HotelReservationIDs']['HotelReservationID'] ?? [];
                $confirmationNumbers = array_map(function ($item) {
                    return [
                        'confirmation_number' => $item['@attributes']['ResID_Value'],
                        'type' => self::CONFIRMATION[$item['@attributes']['ResID_Type']] ?? $item['@attributes']['ResID_Type'],
                    ];
                }, $inputConfirmationNumbers);
                $clientResponse = $this->hbsiHotelBookDto->toHotelBookResponseModel($filters, $confirmationNumbers);

                # Save Booking Info
                $this->saveBookingInfo($filters, $dataResponse);

                $error = false;
            } else {
                $clientResponse = $dataResponse['Errors'];
                $clientResponse['booking_item'] = $filters['booking_item'];
                $clientResponse['supplier'] = SupplierNameEnum::HBSI->value;
            }

        } catch (RequestException $e) {
            Log::error('HbsiBookApiController | book | RequestException ' . $e->getResponse()->getBody());
            return [
                'error' => $e->getResponse()->getBody(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::HBSI->value
            ];
        } catch (\Exception $e) {
            Log::error('HbsiBookApiController | book | Exception ' . $e->getMessage());
            return [
                'error' => $e->getMessage(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::HBSI->value
            ];
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        if (!$error) {
            SaveBookingInspector::dispatch([
                $booking_id, $filters, $dataResponseToSave, $clientResponse, $supplierId, 'book', 'create', $bookingInspector->search_type
            ]);
            # Save Book data to Reservation
            SaveReservations::dispatch($booking_id, $filters, $dataPassengers);
        } else SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponseToSave, $clientResponse, $supplierId, 'book', 'error', $bookingInspector->search_type
        ]);

        if (!$dataResponse) {
            return [];
        }

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) {
            $res = (array)$dataResponse;
        } elseif ($error) {
            $res = $clientResponse;
        } else {
            $res = $clientResponse + $this->tailBookResponse($booking_id, $filters['booking_item']);
        }

        return $res;
    }

    /**
     * @param array $filters
     * @param ApiBookingInspector $bookingInspector
     * @return array|null
     * @throws GuzzleException
     */
    public function retrieveBooking(array $filters, ApiBookingInspector $bookingInspector): array|null
    {
        $booking_id = $filters['booking_id'];
        $filters['search_id'] = $bookingInspector->search_id;
        $filters['booking_item'] = $bookingInspector->booking_item;
        $reservation = $this->extractReservationDetails($bookingInspector);

        $xmlPriceData = $this->hbsiClient->retrieveBooking($reservation);

        $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
        $dataResponse = json_decode(json_encode($response), true);

		$dataResponseToSave = $dataResponse;
        $dataResponseToSave['original'] = [
            'request' => $xmlPriceData['request'],
            'response' => $xmlPriceData['response']->asXML(),
        ];

        if (isset($rdataResponse['Errors'])) {
            $clientDataResponse = $dataResponse['Errors'];
        } else {
            $clientDataResponse = HbsiHotelBookingRetrieveBookingDto::RetrieveBookingToHotelBookResponseModel($filters, $dataResponse);
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponseToSave, $clientDataResponse, $supplierId, 'retrieve_booking',
            '', $bookingInspector->search_type,
        ]);

        if (isset($filters['supplier_data']) && $filters['supplier_data'] == 'true') {
            return (array)$dataResponse;
        } else {
            return $clientDataResponse;
        }
    }

    /**
     * @param array $filters
     * @param ApiBookingsMetadata $apiBookingsMetadata
     * @return array|null
     * @throws GuzzleException
     */
    public function cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata): array|null
    {
        $booking_id = $filters['booking_id'];
        $error = false;

        try
        {
            $xmlPriceData = $this->hbsiClient->cancelBooking($apiBookingsMetadata->booking_item_data);
            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $dataResponse = json_decode(json_encode($response), true);

            $dataResponseToSave = $dataResponse;
            $dataResponseToSave['original'] = [
                'request' => $xmlPriceData['request'],
                'response' => $xmlPriceData['response']->asXML(),
            ];

            if (isset($dataResponse['Errors'])) {
                $res = $dataResponse['Errors'];
                $error = true;
            } else {
                $res = [
                    'booking_item' => $apiBookingsMetadata->booking_item,
                    'status' => 'Room canceled.',
                ];
            }
        } catch (Exception $e) {
            $responseError = explode('response:', $e->getMessage());
            $responseErrorArr = json_decode($responseError[1], true);
            $res = [
                'booking_item' => $apiBookingsMetadata->booking_item,
                'status' => $responseErrorArr['message'],
            ];
            $dataResponseToSave = $responseErrorArr['message'];
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        if (!$error) SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponseToSave, $res, $supplierId, 'cancel_booking',
            'true', 'hotel',
        ]);
        else SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponseToSave, $res, $supplierId, 'cancel_booking',
            'error', 'hotel',
        ]);

        return $res;
    }

    public function listBookings(): array|null
    {
        $token_id = ChannelRenository::getTokenId(request()->bearerToken());
        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $itemsBooked = ApiBookingInspector::where('token_id', $token_id)
            ->where('supplier_id', $supplierId)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->distinct()
            ->get();

        $filters['booking_id'] = request()->get('booking_id');
        $filters['supplier_data'] = request()->get('supplier_data') ?? false;
        $data = [];
        foreach ($itemsBooked as $item) {
            $data[] = $this->retrieveBooking($filters, $item);
        }

        return $data;
    }

    public function changeBooking(array $filters): array|null
    {
        $dataResponseToSave = [];
        $clientResponse = [];
        $dataResponse = [];
        try {
            $xmlPriceData = $this->hbsiClient->modifyBook($filters);

            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $dataResponse = json_decode(json_encode($response), true);

            $dataResponseToSave = $dataResponse;
            $dataResponseToSave['original'] = [
                'request' => $xmlPriceData['request'],
                'response' => $xmlPriceData['response']->asXML(),
                'main_guest' => $xmlPriceData['main_guest'],
            ];
            if (!isset($dataResponse['Errors'])) {
                $clientResponse = $this->hbsiHotelBookDto->toHotelBookResponseModel($filters);
            } else {
                $clientResponse = $dataResponse['Errors'];
                $clientResponse['booking_item'] = $filters['booking_item'];
                $clientResponse['supplier'] = SupplierNameEnum::HBSI->value;
            }

        } catch (RequestException $e) {
            Log::error('HbsiBookApiController | changeBooking ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array)$dataResponse;
        } catch (Exception $e) {
        }

        if (!$dataResponseToSave) return [];

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $filters['booking_id'], $filters, $dataResponseToSave, $clientResponse, $supplierId, 'change_booking', '', 'hotel',
        ]);

        return (array)$dataResponse;
    }


    /**
     * @param ApiBookingInspector $bookingInspector
     * @return array
     */
    private function extractReservationDetails(ApiBookingInspector $bookingInspector): array
    {
        $response = json_decode(Storage::get($bookingInspector->response_path), true);
        $mainGuestData = json_decode(Storage::get(str_replace('.json', '.original.json', $bookingInspector->response_path)), true)['main_guest'];
        $mainGuest = json_decode($mainGuestData, true)['PersonName'];
        $reservation = $this->extractReservationId($response);

        $reservation['main_guest'] = $mainGuest;
        return $reservation;
    }

    private function extractReservationId(array $dataResponse): array
    {
        $hotelReservationID = $dataResponse['HotelReservations']['HotelReservation']['ResGlobalInfo']['HotelReservationIDs']['HotelReservationID'];
        $reservation = [];

        foreach ($hotelReservationID as $item) {
            $attributes = $item["@attributes"];
            if ($attributes["ResID_Type"] == "8") {
                $reservation["bookingId"] = $attributes["ResID_Value"];
            } elseif ($attributes["ResID_Type"] == "3") {
                $reservation["ReservationId"] = $attributes["ResID_Value"];
            }
        }

        return $reservation;
    }

    private function saveBookingInfo(array $filters, array $dataResponse): void
    {
        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $filters['supplier_id'] = $supplierId;

        $reservation = $this->extractReservationId($dataResponse);
        $reservation['main_guest'] = [
            'GivenName' => Arr::get($filters, 'booking_contact.first_name'),
            'Surname'   => Arr::get($filters, 'booking_contact.last_name'),
        ];

        SaveBookingMetadata::dispatch($filters, $reservation);
    }

}
