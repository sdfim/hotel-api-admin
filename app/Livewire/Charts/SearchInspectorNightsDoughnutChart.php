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


    protected function getData(): array
    {
        $keySearchInspectorNightsDoughnutChart = 'SearchInspectorNightsDoughnutChart';

        if (Cache::has($keySearchInspectorNightsDoughnutChart . ':labels') && Cache::has($keySearchInspectorNightsDoughnutChart . ':data')) {
            $labels = Cache::get($keySearchInspectorNightsDoughnutChart . ':labels');
            $data = Cache::get($keySearchInspectorNightsDoughnutChart . ':data');
        } else {
            $giataGeographies = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_geographies';
            $model = ApiSearchInspector::select(
                DB::raw("COALESCE((SELECT city_name FROM $giataGeographies WHERE city_id = JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))),
                    JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination"),
                DB::raw("SUM(DATEDIFF(JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkout')), JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkin'))) - 1) AS nights")
            )
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
