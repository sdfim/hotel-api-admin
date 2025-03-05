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
        $startDate = Carbon::now()->addMonth()->format('Y-m-d');
        $endDate = Carbon::parse($startDate)->addDays(7)->format('Y-m-d');
        $numberOfRooms = '1';

        $data['example'] = str_replace(
            ['{start_date}', '{end_date}', '{number_of_rooms}'],
            [$startDate, $endDate, $numberOfRooms],
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
