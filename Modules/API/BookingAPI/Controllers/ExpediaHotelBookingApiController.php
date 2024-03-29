<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\Supplier;
use App\Repositories\ApiSearchInspectorRepository as SearchRepository;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\Enums\SupplierNameEnum;

class ExpediaHotelBookingApiController extends BaseHotelBookingApiController
{
    /**
     * @param RapidClient $rapidClient
     */
    public function __construct(
        private readonly RapidClient $rapidClient = new RapidClient(),
    )
    {
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function addItem(array $filters): array|null
    {
        # step 1 Read Inspector, Get link 'price_check'
        $linkPriceCheck = SearchRepository::getLinkPriceCheck($filters);

        # step 2 Get POST link for booking
        // TODO: need check if price changed
        $props = $this->getPathParamsFromLink($linkPriceCheck);
        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken']);
            $content = json_decode($response->getBody()->getContents(), true);
            $content['original']['response'] = $content;
            $content['original']['request']['params'] = $props['paramToken'];
            $content['original']['request']['path'] = $props['path'];
        } catch (RequestException $e) {
            Log::error('ExpediaHotelBookingApiHandler | addItem | price_check ' . $e->getResponse()->getBody());
            $content = json_decode('' . $e->getResponse()->getBody());
            return (array)$content;
        }

        if (!$content) {
            return [];
        }

        $booking_id = $filters['booking_id'] ?? (string)Str::uuid();

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, $content, [], $supplierId, 'add_item', 'price_check', 'hotel',
        ]);

        return ['booking_id' => $booking_id];
    }

    /**
     * @param string $link
     * @return array
     */
    private function getPathParamsFromLink(string $link): array
    {
        $arr_link = explode('?', $link);
        $path = $arr_link[0];
        $arr_param = explode('=', $arr_link[1]);
        $paramToken = [$arr_param[0] => str_replace('token=', '', $arr_link[1])];

        return ['path' => $path, 'paramToken' => $paramToken];
    }
}
