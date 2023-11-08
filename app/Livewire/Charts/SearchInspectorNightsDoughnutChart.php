<?php

namespace App\Livewire\Charts;

use App\Models\ApiSearchInspector;
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
    protected static ?string $maxHeight = '400px';


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
            $giataGeographies = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_geographies';
            $model = ApiSearchInspector::select(
                DB::raw("COALESCE(gg.city_name, JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination"),
                DB::raw("SUM(DATEDIFF(JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkout')), JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkin'))) - 1) AS nights")
            )
                ->leftJoin($giataGeographies . ' AS gg', function ($join) {
                    $join->on(DB::raw("gg.city_id"), '=', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))"));
                })
                ->groupBy('destination')
                ->orderBy('nights', 'DESC')
                ->limit(5)
                ->get();

            $labels = $model->pluck('destination');
            $data = $model->pluck('nights');

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
