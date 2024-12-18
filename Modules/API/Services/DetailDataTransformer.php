<?php

namespace Modules\API\Services;

use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentDetailResponseFactory;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\Enums\SupplierNameEnum;

class DetailDataTransformer
{
    public function initializeContentSource(array $giataCodes): array
    {
        $contentSource = [];
        foreach ($giataCodes as $giata_code) {
            $contentSource[$giata_code] = SupplierNameEnum::EXPEDIA->value;
        }
        return $contentSource;
    }

    public function buildStructureSource($repoData, array &$contentSource): array
    {
        $structureSource = [];
        foreach ($repoData as $item) {
            $structureSource[$item->giata_code] = [
                'content_source' => $item->product->contentSource->name,
                'room_images' => $item->roomImagesSource->name,
                'property_images' => $item->product->propertyImagesSource->name,
            ];
            $contentSource[$item->giata_code] = $item->product->contentSource->name;
        }
        return $structureSource;
    }

    public function createEmptyHotelResponse(string $giataCode): array
    {
        $hotelResponse = ContentDetailResponseFactory::create();
        $hotelResponse->setGiataHotelCode($giataCode);
        $hotelResponse->setRooms([]);
        return $hotelResponse->toArray();
    }

    public function createEmptyContentHotelResponse(string $giataCode): array
    {
        $hotelResponse = ContentSearchResponseFactory::create();
        $hotelResponse->setGiataHotelCode($giataCode);
        return $hotelResponse->toArray();
    }

    public function updateContentResultWithHotelData(array &$result, $hotel, array $structureSource, array $transformedResultsIcePortal): void
    {
        // create as Internal data by default
        $this->updateContentResultWithInternalData($result, $hotel);
        $internalPropertyImages = $this->getPropertyImages($hotel);
        $internalPropertyDescription = $this->getHotelDescriptions($hotel);

        if ($structureSource['property_images'] == 'Expedia') {
            $result['images'] = array_merge($internalPropertyImages, $result['images']);
        } elseif ($structureSource['property_images'] == 'IcePortal') {
            $result['images'] = array_merge($internalPropertyImages, Arr::get($transformedResultsIcePortal, $hotel->giata_code . '.images', []));
        } else {
            $result['images'] = $internalPropertyImages;
        }

        if ($structureSource['content_source'] == 'Expedia') {
            $result['description'] = array_merge($internalPropertyDescription, $result['description']);
        } elseif ($structureSource['content_source'] == 'IcePortal') {
            $result['description'] = array_merge($internalPropertyDescription, Arr::get($transformedResultsIcePortal, $hotel->giata_code . '.description', []));
        } else {
            $result['description'] = $internalPropertyDescription;
        }

        $result['structure'] = $structureSource;
    }

    public function updateResultWithHotelData(array &$result, $hotel, array $structureSource, array $resultsIcePortal, array $romsImagesData): void
    {
         // create as Internal data by default
         $this->updateResultWithInternalData($result, $hotel);
         $internalPropertyImages = $this->getPropertyImages($hotel);
         $internalPropertyDescription = $this->getHotelDescriptions($hotel);

        $internalRooms = $this->getHotelRooms($hotel);
        $existingRoomCodes = [
            'Expedia' => [],
            'IcePortal' => [],
        ];
        foreach ($internalRooms as $room) {
            $existingRoomCodes['Expedia'][] = $room['supplier_codes']['Expedia'] ?? null;
            $existingRoomCodes['IcePortal'][] = $room['supplier_codes']['IcePortal'] ?? null;
        }

        $result['rooms'] = array_merge($internalRooms, $result['rooms']);

        foreach ($result['rooms'] as &$room) {
            $contentSupplier = Arr::get($room, 'content_supplier', '');
            $supplierRoomId = Arr::get($room, 'supplier_room_id', '');
            if (!isset($existingRoomCodes[$contentSupplier])) continue;
            if (in_array($supplierRoomId, $existingRoomCodes[$contentSupplier])) {
                unset($room);
            }
        }

        $transformedResultsIcePortal = [];
        foreach ($resultsIcePortal as $item) {
            $giataHotelCode = $item['giata_hotel_code'];
            $transformedResultsIcePortal[$giataHotelCode] = $item;
        }

        if ($structureSource['property_images'] == 'Expedia') {
            $result['images'] = array_merge($internalPropertyImages, $result['images']);
        } elseif ($structureSource['property_images'] == 'IcePortal') {
            $result['images'] = array_merge($internalPropertyImages, Arr::get($transformedResultsIcePortal, $hotel->giata_code . '.images', []));
        }

        $giataId = $hotel->giata_code;
        if ($structureSource['room_images'] == 'Expedia') {
            foreach ($result['rooms'] as &$room) {
                $externalCode = Arr::get($room, 'supplier_codes.external_code', '');
                $room['images'] = array_merge($room['images'], $romsImagesData[$giataId][$externalCode]['Expedia'] ?? []);
            }
        } elseif ($structureSource['room_images'] == 'IcePortal') {
            foreach ($result['rooms'] as &$room) {
                $externalCode = Arr::get($room, 'supplier_codes.external_code', '');
                $room['images'] = array_merge($room['images'], $romsImagesData[$giataId][$externalCode]['IcePortal'] ?? []);
            }
        }

        if ($structureSource['content_source'] == 'Expedia') {
            $result['descriptions'] = array_merge($internalPropertyDescription, $result['descriptions']);
        } elseif ($structureSource['content_source'] == 'IcePortal') {
            $result['descriptions'] = array_merge($internalPropertyDescription, Arr::get($transformedResultsIcePortal, $hotel->giata_code . '.descriptions', []));
        }

        $result['structure'] = $structureSource;
    }

