<?php

namespace App\Livewire\Charts;

use App\Models\ApiSearchInspector;
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
    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $keySearchInspectorOccupancyDoughnutChart = 'SearchInspectorOccupancyDoughnutChart';

        if (Cache::has($keySearchInspectorOccupancyDoughnutChart . ':labels') && Cache::has($keySearchInspectorOccupancyDoughnutChart . ':data')) {
            $labels = Cache::get($keySearchInspectorOccupancyDoughnutChart . ':labels');
            $data = Cache::get($keySearchInspectorOccupancyDoughnutChart . ':data');
        } else {
            $giataGeographies = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_geographies';
            $model = ApiSearchInspector::select(
                DB::raw("COALESCE((SELECT city_name FROM $giataGeographies WHERE city_id = JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))),
                    JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination"),
                DB::raw("SUM(oc.adults + oc.children) AS occupancy"),
            )
                ->crossJoin(DB::raw("JSON_TABLE(request, '$.occupancy[*]' COLUMNS (adults INT PATH '$.adults' DEFAULT '0' ON EMPTY, children INT PATH '$.children' DEFAULT '0' ON EMPTY)) oc"))
                ->groupBy('destination')
                ->orderBy('occupancy', 'DESC')
                ->limit(5)
                ->get();

            $labels = $model->pluck('destination');
            $data = $model->pluck('occupancy');

            Cache::put($keySearchInspectorOccupancyDoughnutChart . ':labels', $labels, now()->addMinutes(60));
            Cache::put($keySearchInspectorOccupancyDoughnutChart . ':data', $data, now()->addMinutes(60));
        }

        $colors = [
            'rgb(0, 0, 255, 0.8)',
            'rgb(0, 128, 0, 0.8)',
            'rgb(255, 0, 0, 0.8)',
            'rgb(255, 165, 0, 0.8)',
            'rgb(128, 0, 128, 0.8)'
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

    protected function getType(): string
    {
        return 'doughnut';
    }
}
