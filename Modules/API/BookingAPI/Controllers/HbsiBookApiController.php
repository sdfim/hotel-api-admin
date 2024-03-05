<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveReservations;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ChannelRenository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\API\Suppliers\DTO\HBSI\HbsiHotelBookDto;
use Modules\API\Suppliers\DTO\HBSI\HbsiHotelBookingRetrieveBookingDto;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\Enums\SupplierNameEnum;
use Modules\Enums\TypeRequestEnum;

class HbsiBookApiController extends BaseBookApiController
{
    public function __construct(
        private readonly HbsiClient       $hbsiClient = new HbsiClient(),
        private readonly HbsiHotelBookDto $hbsiHotelBookDto = new HbsiHotelBookDto(),
    )
    {
    }

    /**
     * @param array $filters
     * @param array $passengersData
     * @return array|null
     */
    public function addPassengers(array $filters, array $passengersData): array|null
    {
        $booking_id = $filters['booking_id'];
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $bookingItemData = json_decode($bookingItem->booking_item_data, true);
        $rateOccupancy = $bookingItemData['rate_occupancy'];
        $occupancy = explode('-', $rateOccupancy);
        $adults = (int)$occupancy[0];
        $children = (int)$occupancy[1] + (int)$occupancy[2];

        $filters['search_id'] = ApiBookingInspector::where('booking_item', $filters['booking_item'])->first()->search_id;

        $res = [];

        if ($bookingItem->rate_type === 'completed' && $filters['booking_item'] !== $bookingItem->complete_id) {
            $bookingItemsSingle = ApiBookingInspector::where('booking_id', $booking_id)
                ->where('rate_type', 'single')
                ->where('complete_id', $filters['booking_item'])
                ->get();
            foreach ($bookingItemsSingle as $bookingItemSingle) {
                $iterFilters = $filters;
                $iterFilters['booking_item'] = $bookingItemSingle->booking_item;
                $res[] = $this->addPassengers($iterFilters, $passengersData);
            }
        }

        $bookingItemIsset = ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $filters['booking_item'])
            ->where('type', 'add_passengers');

        $apiSearchInspector = ApiSearchInspector::where('search_id', $filters['search_id'])->first()->request;
        $searchRequest = json_decode($apiSearchInspector, true);
        $countRooms = count($searchRequest['occupancy']);

        $type = ApiSearchInspector::where('search_id', $filters['search_id'])->first()->search_type;
        if (TypeRequestEnum::from($type) === TypeRequestEnum::HOTEL)
            for ($i = 1; $i <= $countRooms; $i++) {
                if (isset($passengersData['rooms'][$i]['passengers'])) {
                    $searchAdults = $searchRequest['occupancy'][$i - 1]['adults'];
                    $searchChildren = isset($searchRequest['occupancy'][$i - 1]['children_ages'])
                        ? count($searchRequest['occupancy'][$i - 1]['children_ages'])
                        : 0;
                    if ($searchAdults === $adults && $searchChildren === $children) {
                        if (!isset($filters['rooms'][$i])) $filters['rooms'][$i] = $passengersData['rooms'][$i]['passengers'];
                    }
                    ApiBookingItem::where('booking_item', $filters['booking_item'])->update(['room_by_query' => $i]);

                }
            }

        if ($bookingItemIsset->get()->count() > 0) {
            $bookingItemIsset->delete();
            $status = 'Passengers updated to booking.';
            $subType = 'updated';
        } else {
            $status = 'Passengers added to booking.';
            $subType = 'add';
        }

        if (empty($res)) {
            $res = [
                'booking_id' => $booking_id,
                'booking_item' => $filters['booking_item'],
                'status' => $status,
            ];

            $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
            SaveBookingInspector::dispatch([
                $booking_id, $filters, [], $res, $supplierId, 'add_passengers', $subType, 'hotel',
            ]);
        }

        return $res;
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
            $xmlPriceData = $this->hbsiClient->handleBook($filters);

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
                $error = false;
            } else {
                $clientResponse = $dataResponse;
                $clientResponse['booking_item'] = $filters['booking_item'];
                $clientResponse['supplier'] = SupplierNameEnum::HBSI->value;
            }

        } catch (RequestException $e) {
            Log::error('HbsiBookApiController | book | RequestException ' . $e->getResponse()->getBody());
            return ['error' => $e->getResponse()->getBody()];
        } catch (\Exception $e) {
            Log::error('HbsiBookApiController | book | Exception ' . $e->getMessage());
            return ['error' => $e->getMessage()];
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
            $clientDataResponse = $dataResponse;
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
     * @param ApiBookingInspector $bookingInspector
     * @return array|null
     * @throws GuzzleException
     */
    public function cancelBooking(array $filters, ApiBookingInspector $bookingInspector): array|null
    {
        $booking_id = $filters['booking_id'];
        $error = false;
        try {
            $reservation = $this->extractReservationDetails($bookingInspector);
            $xmlPriceData = $this->hbsiClient->cancelBooking($reservation);
            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $dataResponse = json_decode(json_encode($response), true);

            $dataResponseToSave = $dataResponse;
            $dataResponseToSave['original'] = [
                'request' => $xmlPriceData['request'],
                'response' => $xmlPriceData['response']->asXML(),
            ];

            if (isset($dataResponse['Errors'])) {
                $res = $dataResponse;
                $error = true;
            } else {
                $res = [
                    'booking_item' => $bookingInspector->booking_item,
                    'status' => 'Room canceled.',
                ];
            }

        } catch (Exception $e) {
            $responseError = explode('response:', $e->getMessage());
            $responseErrorArr = json_decode($responseError[1], true);
            $res = [
                'booking_item' => $bookingInspector->booking_item,
                'status' => $responseErrorArr['message'],
            ];
            $dataResponseToSave = $responseErrorArr['message'];
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        if (!$error) SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponseToSave, $res, $supplierId, 'cancel_booking',
            'true', $bookingInspector->search_type,
        ]);
        else SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponseToSave, $res, $supplierId, 'cancel_booking',
            'error', $bookingInspector->search_type,
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
                $clientResponse = $dataResponse;
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
        $hotelReservationID = $response['HotelReservations']['HotelReservation']['ResGlobalInfo']['HotelReservationIDs']['HotelReservationID'];
        $reservation = [];
        foreach ($hotelReservationID as $item) {
            $attributes = $item["@attributes"];
            if ($attributes["ResID_Type"] == "8") {
                $reservation["bookingId"] = $attributes["ResID_Value"];
            } elseif ($attributes["ResID_Type"] == "3") {
                $reservation["ReservationId"] = $attributes["ResID_Value"];
            }
        }
        $reservation['main_guest'] = $mainGuest;
        return $reservation;
    }


}
