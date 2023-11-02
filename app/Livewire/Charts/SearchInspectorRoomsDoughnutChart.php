<?php

namespace App\Livewire\Charts;

use App\Models\ApiSearchInspector;
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
    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $keySearchInspectorRoomsDoughnutChart = 'SearchInspectorRoomsDoughnutChart';

        if (Cache::has($keySearchInspectorRoomsDoughnutChart . ':labels') && Cache::has($keySearchInspectorRoomsDoughnutChart . ':data')) {
            $labels = Cache::get($keySearchInspectorRoomsDoughnutChart . ':labels');
            $data = Cache::get($keySearchInspectorRoomsDoughnutChart . ':data');
        } else {
            $giataGeographies = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_geographies';
            $model = ApiSearchInspector::select(
                DB::raw("COALESCE((SELECT city_name FROM $giataGeographies WHERE city_id = JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))),
                    JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination"),
                DB::raw("CAST(AVG(JSON_EXTRACT(request, '$.rating')) AS DECIMAL(5,2)) AS avg_rating"),
                DB::raw("SUM(JSON_LENGTH(JSON_UNQUOTE(JSON_EXTRACT(request, '$.occupancy')))) AS rooms"),
            )
                ->groupBy('destination')
                ->orderBy('avg_rating', 'DESC')
                ->limit(5)
                ->get();

            $labels = $model->pluck('destination');
            $data = $model->pluck('rooms');

            Cache::put($keySearchInspectorRoomsDoughnutChart . ':labels', $labels, now()->addMinutes(60));
            Cache::put($keySearchInspectorRoomsDoughnutChart . ':data', $data, now()->addMinutes(60));
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
