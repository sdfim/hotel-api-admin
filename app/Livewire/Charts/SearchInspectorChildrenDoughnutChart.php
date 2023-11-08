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

    /**
     * @return array
     */
    protected function getData(): array
    {
        $keySearchInspectorChildrenDoughnutChart = 'SearchInspectorChildrenDoughnutChart';

        if (Cache::has($keySearchInspectorChildrenDoughnutChart . ':labels') && Cache::has($keySearchInspectorChildrenDoughnutChart . ':data')) {
            $labels = Cache::get($keySearchInspectorChildrenDoughnutChart . ':labels');
            $data = Cache::get($keySearchInspectorChildrenDoughnutChart . ':data');
        } else {
            $giataGeographies = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_geographies';
            $model = ApiSearchInspector::select(
                DB::raw("COALESCE(gg.city_name, JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination"),
                DB::raw("SUM(oc.children) AS children"),
            )
                ->crossJoin(DB::raw("JSON_TABLE(request, '$.occupancy[*]' COLUMNS (children INT PATH '$.children' DEFAULT '0' ON EMPTY)) oc"))
                ->leftJoin($giataGeographies . ' AS gg', function ($join) {
                    $join->on(DB::raw("gg.city_id"), '=', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))"));
                })
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
            'rgb(25, 25, 112, 0.85)',
            'rgb(75, 0, 130, 0.85)',
            'rgb(0, 255, 255, 0.85)',
            'rgb(50, 205, 50, 0.85)',
            'rgb(255, 215, 0, 0.85)',
            'rgb(250, 128, 114, 0.85)',
            'rgb(200, 162, 200, 0.85)',
            'rgb(152, 255, 152, 0.85)',
            'rgb(255, 204, 153, 0.85)',
            'rgb(0, 127, 255, 0.85)',
            'rgb(220, 20, 60, 0.85)',
            'rgb(255, 191, 0, 0.85)'
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
