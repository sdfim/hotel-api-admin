<?php

namespace App\Livewire\Charts;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchInspectorRoomsDoughnutChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Search Inspector Rooms Total Doughnut Chart';

    /**
     * @var string|null
     */
    protected static ?string $pollingInterval = '3600s';

    /**
     * @var string|null
     */
    protected static ?string $maxHeight = '500px';

    /**
     * @return array
     */
    protected function getData(): array
    {
        $keySearchInspectorRoomsDoughnutChart = 'SearchInspectorRoomsDoughnutChart';

        if (Cache::has($keySearchInspectorRoomsDoughnutChart . ':labels') && Cache::has($keySearchInspectorRoomsDoughnutChart . ':data')) {
            $labels = Cache::get($keySearchInspectorRoomsDoughnutChart . ':labels');
            $data = Cache::get($keySearchInspectorRoomsDoughnutChart . ':data');
        } else {
            $queryResult = DB::select("
                SELECT
                    COALESCE(CONCAT(gg.city_name, ' (', gg.locale_name, ' - ', gg.country_name, ')'), JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination,
                    SUM(JSON_LENGTH(JSON_UNQUOTE(JSON_EXTRACT(request, '$.occupancy')))) AS rooms
                FROM
                    api_search_inspector
                LEFT JOIN
                    giata_geographies AS gg ON gg.city_id = JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))
                GROUP BY
                    destination
                ORDER BY
                    rooms DESC
                LIMIT 5");

            $queryResult = json_decode(json_encode($queryResult), true);

            $labels = array_column($queryResult, 'destination');
            $data = array_column($queryResult, 'rooms');

            Cache::put($keySearchInspectorRoomsDoughnutChart . ':labels', $labels, now()->addMinutes(60));
            Cache::put($keySearchInspectorRoomsDoughnutChart . ':data', $data, now()->addMinutes(60));
        }

        $colors = [
            'rgb(70, 130, 180, 0.85)',
            'rgb(0, 128, 0, 0.85)',
            'rgb(128, 0, 128, 0.85)',
            'rgb(139, 69, 19, 0.85)',
            'rgb(0, 0, 128, 0.85)',
            'rgb(128, 0, 0, 0.85)',
            'rgb(255, 192, 203, 0.85)',
            'rgb(255, 215, 0, 0.85)',
            'rgb(124, 252, 0, 0.85)',
            'rgb(255, 69, 0, 0.85)',
            'rgb(255, 165, 0, 0.85)',
            'rgb(30, 144, 255, 0.85)'
        ];

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ]
        ];
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}
