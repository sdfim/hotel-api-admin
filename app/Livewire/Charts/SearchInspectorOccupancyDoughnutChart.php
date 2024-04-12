<?php

namespace App\Livewire\Charts;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchInspectorOccupancyDoughnutChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Search Inspector Occupancy Total Doughnut Chart';

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
        $keySearchInspectorOccupancyDoughnutChart = 'SearchInspectorOccupancyDoughnutChart';

        if (Cache::has($keySearchInspectorOccupancyDoughnutChart . ':labels') && Cache::has($keySearchInspectorOccupancyDoughnutChart . ':data')) {
            $labels = Cache::get($keySearchInspectorOccupancyDoughnutChart . ':labels');
            $data = Cache::get($keySearchInspectorOccupancyDoughnutChart . ':data');
        } else {
            $queryResult = DB::select("
                SELECT
                    COALESCE(CONCAT(gg.city_name, ' (', gg.locale_name, ' - ', gg.country_name, ')'), JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination,
                    SUM(oc.adults + oc.children) AS occupancy
                FROM
                    api_search_inspector
                CROSS JOIN
                    JSON_TABLE(request, '$.occupancy[*]' COLUMNS (adults INT PATH '$.adults' DEFAULT '0' ON EMPTY, children INT PATH '$.children' DEFAULT '0' ON EMPTY)) oc
                LEFT JOIN
                    " . config(database.mysql2.database) . "giata_geographies AS gg ON gg.city_id = JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))
                GROUP BY
                    destination
                ORDER BY
                    occupancy DESC
                LIMIT 5");

            $queryResult = json_decode(json_encode($queryResult), true);

            $labels = array_column($queryResult, 'destination');
            $data = array_column($queryResult, 'occupancy');

            Cache::put($keySearchInspectorOccupancyDoughnutChart . ':labels', $labels, now()->addMinutes(60));
            Cache::put($keySearchInspectorOccupancyDoughnutChart . ':data', $data, now()->addMinutes(60));
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
