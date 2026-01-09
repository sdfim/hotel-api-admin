<?php

namespace Modules\API\Suppliers\Oracle\Adapters;

use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveBookingMetadata;
use App\Jobs\SaveReservations;
use App\Models\ApiBookingInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\Suppliers\Base\Adapters\BaseHotelBookingAdapter;
use Modules\API\Suppliers\Base\Traits\HotelBookingavAilabilityChangeTrait;
use Modules\API\Suppliers\Base\Traits\HotelBookingavCancelTrait;
use Modules\API\Suppliers\Base\Traits\HotelBookingavRetrieveBookingTrait;
use Modules\API\Suppliers\Contracts\Hotel\Booking\HotelBookingSupplierInterface;
use Modules\API\Suppliers\Oracle\Client\OracleClient;
use Modules\API\Suppliers\Oracle\Transformers\OracleHotelBookingRetrieveBookingTransformer;
use Modules\API\Suppliers\Oracle\Transformers\OracleHotelBookTransformer;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;

class OracleHotelBookingAdapter extends BaseHotelBookingAdapter implements HotelBookingSupplierInterface
{
    use HotelBookingavAilabilityChangeTrait;
    use HotelBookingavCancelTrait;
    use HotelBookingavRetrieveBookingTrait;

    public function __construct(
        private readonly OracleClient $client,
        private readonly OracleHotelAdapter $hotelAdapter,
        private readonly OracleHotelBookTransformer $oracleHotelBookTransformer,
        private readonly OracleHotelBookingRetrieveBookingTransformer $retrieveTransformer,
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
            $booking_id,
            $filters,
            $supplierId,
            'book',
            'create',
            $bookingInspector->search_type,
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

            SaveBookingInspector::dispatch(
                $inspectorBook,
                [],
                [],
                'error',
                ['side' => 'app', 'message' => $e->getResponse()->getBody()]
            );

            return [
                'error' => 'Request Error. '.$e->getResponse()->getBody(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::ORACLE->value,
            ];
        } catch (Exception $e) {
            Log::info("BOOK ACTION - ERROR - Oracle - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]);
            Log::error('OracleHotelBookingAdapter | book | Exception '.$e->getMessage());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch(
                $inspectorBook,
                [],
                [],
                'error',
                ['side' => 'app', 'message' => $e->getMessage()]
            );

            return [
                'error' => 'Unexpected Error. '.$e->getMessage(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::ORACLE->value,
            ];
        }

        if (! $error) {
            $isTestScenario = Arr::get($filters, 'is_test_scenario', false);
            $dispatchMethod = $isTestScenario ? 'dispatchSync' : 'dispatch';
            SaveBookingInspector::$dispatchMethod($inspectorBook, $dataResponseToSave, $clientResponse);
            // Save Book data to Reservation
            SaveReservations::$dispatchMethod($booking_id, $filters, $dataPassengers, request()->bearerToken());
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

        $isTestScenario = Arr::get($filters, 'is_test_scenario', false);
        $dispatchMethod = $isTestScenario ? 'dispatchSync' : 'dispatch';
        SaveBookingMetadata::$dispatchMethod($filters, $reservation);
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
