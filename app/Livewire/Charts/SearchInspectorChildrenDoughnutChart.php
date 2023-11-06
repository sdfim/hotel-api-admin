<?php

namespace App\Livewire\Charts;

use App\Models\ApiSearchInspector;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchInspectorChildrenDoughnutChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Search Inspector Children Total Doughnut Chart';

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
        $keySearchInspectorChildrenDoughnutChart = 'SearchInspectorChildrenDoughnutChart';

        if (Cache::has($keySearchInspectorChildrenDoughnutChart . ':labels') && Cache::has($keySearchInspectorChildrenDoughnutChart . ':data')) {
            $labels = Cache::get($keySearchInspectorChildrenDoughnutChart . ':labels');
            $data = Cache::get($keySearchInspectorChildrenDoughnutChart . ':data');
        } else {
            $giataGeographies = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_geographies';
            $model = ApiSearchInspector::select(
                DB::raw("COALESCE((SELECT city_name FROM $giataGeographies WHERE city_id = JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))),
                    JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination"),
                DB::raw("SUM(oc.children) AS children"),
            )
                ->crossJoin(DB::raw("JSON_TABLE(request, '$.occupancy[*]' COLUMNS (children INT PATH '$.children' DEFAULT '0' ON EMPTY)) oc"))
                ->groupBy('destination')
                ->orderBy('children', 'DESC')
                ->limit(5)
                ->get();

            $labels = $model->pluck('destination');
            $data = $model->pluck('children');

            Cache::put($keySearchInspectorChildrenDoughnutChart . ':labels', $labels, now()->addMinutes(60));
            Cache::put($keySearchInspectorChildrenDoughnutChart . ':data', $data, now()->addMinutes(60));
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
