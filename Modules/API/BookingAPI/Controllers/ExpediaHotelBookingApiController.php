<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiSearchInspectorRepository as SearchRepository;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\Enums\SupplierNameEnum;

class ExpediaHotelBookingApiController extends BaseHotelBookingApiController
{
    public function __construct(
        private readonly RapidClient $rapidClient = new RapidClient(),
    ) {
    }

    public function addItem(array $filters, string $type = 'add_item', array $headers = []): array|null
    {
        // step 1 Read Inspector, Get link 'price_check'
        $linkPriceCheck = SearchRepository::getLinkPriceCheck($filters);

        // step 2 Get POST link for booking
        // TODO: need check if price changed
        $props = $this->getPathParamsFromLink($linkPriceCheck);

        $booking_id = $filters['booking_id'] ?? (string) Str::uuid();

        if ($type === 'change') $filters['search_id'] = $filters['change_search_id'];
        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $bookingInspector = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, $type, 'price_check', 'hotel',
        ]);

        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken'], $headers);
            $content = json_decode($response->getBody()->getContents(), true);
            $content['original']['response'] = $content;
            $content['original']['request']['params'] = $props['paramToken'];
            $content['original']['request']['path'] = $props['path'];

            SaveBookingInspector::dispatch($bookingInspector, $content, []);
        } catch (RequestException $e) {
            Log::error('ExpediaHotelBookingApiHandler | ' . $type . ' | price_check ' . $e->getResponse()->getBody());
            Log::error($e->getTraceAsString());
            $content = json_decode(''.$e->getResponse()->getBody());

            SaveBookingInspector::dispatch($bookingInspector, $content, [], 'error', ['error' => $e->getMessage()]);

            return (array) $content;
        }

        if (! $content) {
            return [];
        }

        return ['booking_id' => $booking_id];
    }

    private function getPathParamsFromLink(string $link): array
    {
        $arr_link = explode('?', $link);
        $path = $arr_link[0];
        $arr_param = explode('=', $arr_link[1]);
        $paramToken = [$arr_param[0] => str_replace('token=', '', $arr_link[1])];

        return ['path' => $path, 'paramToken' => $paramToken];
    }
}
