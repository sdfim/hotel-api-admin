<?php

namespace Modules\HotelContentRepository\Services;

use Illuminate\Support\Arr;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\Hotel;

class HotelService
{
    public function __construct(
        protected readonly HotelContentApiService $hotelContentApiService,
        protected readonly HotelContentApiTransformerService $detailDataTransformer,
    ) {}

    public function getHotelData($giataCode)
    {
        $resultsSuppliers = $this->getSupplierResults($giataCode, true);

        $hotel = Hotel::where('giata_code', $giataCode)->first();
        $repoData = $resultsSuppliers[SupplierNameEnum::EXPEDIA->value] ?? [];
        $this->detailDataTransformer->updateContentResultWithInternalData($repoData, $hotel);
        $repoData['descriptions'] = $this->detailDataTransformer->getHotelDescriptions($hotel);

        return array_merge(['repo' => $repoData], $resultsSuppliers);
    }

    public function getDetailData($giataCode)
    {
        $resultsSuppliers = $this->getSupplierResults($giataCode);

        $hotel = Hotel::where('giata_code', $giataCode)->first();
        $repoData = $resultsSuppliers[SupplierNameEnum::EXPEDIA->value] ?? [];
        $this->detailDataTransformer->updateContentResultWithInternalData($repoData, $hotel);
        $repoData['descriptions'] = $this->detailDataTransformer->getHotelDescriptions($hotel);
        $repoData['rooms'] = $this->detailDataTransformer->getHotelRooms($hotel);
        $repoData['images'] = $this->detailDataTransformer->getPropertyImages($hotel);

        $transformedResults = array_map(function ($results) {
            return $results;
        }, $resultsSuppliers);

        return [
            'repo' => $repoData,
            ...$transformedResults,
        ];
    }

    public function getHotelImagesData($giataCode)
    {
        $resultsSuppliers = $this->getSupplierResults($giataCode);

        $hotel = Hotel::where('giata_code', $giataCode)->first();
        $repoData = $this->detailDataTransformer->getPropertyImages($hotel);

        $imagesData = ['repo' => $repoData];
        foreach ($resultsSuppliers as $supplier => $results) {
            $imagesData[strtolower($supplier)] = Arr::get($results, 'images', []);
        }

        return $imagesData;
    }

    private function getSupplierResults($giataCode, bool $clean = false): array
    {
        $resultsSuppliers = [];

        foreach (SupplierNameEnum::getContentSupplierValues() as $supplier) {
            /** @var SupplierInterface $supplierService */
            $supplierService = app(SupplierInterface::class, ['supplier' => $supplier]);
            $resultsSuppliers[$supplier] = Arr::get($supplierService->getResults([$giataCode]), 0, []);
            if ($clean) {
                unset($resultsSuppliers[$supplier]['images']);
                unset($resultsSuppliers[$supplier]['rooms']);
            }
        }

        return $resultsSuppliers;
    }

    public function getDetailRespose($giataCode)
    {
        return Arr::get($this->hotelContentApiService->fetchDetailResults([$giataCode], true), 0, []);
    }
}
