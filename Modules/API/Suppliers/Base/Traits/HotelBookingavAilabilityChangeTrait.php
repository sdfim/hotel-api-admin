<?php

namespace Modules\API\Suppliers\Base\Traits;

use App\Jobs\SaveBookingItems;
use App\Jobs\SaveSearchInspector;
use App\Models\ApiBookingItem;
use App\Models\Supplier;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\Enums\SupplierNameEnum;

trait HotelBookingavAilabilityChangeTrait
{
    public function availabilityChange(array $filters, SupplierNameEnum $supplier, $type = 'change'): ?array
    {
        $booking_item = $filters['booking_item'];
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();
        $searchId = (string) Str::uuid();
        $hotelId = Arr::get(json_decode($bookingItem->booking_item_data, true), 'hotel_supplier_id');
        $supplierId = Supplier::where('name', $supplier->value)->first()->id;
        $searchInspector = ApiSearchInspectorRepository::newSearchInspector([$searchId, $filters, [$supplierId], $type, 'hotel']);

        $response = $this->hotelAdapter->price($filters, $searchInspector, [], $hotelId);

        $giataIds = Arr::get($filters, 'giata_ids', []);

        $handleResponse = $this->hotelAdapter->processPriceResponse(
            $response,
            $filters,
            $searchId,
            $this->pricingRulesService->rules($filters, $giataIds),
            $this->pricingRulesService->rules($filters, $giataIds, true),
            $giataIds
        );

        $clientResponse = $handleResponse['clientResponse'];
        $content = ['count' => $handleResponse['countResponse'], 'query' => $filters, 'results' => $handleResponse['dataResponse']];
        $result = [
            'count' => $handleResponse['countClientResponse'],
            'total_pages' => max($handleResponse['totalPages']),
            'query' => $filters,
            'results' => $clientResponse,
        ];

        /** Save data to Inspector */
        SaveSearchInspector::dispatch($searchInspector, $handleResponse['dataOriginal'] ?? [], $content, $result);

        /** Save booking_items */
        if (!empty($handleResponse['bookingItems'])) {
            foreach ($handleResponse['bookingItems'] as $items) {
                SaveBookingItems::dispatch($items);
            }
        }

        return [
            'result' => $clientResponse[$supplier->value],
            $type . '_search_id' => $searchId,
        ];
    }
}
