<?php

namespace Modules\API\Suppliers\Oracle\Adapters;

use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveBookingMetadata;
use App\Jobs\SaveReservations;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingsMetadata;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\Suppliers\Base\Adapters\BaseHotelBookingAdapter;
use Modules\API\Suppliers\Base\Traits\HotelBookingTrait;
use Modules\API\Suppliers\Contracts\Hotel\Booking\HotelBookingSupplierInterface;
use Modules\API\Suppliers\Oracle\Client\OracleClient;
use Modules\API\Suppliers\Oracle\Transformers\OracleHotelBookingRetrieveBookingTransformer;
use Modules\API\Suppliers\Oracle\Transformers\OracleHotelBookTransformer;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;

class OracleHotelBookingAdapter extends BaseHotelBookingAdapter implements HotelBookingSupplierInterface
{
    use HotelBookingTrait;

    public function __construct(
        private readonly OracleClient $client,
        private readonly OracleHotelAdapter $hotelAdapter,
        private readonly OracleHotelBookTransformer $oracleHotelBookTransformer,
        private readonly PricingRulesTools $pricingRulesService,
    ) {}

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::ORACLE;
    }

    public function book(array $filters, ApiBookingInspector $bookingInspector): ?array
    {
        $booking_id = $bookingInspector->booking_id;
        $filters['search_id'] = $bookingInspector->search_id;
        $filters['booking_item'] = $bookingInspector->booking_item;

        Log::info("BOOK ACTION - Oracle - $booking_id", ['filters' => $filters]);

        $passengers = ApiBookingInspectorRepository::getPassengers($booking_id, $filters['booking_item']);

        if (! $passengers) {
            Log::info("BOOK ACTION - ERROR - Oracle - $booking_id", ['error' => 'Passengers not found', 'filters' => $filters]);

            return [
                'error' => 'Passengers not found.',
                'booking_item' => $filters['booking_item'],
            ];
        } else {
            $passengersArr = $passengers->toArray();
            $dataPassengers = json_decode($passengersArr['request'], true);
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::ORACLE->value)->first()->id;
        $inspectorBook = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'book', 'create', $bookingInspector->search_type,
        ]);

        $error = true;
        try {
            Log::info('OracleHotelBookingAdapter | book | '.json_encode($filters));
            Log::info("BOOK ACTION - REQUEST TO Oracle START - Oracle - $booking_id", ['filters' => $filters]);
            $sts = microtime(true);
            $bookingData = $this->client->book($filters, $inspectorBook);
            Log::info("BOOK ACTION - REQUEST TO Oracle FINISH - Oracle - $booking_id", ['time' => (microtime(true) - $sts).' seconds', 'filters' => $filters]);

            $dataResponseToSave['original'] = [
                'request' => $bookingData['request'],
                'response' => $bookingData['response'],
                'main_guest' => $bookingData['main_guest'],
            ];
            if (Arr::get($bookingData, 'response')) {
                // Save Booking Info
                $this->saveBookingInfo($filters, $bookingData, $bookingData['main_guest']);

                $clientResponse = $this->oracleHotelBookTransformer

                    ->toHotelBookResponseModel($filters, [
                        'confirmationNumber' => $this->getConfirmationNumber($bookingData),
                        'reservationNumber' => $this->getReservationNumber($bookingData),
                    ]);

                $error = false;
            } else {
                $clientResponse = Arr::get($bookingData, 'response.errors', []);
                $clientResponse['booking_item'] = $filters['booking_item'];
                $clientResponse['supplier'] = SupplierNameEnum::ORACLE->value;
            }

        } catch (RequestException $e) {
            Log::info("BOOK ACTION - ERROR - Oracle - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]);
            Log::error('OracleHotelBookingAdapter | book | RequestException '.$e->getResponse()->getBody());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($inspectorBook, [], [], 'error',
                ['side' => 'app', 'message' => $e->getResponse()->getBody()]);

            return [
                'error' => 'Request Error. '.$e->getResponse()->getBody(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::ORACLE->value,
            ];
        } catch (Exception $e) {
            Log::info("BOOK ACTION - ERROR - Oracle - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]);
            Log::error('OracleHotelBookingAdapter | book | Exception '.$e->getMessage());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($inspectorBook, [], [], 'error',
                ['side' => 'app', 'message' => $e->getMessage()]);

            return [
                'error' => 'Unexpected Error. '.$e->getMessage(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::ORACLE->value,
            ];
        }

        if (! $error) {
            SaveBookingInspector::dispatch($inspectorBook, $dataResponseToSave, $clientResponse);
            // Save Book data to Reservation
            SaveReservations::dispatch($booking_id, $filters, $dataPassengers, request()->bearerToken());
        }

        if (! $bookingData) {
            Log::info("BOOK ACTION - ERROR - Oracle - $booking_id", ['error' => 'Empty dataResponse', 'filters' => $filters]);

            return [];
        }

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) {
            $res = $bookingData;
        } elseif ($error) {
            $res = $clientResponse;
        } else {
            $res = $clientResponse + $this->tailBookResponse($booking_id, $filters['booking_item']);
        }

        return $res;
    }

    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, bool $isSync = false): ?array
    {
        $booking_id = $filters['booking_id'];
        $filters['booking_item'] = $apiBookingsMetadata->booking_item;
        $filters['search_id'] = ApiBookingItemRepository::getSearchId($filters['booking_item']);

        $supplierId = Supplier::where('name', SupplierNameEnum::ORACLE->value)->first()->id;
        $bookingInspector = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'book', 'retrieve', $apiBookingsMetadata->search_type,
        ]);

        $retrieveData = $this->client->retrieve(
            $apiBookingsMetadata,
            $bookingInspector
        );

        if (! empty($retrieveData['errors'])) {
            return [];
        }

        $dataResponseToSave['original'] = [
            'request' => $retrieveData['request'],
            'response' => $retrieveData['response'],
        ];

        $clientDataResponse = Arr::get($retrieveData, 'response') ?
            OracleHotelBookingRetrieveBookingTransformer::RetrieveBookingToHotelBookResponseModel($filters, Arr::get($retrieveData, 'response'))
            : Arr::get($retrieveData, 'errors');

        SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, $clientDataResponse);

        if (isset($filters['supplier_data']) && $filters['supplier_data'] == 'true') {
            return Arr::get($retrieveData, 'response');
        } else {
            return $clientDataResponse;
        }
    }

    public function cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, int $iterations = 0): ?array
    {
        $booking_id = $filters['booking_id'];

        $supplierId = Supplier::where('name', SupplierNameEnum::ORACLE->value)->first()->id;
        $inspectorCansel = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'cancel_booking', 'true', 'hotel',
        ]);

        try {
            $cancelData = $this->client->cancel(
                $apiBookingsMetadata,
                $inspectorCansel
            );

            $dataResponseToSave['original'] = [
                'request' => $cancelData['request'],
                'response' => $cancelData['response'],
            ];

            if (Arr::get($cancelData, 'errors')) {
                $res = Arr::get($cancelData, 'errors');
            } else {
                $res = [
                    'booking_item' => $apiBookingsMetadata->booking_item,
                    'status' => 'Room canceled.',
                ];

                SaveBookingInspector::dispatch($inspectorCansel, $dataResponseToSave, $res);
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $res = [
                'booking_item' => $apiBookingsMetadata->booking_item,
                'status' => $message,
                'Error' => $message,
            ];

            $dataResponseToSave = is_array($message) ? $message : [];

            SaveBookingInspector::dispatch($inspectorCansel, $dataResponseToSave, $res, 'error',
                ['side' => 'app', 'message' => $message]);
        }

        return $res;
    }

    public function listBookings(): ?array
    {
        return [];
    }

    public function changeBooking(array $filters, string $mode = 'soft'): ?array
    {
        return [];
    }

    public function priceCheck(array $filters): ?array
    {
        return [];
    }

    private function saveBookingInfo(array $filters, array $bookingData, array $mainGuest): void
    {
        $filters['supplier_id'] = Supplier::where('name', SupplierNameEnum::ORACLE->value)->first()->id;

        $reservation['bookingId'] = $this->getConfirmationNumber($bookingData);
        $reservation['main_guest']['Surname'] = Arr::get($mainGuest, '0.family_name', '');
        $reservation['main_guest']['GivenName'] = Arr::get($mainGuest, '0.given_name', '');

        SaveBookingMetadata::dispatch($filters, $reservation);
    }

    private function getConfirmationNumber(array $bookingData): ?string
    {
        $targetLink = Arr::first($bookingData['response'], function ($link) {
            return Str::contains(Arr::get($link, 'href'), 'confirmationNumberList');
        });

        $confirmationNumber = null;
        if ($targetLink) {
            $fullUrl = Arr::get($targetLink, 'href');
            $parsedUrl = parse_url($fullUrl);
            $queryString = $parsedUrl['query'] ?? null;
            if ($queryString) {
                parse_str($queryString, $queryVariables);
                $confirmationNumber = Arr::get($queryVariables, 'confirmationNumberList');
            }
        }

        return $confirmationNumber;
    }

    private function getReservationNumber(array $bookingData): ?string
    {
        $targetLink = Arr::first(Arr::get($bookingData, 'response', []), function ($link) {
            $operationId = Arr::get($link, 'operationId');

            return $operationId === 'getReservation' || $operationId === 'putReservation';
        });
        if ($targetLink) {
            $fullUrl = Arr::get($targetLink, 'href');
            $parsedUrl = parse_url($fullUrl);
            $path = $parsedUrl['path'] ?? null;
            if ($path) {
                $pathSegments = explode('/', $path);
                $reservationNumber = end($pathSegments);
                if (is_numeric($reservationNumber) && $reservationNumber) {
                    return $reservationNumber;
                }
            }
        }

        return null;
    }
}
