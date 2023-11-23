<?php

namespace App\Livewire\Charts;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchInspectorNightsDoughnutChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Search Inspector Nights Total Doughnut Chart';

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
        $keySearchInspectorNightsDoughnutChart = 'SearchInspectorNightsDoughnutChart';

        if (Cache::has($keySearchInspectorNightsDoughnutChart . ':labels') && Cache::has($keySearchInspectorNightsDoughnutChart . ':data')) {
            $labels = Cache::get($keySearchInspectorNightsDoughnutChart . ':labels');
            $data = Cache::get($keySearchInspectorNightsDoughnutChart . ':data');
        } else {
            $queryResult = DB::select("
                SELECT
                    COALESCE(CONCAT(gg.city_name, ' (', gg.locale_name, ' - ', gg.country_name, ')'), JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination,
                    SUM(DATEDIFF(JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkout')), JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkin'))) - 1) AS nights
                FROM
                    api_search_inspector
                LEFT JOIN
                    ujv_api.giata_geographies AS gg ON gg.city_id = JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))
                GROUP BY
                    destination
                ORDER BY
                    nights DESC
                LIMIT 5");

            $queryResult = json_decode(json_encode($queryResult), true);

            $labels = array_column($queryResult, 'destination');
            $data = array_column($queryResult, 'nights');

            Cache::put($keySearchInspectorNightsDoughnutChart . ':labels', $labels, now()->addMinutes(60));
            Cache::put($keySearchInspectorNightsDoughnutChart . ':data', $data, now()->addMinutes(60));
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
