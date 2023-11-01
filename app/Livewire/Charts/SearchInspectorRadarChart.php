<?php

namespace App\Livewire\Charts;

use App\Models\ApiSearchInspector;
use App\Models\ExpediaContent;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchInspectorRadarChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Search Inspector Radar Chart';

    /**
     * @var string|null
     */
    protected static ?string $pollingInterval = null;

    /**
     * @var string|null
     */
    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $keySearchInspectorRadarChart = 'SearchInspectorRadarChart';

        $labels = [
            'Rating',
            'Rooms',
            'Occupancy',
            'Children',
            'Nights'
        ];

        if (Cache::has($keySearchInspectorRadarChart . ':labels') && Cache::has($keySearchInspectorRadarChart . ':data')) {
            $data = Cache::get($keySearchInspectorRadarChart . ':data');
        } else {
            $model = ApiSearchInspector::select(
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(request, "$.destination")) AS destination'),
                DB::raw('AVG(JSON_EXTRACT(request, "$.rating")) AS average_rating'),
                DB::raw('AVG(SUM(CAST(JSON_EXTRACT(request, "$.occupancy[0].adults") AS SIGNED) + COALESCE(JSON_EXTRACT(request, "$.occupancy[0].children"), 0))) AS total_occupancy'),
                DB::raw('AVG(COALESCE(JSON_EXTRACT(request, "$.occupancy[0].children"), 0))) AS children'),
                DB::raw('AVG(DATEDIFF(JSON_UNQUOTE(JSON_EXTRACT(request, "$.checkout")), JSON_UNQUOTE(JSON_EXTRACT(request, "$.checkin")))) AS days')
            )
            ->groupBy('destination')
            ->orderBy('average_rating', 'DESC')
            ->limit(5)
            ->get();

            $data = $model->pluck('total');

            Cache::put($keySearchInspectorRadarChart . ':data', $data, now()->addMinutes(1440));
        }

        $colors = [];

        for ($i = 0; $i < count($data); $i++) {
            $randomColor = '#' . dechex(mt_rand(0x000000, 0xFFFFFF));
            $colors[] = $randomColor;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }
}
