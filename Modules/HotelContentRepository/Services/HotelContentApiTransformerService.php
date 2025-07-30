<?php

namespace Modules\HotelContentRepository\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentDetailResponseFactory;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelContentApiTransformerService
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

    public function updateContentResultWithHotelData(array &$result, $hotel, array $structureSource, array $transformedResults): void
    {
        // Create as Internal data by default
        $this->updateContentResultWithInternalData($result, $hotel);
        $internalPropertyImages = $this->getPropertyImages($hotel);
        $internalPropertyDescription = $this->getHotelDescriptions($hotel);

        // Property Images
        $additionalImages = $internalPropertyImages;
        if (isset($structureSource['property_images'])) {
            $supplierImages = Arr::get($transformedResults, "{$structureSource['property_images']}.{$hotel->giata_code}.images", []);
            $additionalImages = array_merge($internalPropertyImages, $supplierImages);
        }
        $result['images'] = $additionalImages;

        // Content Descriptions
        $additionalDescriptions = $internalPropertyDescription;
        if (isset($structureSource['content_source'])) {
            $supplierDescriptions = Arr::get($transformedResults, "{$structureSource['content_source']}.{$hotel->giata_code}.description", []);
            $additionalDescriptions = array_merge($internalPropertyDescription, $supplierDescriptions);
        }
        $result['description'] = $additionalDescriptions;

        $result['structure'] = $structureSource;
    }

    public function updateResultWithHotelData(array &$result, $hotel, array $structureSource, array $resultsSuppliers, array $romsImagesData): void
    {
        // Create as Internal data by default
        $this->updateContentResultWithInternalData($result, $hotel);
        $internalPropertyImages = $this->getPropertyImages($hotel);
        $internalPropertyDescription = $this->getHotelDescriptions($hotel);

        $internalRooms = $this->getHotelRooms($hotel);
        $existingRoomCodes = [];

        foreach ($internalRooms as $room) {
            foreach (SupplierNameEnum::getContentSupplierValues() as $supplier) {
                $existingRoomCodes[$supplier][] = $room['supplier_codes'][$supplier] ?? null;
            }
        }

        $result['rooms'] = array_merge($internalRooms, $result['rooms']);

        foreach ($result['rooms'] as $key => $resultRoom) {
            $contentSupplier = Arr::get($resultRoom, 'content_supplier', '');
            $unifiedRoomCode = Arr::get($resultRoom, 'unified_room_code', '');
            if (! isset($existingRoomCodes[$contentSupplier])) {
                continue;
            }
            if (in_array($unifiedRoomCode, $existingRoomCodes[$contentSupplier])) {
                unset($result['rooms'][$key]);
            }
        }

        $transformedResults = [];
        foreach ($resultsSuppliers as $supplier => $items) {
            foreach ($items as $item) {
                $giataHotelCode = $item['giata_hotel_code'];
                $transformedResults[$supplier][$giataHotelCode] = $item;
            }
        }

        // Property Images
        $additionalImages = $internalPropertyImages;
        if (isset($structureSource['property_images'])) {
            $supplierNane = $structureSource['property_images'];
            $supplierrImages = Arr::get($transformedResults, $supplierNane, []);
            $additionalImages = array_merge($additionalImages, Arr::get($supplierrImages, $hotel->giata_code.'.images', []));
        }
        $result['images'] = $additionalImages;

        // Room Images
        $giataId = $hotel->giata_code;
        $result['rooms'] = array_map(function ($room) use ($structureSource, $romsImagesData, $giataId) {
            $externalCode = Arr::get($room, 'supplier_codes.external_code', '');
            $additionalImages = [];
            if (isset($structureSource['room_images'])) {
                $additionalImages = $romsImagesData[$giataId][$externalCode][$structureSource['room_images']] ?? [];
            }
            $room['images'] = array_merge($room['images'], $additionalImages);

            return $room;
        }, $result['rooms']);

        $result['rooms'] = array_values($result['rooms']);

        // Content Descriptions
        $additionalDescriptions = $internalPropertyDescription;
        if (isset($structureSource['content_source'])) {
            $supplierNane = $structureSource['content_source'];
            $supplierrDescriptions = Arr::get($transformedResults, $supplierNane, []);
            $additionalDescriptions = array_merge($additionalDescriptions, Arr::get($supplierrDescriptions, $hotel->giata_code.'.descriptions', []));
        }
        $result['descriptions'] = $additionalDescriptions;

        $result['rooms'] = array_values($result['rooms']);

        $result['rooms'] = array_values($result['rooms']);

        $result['structure'] = $structureSource;

        foreach ($result['rooms'] as &$room) {
            unset($room['supplier_codes']['external_code']);
        }
    }

    public function getPropertyImages($hotel): array
    {
        return $hotel->product->galleries
            ->flatMap(function ($gallery) {
                return $gallery->images
                    ->sortByDesc('weight')
                    ->pluck('full_url', 'weight');
            })->take(200)->all();
    }

    public function updateContentResultWithInternalData(array &$result, $hotel): void
    {
        $result['hotel_name'] = $hotel->product->name;
        $result['latitude'] = $hotel->product->lat;
        $result['longitude'] = $hotel->product->lng;
        $result['address'] = implode(', ', $hotel->address);
        $result['giata_destination'] = Arr::get($hotel->address, 'city', '');
        $result['rating'] = $hotel->star_rating;
        $result['currency'] = $hotel->product->default_currency;
        $result['number_rooms'] = $hotel->num_rooms;
        $result['user_rating'] = $hotel->star_rating;
        $result['attributes'] = $this->getHotelAttributes($hotel);
        $result['weight'] = $hotel->weight ?? 0;
        $result['cancellation_policies'] = $this->getHotelCancellationPolicies($hotel);
        $result['deposit_information'] = $this->getProductDepositInformation($hotel);
        $result['drivers'] = $this->getHotelDrivers($hotel);

        if (! empty($hotel->product->hero_image)) {
            $pathParts = explode('/', $hotel->product->hero_image);
            $filename = array_pop($pathParts);
            $directory = implode('/', $pathParts);
            $result['hero_image'] = url('storage/'.($directory ? $directory.'/' : '').rawurlencode($filename));
        }
        if (! empty($hotel->product->hero_image_thumbnails)) {
            $pathParts = explode('/', $hotel->product->hero_image_thumbnails);
            $filename = array_pop($pathParts);
            $directory = implode('/', $pathParts);
            $result['hero_image_thumbnail'] = url('storage/'.($directory ? $directory.'/' : '').rawurlencode($filename));
        }

        if (! empty($hotel->hotel_board_basis)) {
            $result['meal_plans'] = $hotel->hotel_board_basis;
        }
    }

    private function getHotelFees($hotel): array
    {
        return $hotel->product->descriptiveContentsSection
            ->map(function ($section) {
                if ($section->descriptiveType?->type === 'Taxes And Fees') {
                    return [
                        //                        'name' => 'hotel_fees_'.$section->descriptiveType?->name,
                        'name' => 'hotel_fees',
                        'value' => $section->value,
                        'start_date' => $section->start_date,
                        'end_date' => $section->end_date,
                    ];
                }
            })
            ->all();
    }

    private function getHotelFeesPricingApi($hotel): array
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

        return $hotel->product->cancellationPolicies
            ->filter(function ($policy) {
                return $policy->rate_id === null;
            })
            ->map(function ($policy) {
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

        return array_values($hotel->product->depositInformations
            ->filter(function ($depositInfo) {
                return $depositInfo->rate_id === null;
            })
            ->map(function ($depositInfo) {
                $initialPaymentDueType = $depositInfo->initial_payment_due_type;
                $deposit = [
                    'name' => $depositInfo->name,
                    'start_date' => $depositInfo->start_date,
                    'expiration_date' => $depositInfo->expiration_date,
                    'manipulable_price_type' => $depositInfo->manipulable_price_type,
                    'price_value' => $depositInfo->price_value,
                    'price_value_type' => $depositInfo->price_value_type,
                    'price_value_target' => $depositInfo->price_value_target,
                    'conditions' => $this->formatConditions($depositInfo->conditions),
                ];
                if ($initialPaymentDueType) {
                    $deposit['initial_payment_due']['type'] = $initialPaymentDueType;
                    $initialPaymentDueType === 'day'
                        ? $deposit['initial_payment_due']['days'] = $depositInfo->days_initial_payment_due
                        : $deposit['initial_payment_due']['date'] = $depositInfo->date_initial_payment_due;
                }

                return $deposit;
            })->all());
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

    public function getHotelDescriptions($hotel): array
    {
        $descriptions = $hotel->product->descriptiveContentsSection
            ->filter(function ($section) {
                return $section->descriptiveType?->type !== 'Taxes And Fees' && $section->rate_id === null;
            })
            ->map(function ($section) {
                return [
                    'name' => $section->descriptiveType?->name,
                    'value' => $section->value,
                    'start_date' => $section->start_date,
                    'end_date' => $section->end_date,
                ];
            })
            ->all();

        $descriptions = array_merge($descriptions, $this->getHotelFees($hotel));

        $descriptions = array_filter($descriptions, function ($description) {
            return $description !== null;
        });

        return array_values($descriptions);
    }

    private function getRoomDescriptions(HotelRoom $room): array
    {
        $descriptiveContents[] = [
            'name' => 'room_description',
            'value' => $room->description,
            'start_date' => null,
            'end_date' => null,
        ];

        return $descriptiveContents;
    }

    private function getHotelAttributes($hotel): array
    {
        return $hotel->product->attributes->map(function ($attribute) {
            return [
                'name' => $attribute->attribute?->name,
                'category' => $attribute->category?->name ?? 'general',
            ];
        })->all();
    }

    private function getHotelDrivers($hotel): array
    {
        return collect($hotel->product->off_sale_by_sources)->map(function ($driver) {
            return [
                'name' => $driver,
                'value' => true,
            ];
        })->all();
    }

    public function getHotelRooms($hotel): array
    {
        $requestConsortiaAffiliation = request()->input('consortia_affiliation', null);

        $rooms = [];
        foreach ($hotel->rooms as $room) {
            $attributes = $room->attributes->map(function ($attribute) {
                return [
                    'name' => $attribute?->name,
                    'category' => 'general',
                ];
            })->all();

            $relatedRooms = $room->relatedRooms->map(function ($relatedRoom) {
                return [
                    'unified_room_code' => $relatedRoom->external_code,
                    'name' => $relatedRoom->name,
                ];
            })->all();

            $newImages = $room->galleries
                ->flatMap(function ($gallery) {
                    return $gallery->images->pluck('full_url');
                })->take(20)->all();

            $supplierCodes = collect(json_decode($room->supplier_codes, true))
                ->mapWithKeys(function ($code) {
                    return [$code['supplier'] => $code['code']];
                })->all();
            $supplierCodes['external_code'] = $room->external_code;

            $rooms[] = [
                'content_supplier' => 'Internal Repository',
                'unified_room_code' => $room->external_code,
                'supplier_room_id' => $room->external_code,
                'supplier_room_code' => $room->external_code,
                'supplier_room_name' => $room->name,
                'area' => $room->area.' sqft',
                'bed_groups' => $room->bed_groups,
                'room_views' => $room->room_views,
                'connecting_room_types' => $relatedRooms,
                'attributes' => $attributes,
                'images' => $newImages,
                'descriptions' => $this->getRoomDescriptions($room),
                'supplier_codes' => $supplierCodes,
            ];
        }

        return $rooms;
    }
}
