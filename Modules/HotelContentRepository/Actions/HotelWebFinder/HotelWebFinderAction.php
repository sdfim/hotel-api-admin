<?php

namespace Modules\HotelContentRepository\Actions\HotelWebFinder;

use Carbon\Carbon;
use Modules\HotelContentRepository\Models\HotelWebFinder;

class HotelWebFinderAction
{
    public function create(array $data, int $hotelId): HotelWebFinder
    {
        return $this->saveOrUpdate($data, null, $hotelId);
    }

    public function update(array $data, int $recordId, int $hotelId): HotelWebFinder
    {
        return $this->saveOrUpdate($data, $recordId, $hotelId);
    }

    public function saveOrUpdate(array $data, ?int $recordId, int $hotelId): HotelWebFinder
    {
        $startDateFormat = 'Y-m-d';
        $endDateFormat = 'Y-m-d';

        foreach ($data['units'] as $unit) {
            if ($unit['field'] === 'search_start_travel_date_name' && ! empty($unit['type'])) {
                $startDateFormat = $unit['type'];
            }
            if ($unit['field'] === 'search_end_travel_date_name' && ! empty($unit['type'])) {
                $endDateFormat = $unit['type'];
            }
        }

        $startDate = Carbon::now()->addMonth()->format($startDateFormat);
        $endDate = Carbon::parse($startDate)->addDays(7)->format($endDateFormat);
        $adults = '2';
        $children = '0';
        $numberOfRooms = '1';

        $data['example'] = str_replace(
            [
                '{search_adults_name}',
                '{search_children_name}',
                '{search_rooms_count_name}',
                "{search_end_travel_date_name:$endDateFormat}",
                "{search_start_travel_date_name:$startDateFormat}",
            ],
            [$adults, $children, $numberOfRooms, $endDate, $startDate],
            $data['finder']
        );

        $webFinder = HotelWebFinder::updateOrCreate(
            ['id' => $recordId],
            [
                'base_url' => $data['base_url'],
                'finder' => $data['finder'],
                'website' => $data['website'],
                'example' => $data['example'],
            ]
        );

        $webFinder->hotels()->sync([$hotelId]);

        $webFinder->units()->delete();

        foreach ($data['units'] as $unitData) {
            $webFinder->units()->create($unitData);
        }

        return $webFinder;
    }
}
