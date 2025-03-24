<?php

namespace Modules\API\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class MappingCacheService
{
    private const EXPEDIA_HASH_MAP_CACHE_KEY = 'expedia_hash_map_mappings';
    private const CACHE_DURATION = 86400; // Cache for 1 day

    public function getMappingsExpediaHashMap(string $mainDB = null): array
    {
        $mainDB = $mainDB ?? config('database.connections.mysql.database');

        if (Cache::has(self::EXPEDIA_HASH_MAP_CACHE_KEY)) {
            $compressedHashMap = Cache::get(self::EXPEDIA_HASH_MAP_CACHE_KEY);
            return unserialize(gzuncompress($compressedHashMap));
        } else {
            $mappings = DB::table($mainDB . '.mappings')
                ->where('supplier', MappingSuppliersEnum::Expedia->value)
                ->whereNotNull('supplier_id')
                ->select('supplier_id as expedia_code', 'giata_id as giata_code')
                ->get()
                ->toArray();

            $hashMap = [];
            foreach ($mappings as $mapping) {
                $hashMap[$mapping->expedia_code] = $mapping->giata_code;
            }

            $compressedHashMap = gzcompress(serialize($hashMap));
            Cache::put(self::EXPEDIA_HASH_MAP_CACHE_KEY, $compressedHashMap, self::CACHE_DURATION);
            return $hashMap;
        }
    }}
