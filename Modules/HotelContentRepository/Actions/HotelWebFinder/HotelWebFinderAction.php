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
            if ($unit['field'] === 'search_start_travel_date_name' && ! empty($unit['type_select'])) {
                $startDateFormat = $unit['type_select'];
            }
            if ($unit['field'] === 'search_end_travel_date_name' && ! empty($unit['type_select'])) {
                $endDateFormat = $unit['type_select'];
            }
        }

        $startDateObj = Carbon::now()->addMonth();
        $endDateObj = $startDateObj->copy()->addDays(7);
        $startDate = $startDateObj->format($startDateFormat);
        $endDate = $endDateObj->format($endDateFormat);
        $adults = '2';
        $children = '0';
        $numberOfRooms = '1';
        $nights = $startDateObj->diffInDays($endDateObj);

        $data['example'] = str_replace(
            [
                '{search_adults_name}',
                '{search_children_name}',
                '{search_rooms_count_name}',
                '{search_nights_name}',
                "{search_end_travel_date_name:$endDateFormat}",
                "{search_start_travel_date_name:$startDateFormat}",
            ],
            [$adults, $children, $numberOfRooms, $nights, $endDate, $startDate],
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
            $unitData['type'] = $unitData['type_select'] ?? $unitData['type_text'] ?? null;
            unset($unitData['type_select'], $unitData['type_text']);
            $webFinder->units()->create($unitData);
        }

        return $webFinder;
    }
}
