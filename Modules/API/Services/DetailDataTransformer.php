<?php

namespace Modules\API\Services;

use Illuminate\Database\Eloquent\Collection;
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
            if (! $item->product?->contentSource) {
                continue;
            }
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

        if ($structureSource['property_images'] == SupplierNameEnum::EXPEDIA->value) {
            $result['images'] = array_merge($internalPropertyImages, $result['images']);
        } elseif ($structureSource['property_images'] == SupplierNameEnum::ICE_PORTAL->value) {
            $result['images'] = array_merge($internalPropertyImages, Arr::get($transformedResultsIcePortal, $hotel->giata_code.'.images', []));
        } else {
            $result['images'] = $internalPropertyImages;
        }

        if ($structureSource['content_source'] == SupplierNameEnum::EXPEDIA->value) {
            $result['description'] = array_merge($internalPropertyDescription, $result['description']);
        } elseif ($structureSource['content_source'] == SupplierNameEnum::ICE_PORTAL->value) {
            $result['description'] = array_merge($internalPropertyDescription, Arr::get($transformedResultsIcePortal, $hotel->giata_code.'.description', []));
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
            SupplierNameEnum::EXPEDIA->value => [],
            SupplierNameEnum::ICE_PORTAL->value => [],
        ];
        foreach ($internalRooms as $room) {
            $existingRoomCodes[SupplierNameEnum::EXPEDIA->value][] = $room['supplier_codes'][SupplierNameEnum::EXPEDIA->value] ?? null;
            $existingRoomCodes[SupplierNameEnum::ICE_PORTAL->value][] = $room['supplier_codes'][SupplierNameEnum::ICE_PORTAL->value] ?? null;
        }

        foreach ($internalRooms as &$room) {
            $room['images'] = array_map(function ($imageUrl) {
                return url('storage/'.$imageUrl);
            }, $room['images']);
        }

        $result['rooms'] = array_merge($internalRooms, $result['rooms']);

        foreach ($result['rooms'] as $key => $room) {
            $contentSupplier = Arr::get($room, 'content_supplier', '');
            $supplierRoomId = Arr::get($room, 'supplier_room_id', '');
            if (! isset($existingRoomCodes[$contentSupplier])) {
                continue;
            }
            if (in_array($supplierRoomId, $existingRoomCodes[$contentSupplier])) {
                unset($result['rooms'][$key]);
            }
        }

        $transformedResultsIcePortal = [];
        foreach ($resultsIcePortal as $item) {
            $giataHotelCode = $item['giata_hotel_code'];
            $transformedResultsIcePortal[$giataHotelCode] = $item;
        }

        if ($structureSource['property_images'] == SupplierNameEnum::EXPEDIA->value) {
            $result['images'] = array_merge($internalPropertyImages, $result['images']);
        } elseif ($structureSource['property_images'] == SupplierNameEnum::ICE_PORTAL->value) {
            $result['images'] = array_merge($internalPropertyImages, Arr::get($transformedResultsIcePortal, $hotel->giata_code.'.images', []));
        }

        $giataId = $hotel->giata_code;
        if ($structureSource['room_images'] == SupplierNameEnum::EXPEDIA->value) {
            foreach ($result['rooms'] as &$room) {
                $externalCode = Arr::get($room, 'supplier_codes.external_code', '');
                $room['images'] = array_merge($room['images'], $romsImagesData[$giataId][$externalCode][SupplierNameEnum::EXPEDIA->value] ?? []);
            }
        } elseif ($structureSource['room_images'] == SupplierNameEnum::ICE_PORTAL->value) {
            foreach ($result['rooms'] as &$room) {
                $externalCode = Arr::get($room, 'supplier_codes.external_code', '');
                $room['images'] = array_merge($room['images'], $romsImagesData[$giataId][$externalCode][SupplierNameEnum::ICE_PORTAL->value] ?? []);
            }
        }

        if ($structureSource['content_source'] == SupplierNameEnum::EXPEDIA->value) {
            $result['descriptions'] = array_merge($internalPropertyDescription, $result['descriptions']);
        } elseif ($structureSource['content_source'] == SupplierNameEnum::ICE_PORTAL->value) {
            $result['descriptions'] = array_merge($internalPropertyDescription, Arr::get($transformedResultsIcePortal, $hotel->giata_code.'.descriptions', []));
        } else {
            $result['descriptions'] = $internalPropertyDescription;
        }

        $result['structure'] = $structureSource;
    }

    private function getPropertyImages($hotel): array
    {
        return $hotel->product->galleries
            ->flatMap(function ($gallery) {
                return $gallery->images->pluck('image_url')->map(function ($imageUrl) {
                    return url('storage/'.$imageUrl);
                });
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
        $result['cancellation_policies'] = $this->getHotelCancellationPolicies($hotel);
        $result['deposit_information'] = $this->getProductDepositInformation($hotel);
        $result['amenities'] = $this->getProductAmenities($hotel);
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
        $result['cancellation_policies'] = $this->getHotelCancellationPolicies($hotel);
        $result['deposit_information'] = $this->getProductDepositInformation($hotel);
        $result['amenities'] = $this->getProductAmenities($hotel);
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

    private function getHotelCancellationPolicies($hotel): array
    {
        if (! $hotel->product->cancellationPolicies) {
            return [];
        }

        return $hotel->product->cancellationPolicies->map(function ($policy) {
            return [
                'name' => $policy->name,
                'start_date' => $policy->start_date,
                'expiration_date' => $policy->end_date,
                'manipulable_price_type' => $policy->manipulable_price_type,
                'price_value' => $policy->price_value,
                'price_value_type' => $policy->price_value_type,
                'price_value_target' => $policy->price_value_target,
                'conditions' => $this->formatConditions($policy->conditions),
            ];
        })->all();
    }

    private function getProductDepositInformation($hotel): array
    {
        if (! $hotel->product->depositInformations) {
            return [];
        }

        return $hotel->product->depositInformations->map(function ($depositInfo) {
            return [
                'name' => $depositInfo->name,
                'start_date' => $depositInfo->start_date,
                'expiration_date' => $depositInfo->expiration_date,
                'manipulable_price_type' => $depositInfo->manipulable_price_type,
                'price_value' => $depositInfo->price_value,
                'price_value_type' => $depositInfo->price_value_type,
                'price_value_target' => $depositInfo->price_value_target,
                'conditions' => $this->formatConditions($depositInfo->conditions),
            ];
        })->all();
    }

    private function getProductAmenities($hotel): array
    {
        if (! $hotel->product->affiliations) {
            return [];
        }

        return $hotel->product->affiliations->map(function ($depositInfo) {
            return [
                'consortia' => $depositInfo->consortia->name,
                'description' => $depositInfo->description,
                'start_date' => $depositInfo->start_date,
                'end_date' => $depositInfo->end_date,
                'amenities' => $depositInfo->amenities,
            ];
        })->all();
    }

    private function formatConditions(Collection $conditions): string
    {
        return collect($conditions)->map(function ($condition) {
            $value = $condition['value'] ?? '';
            $valueFrom = $condition['value_from'] ?? '';
            $valueTo = $condition['value_to'] ?? '';

            //            return "{$condition['field']} {$condition['compare']} {$value} {$valueFrom} {$valueTo}";
            return preg_replace(
                ['/ {2,}/', '/\s+([,.!?])/', '/\s+$/'],
                [' ', '$1', ''],
                "{$condition['field']} {$condition['compare']} {$value} {$valueFrom} {$valueTo}"
            );
        })->implode(', ');
    }

    private function getHotelDescriptions($hotel): array
    {
        return $hotel->product->descriptiveContentsSection->mapWithKeys(function ($section) {
            return [
                $section->descriptiveType->name => [
                    'value' => $section->value,
                    'start_date' => $section->start_date,
                    'end_date' => $section->end_date,
                ],
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
            $attributes = $room->attributes->mapWithKeys(function ($attribute) {
                return [$attribute->id => $attribute->name];
            })->all();

            if ($room->affiliations) {
                $amenities = $room->affiliations->map(function ($amenity) {
                    return [
                        'consortia' => $amenity->consortia->name,
                        'description' => $amenity->description,
                        'start_date' => $amenity->start_date,
                        'end_date' => $amenity->end_date,
                        'amenities' => $amenity->amenities,
                    ];
                })->all();
            }

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
                'area' => $room->area.' sqft',
                'bed_groups' => $room->bed_groups,
                'room_views' => $room->room_views,
                'supplier_room_code' => $room->hbsi_data_mapped_name,
                'attributes' => $attributes,
                'amenities' => $amenities,
                'images' => $newImages,
                'descriptions' => $room->description,
                'supplier_codes' => $supplierCodes,
            ];
        }

        return $rooms;
    }
}
