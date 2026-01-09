<?php

namespace Modules\API\Suppliers\Base\Traits;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingsMetadata;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use Illuminate\Support\Arr;

trait HotelBookingavRetrieveBookingTrait
{
    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, bool $isSync = false): ?array
    {
        $supplier = $this->supplier();
        $booking_id = $filters['booking_id'];
        $filters['booking_item'] = $apiBookingsMetadata->booking_item;
        $filters['search_id'] = ApiBookingItemRepository::getSearchId($filters['booking_item']);

        $supplierId = Supplier::where('name', $supplier->value)->first()->id;
        $bookingInspector = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id,
            $filters,
            $supplierId,
            'book',
            'retrieve',
            $apiBookingsMetadata->search_type,
        ]);

        if (! $this->client || ! $this->retrieveTransformer) {
            return ['error' => "Client or Transformer not found for supplier: {$supplier->value}"];
        }

        $retrieveData = $this->client->retrieve($apiBookingsMetadata, $bookingInspector);

        if (empty($retrieveData) || (isset($retrieveData['errors']) && ! empty($retrieveData['errors'])) || (isset($retrieveData['Errors']) && ! empty($retrieveData['Errors'])) || (isset($retrieveData['error']) && ! empty($retrieveData['error']))) {
            return $retrieveData;
        }

        $clientDataResponse = Arr::get($retrieveData, 'response') ?
            $this->retrieveTransformer::RetrieveBookingToHotelBookResponseModel($filters, Arr::get($retrieveData, 'response'))
            : Arr::get($retrieveData, 'errors');

        $dataResponseToSave = $this->prepareRetrieveDataToSave($retrieveData, $clientDataResponse);

        if ($isSync) {
            SaveBookingInspector::dispatchSync($bookingInspector, $dataResponseToSave, $clientDataResponse);
        } else {
            SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, $clientDataResponse);
        }

        if (isset($filters['supplier_data']) && $filters['supplier_data'] == 'true') {
            return $this->getRetrieveOriginalResponse($retrieveData);
        } else {
            return $clientDataResponse;
        }
    }

    protected function prepareRetrieveDataToSave(array $retrieveData, ?array $clientDataResponse): array
    {
        return [
            'original' => [
                'request' => $retrieveData['request'] ?? [],
                'response' => $retrieveData['response'] ?? $retrieveData,
            ],
        ];
    }

    protected function getRetrieveOriginalResponse(array $retrieveData): array
    {
        return $retrieveData['response'] ?? $retrieveData;
    }
}
