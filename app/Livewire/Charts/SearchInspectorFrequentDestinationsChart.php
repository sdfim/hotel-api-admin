<?php

namespace App\Livewire\Charts;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchInspectorFrequentDestinationsChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Frequent Destinations';

    /**
     * @var string|null
     */
    protected static ?string $pollingInterval = '86400s';

    /**
     * @var string|null
     */
    protected static ?string $maxHeight = '500px';

    /**
     * @return array
     */
    protected function getData(): array
    {
        $keyGiataCityChart = 'SearchInspectorFrequentDestinationsChart';

        if (Cache::has($keyGiataCityChart . ':labels') && Cache::has($keyGiataCityChart . ':data')) {
            $labels = Cache::get($keyGiataCityChart . ':labels');
            $data = Cache::get($keyGiataCityChart . ':data');
        } else {
            $queryResult = DB::select("
			SELECT
				COALESCE(CONCAT(gg.city_name, ' (', gg.locale_name, ' - ', gg.country_name, ')'), JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination,
				COUNT(*) as count
			FROM
				api_search_inspector
			LEFT JOIN
                ujv_api.giata_geographies AS gg ON gg.city_id = JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))
			GROUP BY
				destination
			ORDER BY
				count DESC
			LIMIT 10");

            $queryResult = json_decode(json_encode($queryResult), true);

            $labels = array_column($queryResult, 'destination');
            $data = array_column($queryResult, 'count');

            Cache::put($keyGiataCityChart . ':labels', $labels, now()->addMinutes(1440));
            Cache::put($keyGiataCityChart . ':data', $data, now()->addMinutes(1440));
        }

        $colors = [
            'rgb(0, 0, 255, 0.8)',
            'rgb(0, 128, 0, 0.8)',
            'rgb(255, 0, 0, 0.8)',
            'rgb(255, 165, 0, 0.8)',
            'rgb(128, 0, 128, 0.8)',
            'rgb(0, 128, 128, 0.8)',
            'rgb(255, 255, 0, 0.8)',
            'rgb(255, 105, 180, 0.8)',
            'rgb(139, 69, 19, 0.8)',
            'rgb(0, 255, 255, 0.8)',
            'rgb(0, 255, 0, 0.8)',
            'rgb(255, 0, 255, 0.8)'
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
