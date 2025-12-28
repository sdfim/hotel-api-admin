<?php

namespace Modules\HotelContentRepository\Actions\Hotel;

use Illuminate\Support\Facades\Artisan;
use Modules\API\Suppliers\Contracts\Hotel\ContentV1\HotelContentV1SupplierRegistry;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\Hotel;

class MappingLevelRoom
{
    public function __construct(
        protected readonly HotelContentV1SupplierRegistry $contentRegistry,
    ) {}

    public function execute(Hotel $hotel): void
    {
        $mappings = $hotel->giataCode->mappings;
        $giataCode = $hotel->giata_code;
        $supplierRoomData = [];
        $supplierNames = [];
        foreach ($mappings as $mapping) {
            $supplierNames[] = $mapping->supplier;
        }
        $supplierNames = array_unique($supplierNames);
        foreach ($supplierNames as $supplierName) {
            try {
                $results = $this->contentRegistry->get(SupplierNameEnum::from($supplierName))->getRoomsData($giataCode);
                foreach ($results as $room) {
                    $supplierRoomData[$supplierName][] = [
                        'code' => $room['id'] ?? $room['code'] ?? '',
                        'name' => $room['name'] ?? '',
                    ];
                }
            } catch (\Exception $e) {
                logger()->debug('Error creating service:', [$supplierName, $e->getMessage(), $e->getTraceAsString()]);
                $supplierRoomData[$supplierName] = [];
            }
        }

        $supplierDataJson = json_encode($supplierRoomData, JSON_UNESCAPED_UNICODE);
        Artisan::call('merge:suppliers:gemini-provider', [
            'supplierData' => $supplierDataJson,
            'giata_id' => $giataCode,
        ]);

        $mapperSuppiersOutput = Artisan::output();
        $decodedMapper = null;
        // Extract JSON from output
        if (preg_match('/(\{.*\}|\[.*\])/s', $mapperSuppiersOutput, $matches)) {
            $json = $matches[0];
            $decodedMapper = json_decode($json, true);
        }

        foreach ($hotel->rooms as $room) {
            $supplierCodes = $room->supplier_codes;
            // Ensure supplierCodes is an array
            if (is_string($supplierCodes)) {
                $decoded = json_decode($supplierCodes, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $supplierCodes = $decoded;
                } else {
                    continue;
                }
            }
            if (empty($supplierCodes) || ! is_array($supplierCodes) || ! $decodedMapper) {
                continue;
            }
            $firstSupplierCode = $supplierCodes[0];
            $firstCode = $firstSupplierCode['code'] ?? null;
            $firstSupplier = $firstSupplierCode['supplier'] ?? null;
            $additionalListings = [];
            $mergeIdToSet = null;
            foreach ($decodedMapper as $merge) {
                $matchedIndex = null;
                foreach ($merge['listings_to_merge'] as $idx => $listing) {
                    if (($listing['code'] ?? null) === $firstCode && ($listing['supplier'] ?? null) === $firstSupplier) {
                        $mergeIdToSet = $merge['merge_id'] ?? null;
                        $matchedIndex = $idx;
                        break;
                    }
                }
                if ($matchedIndex !== null) {
                    foreach ($merge['listings_to_merge'] as $idx => $otherListing) {
                        if ($idx !== $matchedIndex) {
                            $additionalListings[] = $otherListing;
                        }
                    }
                    break;
                }
            }
            foreach ($additionalListings as $listing) {
                $exists = false;
                foreach ($supplierCodes as $existing) {
                    if (($existing['code'] ?? null) === ($listing['code'] ?? null) && ($existing['supplier'] ?? null) === ($listing['supplier'] ?? null)) {
                        $exists = true;
                        break;
                    }
                }
                if (! $exists) {
                    $supplierCodes[] = $listing;
                }
            }
            $room->supplier_codes = $supplierCodes;
            if (empty($room->external_code) && $mergeIdToSet) {
                $room->external_code = $mergeIdToSet;
            }
            $room->save();
        }
    }
}
