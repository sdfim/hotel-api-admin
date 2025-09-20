<?php

namespace App\Repositories;

use App\Models\IcePortalPropertyAsset;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class IcePortalAccetRepository
{
    public function __construct() {}

    public static function dataByCity(string $city): array
    {
        $results = IcePortalPropertyAsset::with('mapperHbsiGiata')
            ->where('city', $city)
            ->get()
            ->map(function ($property) {
                $result = $property->toArray();
                $mappings = $property->mapperHbsiGiata;
                if ($mappings && count($mappings) > 0) {
                    $result['giata_id'] = $mappings[0]->giata_id ?? null;
                    $result['perc'] = $mappings[0]->match_percentage ?? null;
                }

                return $result;
            })
            ->toArray();

        return ['results' => $results, 'count' => count($results)];
    }

    public static function dataByGiataIds(array $giataIds): array
    {
        $results = IcePortalPropertyAsset::with('mapperHbsiGiata')
            ->whereHas('mapperHbsiGiata', function ($query) use ($giataIds) {
                $query->whereIn('giata_id', $giataIds);
            })
            ->get()
            ->map(function ($property) {
                $result = $property->toArray();
                $mappings = $property->mapperHbsiGiata;
                if ($mappings && count($mappings) > 0) {
                    $result['giata_id'] = $mappings[0]->giata_id ?? null;
                    $result['perc'] = $mappings[0]->match_percentage ?? null;
                }

                return $result;
            })
            ->toArray();

        return ['results' => $results, 'count' => count($results)];
    }

    public static function getIdsByDestinationGiata(string $input): array
    {
        $query = is_numeric($input)
            ? IcePortalPropertyAsset::where('city_id', $input)
            : IcePortalPropertyAsset::where('city', $input);

        return $query->whereHas('mapperHbsiGiata', function ($q) {
            $q->where('supplier', MappingSuppliersEnum::IcePortal->value)
                ->whereNotNull('supplier_id');
        })
            ->with(['mapperHbsiGiata' => function ($q) {
                $q->where('supplier', MappingSuppliersEnum::IcePortal->value)
                    ->whereNotNull('supplier_id');
            }])
            ->get()
            ->pluck('mapperHbsiGiata')
            ->flatten()
            ->pluck('supplier_id')
            ->unique()
            ->toArray();
    }

    public static function getIdByCoordinate(array $minMaxCoordinate): int
    {
        $property = IcePortalPropertyAsset::where('latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where('latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where('longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where('longitude', '<', $minMaxCoordinate['max_longitude'])
            ->whereHas('mapperHbsiGiata', function ($q) {
                $q->where('supplier', MappingSuppliersEnum::IcePortal->value)
                    ->whereNotNull('supplier_id');
            })
            ->with(['mapperHbsiGiata' => function ($q) {
                $q->where('supplier', MappingSuppliersEnum::IcePortal->value)
                    ->whereNotNull('supplier_id');
            }])
            ->first();

        return $property && $property->mapperHbsiGiata && count($property->mapperHbsiGiata) > 0
            ? $property->mapperHbsiGiata[0]->supplier_id
            : 0;
    }
}
