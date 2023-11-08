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
            $giataGeographies = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_geographies';
            $model = ApiSearchInspector::select(
                DB::raw("COALESCE(gg.city_name, JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination"),
                DB::raw("SUM(oc.adults + oc.children) AS occupancy"),
            )
                ->crossJoin(DB::raw("JSON_TABLE(request, '$.occupancy[*]' COLUMNS (adults INT PATH '$.adults' DEFAULT '0' ON EMPTY, children INT PATH '$.children' DEFAULT '0' ON EMPTY)) oc"))
                ->leftJoin($giataGeographies . ' AS gg', function ($join) {
                    $join->on(DB::raw("gg.city_id"), '=', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))"));
                })
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
            'rgb(0, 0, 139, 0.8)',
            'rgb(75, 0, 130, 0.8)',
            'rgb(0, 128, 128, 0.8)',
            'rgb(34, 139, 34, 0.8)',
            'rgb(255, 215, 0, 0.8)',
            'rgb(255, 127, 80, 0.8)',
            'rgb(230, 230, 250, 0.8)',
            'rgb(152, 251, 152, 0.8)',
            'rgb(255, 218, 185, 0.8)',
            'rgb(135, 206, 235, 0.8)',
            'rgb(220, 20, 60, 0.8)',
            'rgb(218, 165, 32, 0.8)',
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
