<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingMetadata;
use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveReservations;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingsMetadata;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingItemRepository;
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

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $inspectorBook = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'book', 'create', $bookingInspector->search_type
        ]);

        $error = true;
        try {
            Log::info('HbsiBookApiController | book | ' . json_encode($filters));
            $xmlPriceData = $this->hbsiClient->handleBook($filters, $inspectorBook);

            if (isset($xmlPriceData['error'])) {
                return [
                    'error' => $xmlPriceData['error'],
                    'booking_item' => $filters['booking_item'] ?? '',
                    'supplier' => SupplierNameEnum::HBSI->value
                ];
            }

            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $dataResponse = json_decode(json_encode($response), true) ?? [];

            $dataResponseToSave = $dataResponse;
            $dataResponseToSave['original'] = [
                'request' => $xmlPriceData['request'],
                'response' => $xmlPriceData['response']->asXML(),
                'main_guest' => $xmlPriceData['main_guest'],
            ];
            if (!isset($dataResponse['Errors'])) {
                # Save Booking Info
                $this->saveBookingInfo($filters, $dataResponse, json_decode($xmlPriceData['main_guest'], true));

                $inputConfirmationNumbers = $dataResponse['HotelReservations']['HotelReservation']['ResGlobalInfo']['HotelReservationIDs']['HotelReservationID'] ?? [];
                $confirmationNumbers = array_map(function ($item) {
                    return [
                        'confirmation_number' => $item['@attributes']['ResID_Value'],
                        'type' => self::CONFIRMATION[$item['@attributes']['ResID_Type']] ?? $item['@attributes']['ResID_Type'],
                    ];
                }, $inputConfirmationNumbers);
                $clientResponse = $this->hbsiHotelBookDto->toHotelBookResponseModel($filters, $confirmationNumbers);

                $error = false;
            } else {
                $clientResponse = $dataResponse['Errors'];
                $clientResponse['booking_item'] = $filters['booking_item'];
                $clientResponse['supplier'] = SupplierNameEnum::HBSI->value;
            }

        } catch (RequestException $e) {
            Log::error('HbsiBookApiController | book | RequestException ' . $e->getResponse()->getBody());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($inspectorBook, [], [], 'error',
                ['side' => 'app', 'message' => $e->getResponse()->getBody()]);

            return [
                'error' => 'Request Error. '.$e->getResponse()->getBody(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::HBSI->value
            ];
        } catch (\Exception $e) {
            Log::error('HbsiBookApiController | book | Exception ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($inspectorBook, [], [], 'error',
                ['side' => 'app', 'message' => $e->getMessage()]);

            return [
                'error' => 'Unexpected Error. '.$e->getMessage(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::HBSI->value
            ];
        }

        if (!$error) {
            SaveBookingInspector::dispatch($inspectorBook, $dataResponseToSave, $clientResponse);
            # Save Book data to Reservation
            SaveReservations::dispatch($booking_id, $filters, $dataPassengers);
        }

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
     * @param ApiBookingsMetadata $apiBookingsMetadata
     * @return array|null
     * @throws GuzzleException
     */
    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata): array|null
    {
        $booking_id = $filters['booking_id'];
        $filters['booking_item'] = $apiBookingsMetadata->booking_item;
        $filters['search_id'] = ApiBookingItemRepository::getSearchId($filters['booking_item']);

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'booking', 'retrieve', $apiBookingsMetadata->search_type,
        ]);

        $xmlPriceData = $this->hbsiClient->retrieveBooking(
            $apiBookingsMetadata->booking_item_data,
            $apiBookingsMetadata->hotel_supplier_id ?? null,
            $bookingInspector
        );

        $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();

        $dataResponse = json_decode(json_encode($response), true);

		$dataResponseToSave = $dataResponse;
        $dataResponseToSave['original'] = [
            'request' => $xmlPriceData['request'],
            'response' => $xmlPriceData['response']->asXML(),
        ];

        $clientDataResponse = $dataResponse['Errors'] ?? HbsiHotelBookingRetrieveBookingDto::RetrieveBookingToHotelBookResponseModel($filters, $dataResponse);

        SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, $clientDataResponse);

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

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $inspectorCansel = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'cancel_booking', 'true', 'hotel',
        ]);

        try
        {
            $xmlPriceData = $this->hbsiClient->cancelBooking(
                $apiBookingsMetadata->booking_item_data,
                $apiBookingsMetadata->hotel_supplier_id ?? null,
                $inspectorCansel
            );
            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $dataResponse = json_decode(json_encode($response), true);

            $dataResponseToSave = $dataResponse;
            $dataResponseToSave['original'] = [
                'request' => $xmlPriceData['request'],
                'response' => $xmlPriceData['response']->asXML(),
            ];


            if (isset($dataResponse['Errors'])) {
                $res = $dataResponse['Errors'];
                SaveBookingInspector::dispatch($inspectorCansel, $dataResponseToSave, $res, 'error',
                    ['side' => 'app', 'message' => $dataResponse['Errors']]);
            } else {
                $res = [
                    'booking_item' => $apiBookingsMetadata->booking_item,
                    'status' => 'Room canceled.',
                ];
                SaveBookingInspector::dispatch($inspectorCansel, $dataResponseToSave, $res);
            }
        } catch (Exception $e) {
            $responseError = explode('response:', $e->getMessage());
            $message = isset($responseError[1])
                ? json_decode($responseError[1], true)['message']
                : $e->getMessage();
            $res = [
                'booking_item' => $apiBookingsMetadata->booking_item,
                'status' => $message,
            ];
            $dataResponseToSave = $message;

            SaveBookingInspector::dispatch($inspectorCansel, $dataResponseToSave, $res, 'error',
                ['side' => 'app', 'message' => $message]);
        }

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
        $dataResponse = [];

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $filters['booking_id'], $filters, $supplierId, 'book', 'change-soft', 'hotel',
        ]);

        try {
            $xmlPriceData = $this->hbsiClient->modifyBook($filters, $bookingInspector);

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

            SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, $clientResponse);

        } catch (RequestException $e) {
            Log::error('HbsiBookApiController | changeBooking ' . $e->getResponse()->getBody());
            Log::error($e->getTraceAsString());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());

            SaveBookingInspector::dispatch($bookingInspector, $dataResponse, [], 'error',
                ['side' => 'app', 'message' => $e->getResponse()->getBody()]);

            return (array)$dataResponse;
        } catch (Exception $e) {
            Log::error('HbsiBookApiController | changeBooking ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($bookingInspector, [], [], 'error',
                ['side' => 'app', 'message' => $e->getMessage()]);

            return (array)$dataResponse;
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

    /**
     * This method can receive book $dataResponse or retrieveBooking confirmation_numbers array
     * @param array $dataResponse
     * @return array
     */
    private function extractReservationId(array $dataResponse): array
    {
        $reservation = [];

        if (Arr::has($dataResponse, 'HotelReservations'))
        {
            $hotelReservationID = $dataResponse['HotelReservations']['HotelReservation']['ResGlobalInfo']['HotelReservationIDs']['HotelReservationID'];

            foreach ($hotelReservationID as $item) {
                $attributes = $item["@attributes"];
                if ($attributes["ResID_Type"] == "8") {
                    $reservation["bookingId"] = $attributes["ResID_Value"];
                } elseif ($attributes["ResID_Type"] == "3") {
                    $reservation["ReservationId"] = $attributes["ResID_Value"];
                }
            }
        }
        elseif (! empty($dataResponse))
        {
            foreach ($dataResponse as $item) {
                if ($item["type_id"] == "8") {
                    $reservation["bookingId"] = $item["confirmation_number"];
                } elseif ($item["type_id"] == "3") {
                    $reservation["ReservationId"] = $item["confirmation_number"];
                }
            }
        }

        return $reservation;
    }

    private function saveBookingInfo(array $filters, array $dataResponse, array $mainGuest): void
    {
        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $filters['supplier_id'] = $supplierId;

        $reservation = $this->extractReservationId($dataResponse);
        $reservation['main_guest'] = $mainGuest['PersonName'];

        SaveBookingMetadata::dispatch($filters, $reservation);
    }

}
