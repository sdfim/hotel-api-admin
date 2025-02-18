<?php

namespace Modules\HotelContentRepository\Services;

use Illuminate\Support\Arr;
use Modules\API\Controllers\ApiHandlers\HotelApiHandlerV1 as HotelHandler;
use Modules\API\Services\DetailDataTransformer;
use Modules\HotelContentRepository\Models\Hotel;

class HotelService
{
    public function __construct(
        protected readonly HotelHandler $hotelHandler,
        protected readonly DetailDataTransformer $detailDataTransformer,
    ) {}

    public function getHotelData($giataCode)
    {
        $resultsExpedia = Arr::get($this->hotelHandler->getExpediaResults([$giataCode]), 0, []);
        unset($resultsExpedia['images']);
        unset($resultsExpedia['rooms']);
        unset($resultsExpedia['supplier_information']);
        $resultsIcePortal = Arr::get($this->hotelHandler->getIcePortalResults([$giataCode]), 0, []);
        unset($resultsIcePortal['images']);
        unset($resultsIcePortal['rooms']);
        unset($resultsIcePortal['supplier_information']);

        $hotel = Hotel::where('giata_code', $giataCode)->first();
        $repoData = $resultsExpedia;
        $this->detailDataTransformer->updateResultWithInternalData($repoData, $hotel);
        $repoData['descriptions'] = $this->detailDataTransformer->getHotelDescriptions($hotel);

        return [
            'repo' => $repoData,
            'expedia' => $resultsExpedia,
            'icePortal' => $resultsIcePortal,
        ];
    }

    public function getDetailData($giataCode)
    {
        $resultsExpedia = Arr::get($this->hotelHandler->getExpediaResults([$giataCode]), 0, []);
        $resultsIcePortal = Arr::get($this->hotelHandler->getIcePortalResults([$giataCode]), 0, []);

        $hotel = Hotel::where('giata_code', $giataCode)->first();
        $repoData = $resultsExpedia;
        $this->detailDataTransformer->updateResultWithInternalData($repoData, $hotel);
        $repoData['descriptions'] = $this->detailDataTransformer->getHotelDescriptions($hotel);
        $repoData['rooms'] = $this->detailDataTransformer->getHotelRooms($hotel);
        $repoData['images'] = $this->detailDataTransformer->getPropertyImages($hotel);

        return [
            'repo' => $repoData,
            'expedia' => $resultsExpedia,
            'icePortal' => $resultsIcePortal,
        ];
    }

    public function getDetailRespose($giataCode)
    {
        return Arr::get($this->hotelHandler->fetchDetailResults([$giataCode]), 0, ['On Sale' => 'OFF']);
    }

    public function getHotelImagesData($giataCode)
    {
        $resultsIcePortal = Arr::get($this->hotelHandler->getIcePortalResults([$giataCode]), 0, []);
        $resultsExpedia = Arr::get($this->hotelHandler->getExpediaResults([$giataCode]), 0, []);

        $hotel = Hotel::where('giata_code', $giataCode)->first();
        $repoData = $this->detailDataTransformer->getPropertyImages($hotel);

        return [
            'repo' => $repoData,
            'expedia' => Arr::get($resultsExpedia, 'images', []),
            'icePortal' => Arr::get($resultsIcePortal, 'images', []),
        ];
    }
}