    private function getPropertyImages($hotel): array
    {
        return $hotel->product->galleries
            ->flatMap(function ($gallery) {
                return $gallery->images->pluck('image_url');
            })->take(25)->all();
    }

    private function updateResultWithInternalData(array &$result, $hotel): void
    {
        $result['hotel_name'] = $hotel->product->name;
        $result['latitude'] = $hotel->product->lat;
        $result['longitude'] = $hotel->product->lng;
        $result['address'] = $hotel->address;
        $result['giata_destination'] = Arr::get($hotel->address, 'city', '');
        $result['rating'] = $hotel->star_rating;
        $result['user_rating'] = $hotel->star_rating;
        $result['hotel_fees'] = $this->getHotelFees($hotel);
        $result['amenities'] = $this->getHotelAmenities($hotel);
//        $result['rooms'] = $this->getHotelRooms($hotel);
        $result['weight'] = $hotel->weight;
    }

    private function updateContentResultWithInternalData(array &$result, $hotel): void
    {
        $result['hotel_name'] = $hotel->product->name;
        $result['latitude'] = $hotel->product->lat;
        $result['longitude'] = $hotel->product->lng;
        $result['address'] = $hotel->address;
        $result['giata_destination'] = Arr::get($hotel->address, 'city', '');
        $result['rating'] = $hotel->star_rating;
        $result['user_rating'] = $hotel->star_rating;
        $result['amenities'] = $this->getHotelAmenities($hotel);
        $result['weight'] = $hotel->weight ?? 0;

    }

    private function getHotelFees($hotel): array
    {
        $res = [];
        foreach ($hotel->product->feeTaxes as $feeTax) {
            $data = [
                'name' => $feeTax->name,
                'type' => $feeTax->type,
                'net_value' => $feeTax->net_value,
                'rack_value' => $feeTax->rack_value,
                'apply_type' => $feeTax->apply_type,
                'commissionable' => $feeTax->commissionable,
            ];
            if ($feeTax->fee_category == 'mandatory') {
                $res['mandatory'][] = $data;
            } else {
                $res['optional'][] = $data;
            }
        }
        return $res;
    }

    private function getHotelDescriptions($hotel): array
    {
        return $hotel->product->descriptiveContentsSection->mapWithKeys(function ($section) {
            return [
                $section->descriptiveType->name => [
                    'value' => $section->value,
                    'start_date' => $section->start_date,
                    'end_date' => $section->end_date,
                ]
            ];
        })->all();
    }

    private function getHotelAmenities($hotel): array
    {
        return $hotel->product->attributes->mapWithKeys(function ($attribute) {
            return [$attribute->attribute->id => $attribute->attribute->name];
        })->all();
    }

    private function getHotelRooms($hotel): array
    {
        $rooms = [];
        foreach ($hotel->rooms as $room) {
            $amenities = $room->attributes->mapWithKeys(function ($attribute) {
                return [$attribute->id => $attribute->name];
            })->all();

            $newImages = $room->galleries
                ->flatMap(function ($gallery) {
                    return $gallery->images->pluck('image_url');
                })->take(20)->all();

            $supplierCodes = collect(json_decode($room->supplier_codes, true))
                ->mapWithKeys(function ($code) {
                    return [$code['supplier'] => $code['code']];
                })->all();
            $supplierCodes['external_code'] = $room->hbsi_data_mapped_name;

            $rooms[] = [
                'content_supplier' => 'Internal Repository',
                'supplier_room_id' => $room->hbsi_data_mapped_name,
                'supplier_room_name' => $room->name,
                'supplier_room_code' => $room->hbsi_data_mapped_name,
                'amenities' => $amenities,
                'images' => $newImages,
                'descriptions' => $room->description,
                'supplier_codes' => $supplierCodes,
            ];
        }
        return $rooms;
    }
}
