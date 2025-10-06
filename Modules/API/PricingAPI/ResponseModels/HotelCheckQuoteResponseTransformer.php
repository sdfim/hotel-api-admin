<?php

namespace Modules\API\PricingAPI\ResponseModels;

use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Support\Arr;
use Modules\API\Services\HotelBookingCheckQuoteService;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Services\HotelContentApiTransformerService;

class HotelCheckQuoteResponseTransformer
{
    public static function transform(
        $dataFirstSearch,
        $data,
        $giata_id,
        $bookingItem,
        $matchedRooms,
        $parent_booking_item,
    ): HotelCheckQuoteResponseModel {
        $service = app(HotelBookingCheckQuoteService::class);

        $searchId = Arr::get($data, 'check_quote_search_id');
        $hotel = Hotel::where('giata_code', $giata_id)->first();
        $search = ApiSearchInspectorRepository::getSearchInLoop($searchId);

        $fieldsToCompare = ['total_net', 'total_tax', 'total_fees', 'total_price', 'markup'];

        $model = HotelCheckQuoteResponseFactory::create();
        $model->setComparisonOfAmounts($service->compareFieldSums($fieldsToCompare, $dataFirstSearch, $matchedRooms));
        $model->setCheckQuoteSearchId($searchId);
        $model->setHotelImage($hotel?->product?->hero_image ? \Storage::url($hotel->product->hero_image) : null);
        $model->setAttributes($hotel?->product?->attributes ? app(HotelContentApiTransformerService::class)->getHotelAttributes($hotel) : []);
        $model->setEmailVerification(ApiBookingInspectorRepository::getEmailVerificationBookingItem($bookingItem->booking_item));
        $model->setCheckQuoteSearchQuery(json_decode($search->request, true));
        $model->setGiataId($giata_id);
        $model->setBookingItem($parent_booking_item);
        $model->setBookingId(ApiBookingInspectorRepository::getBookIdByBookingItem($bookingItem->booking_item));
        $model->setCurrentSearch(array_values($matchedRooms));
        $model->setFirstSearch($dataFirstSearch);

        return $model;
    }
}
