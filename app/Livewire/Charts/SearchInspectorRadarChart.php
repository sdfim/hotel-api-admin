<?php

namespace App\Livewire\Charts;

use App\Models\ApiSearchInspector;
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
        $keySearchInspectorRadarChart = 'SearchInspectorRadarChart';

        if (Cache::has($keySearchInspectorRadarChart . ':data')) {
            $data = Cache::get($keySearchInspectorRadarChart . ':data');
        } else {
            $giataGeographies = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_geographies';
            $data = ApiSearchInspector::select(
                DB::raw("COALESCE(gg.city_name, JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination"),
                DB::raw("CAST(AVG(JSON_EXTRACT(request, '$.rating')) AS DECIMAL(5,2)) AS avg_rating"),
                DB::raw("CAST(AVG(JSON_LENGTH(JSON_UNQUOTE(JSON_EXTRACT(request, '$.occupancy')))) AS DECIMAL(5,2)) AS avg_rooms"),
                DB::raw("CAST(AVG(oc.adults + oc.children) AS DECIMAL(5,2)) AS avg_occupancy"),
                DB::raw("CAST(AVG(oc.children) AS DECIMAL(5,2)) AS avg_children"),
                DB::raw("CAST(AVG(DATEDIFF(JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkout')), JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkin')))) AS DECIMAL(5,2)) AS avg_days")
            )
                ->crossJoin(DB::raw("JSON_TABLE(request, '$.occupancy[*]' COLUMNS (adults INT PATH '$.adults' DEFAULT '0' ON EMPTY, children INT PATH '$.children' DEFAULT '0' ON EMPTY)) oc"))
                ->leftJoin($giataGeographies . ' AS gg', function ($join) {
                    $join->on(DB::raw("gg.city_id"), '=', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))"));
                })
                ->groupBy('destination')
                ->orderBy('avg_rating', 'DESC')
                ->limit(5)
                ->get()
                ->toArray();

            Cache::put($keySearchInspectorRadarChart . ':data', $data, now()->addMinutes(60));
        }

        $labels = [
            'Rating',
            'Rooms',
            'Occupancy',
            'Children',
            'Nights'
        ];

        $colors = [
            '70, 130, 180',
            '0, 128, 0',
            '128, 0, 128',
            '139, 69, 19',
            '0, 0, 128',
        ];

        $datasets = [];

        foreach ($data as $index => $popularDestination) {
            $dataset = [
                'label' => $popularDestination['destination'],
                'data' => [
                    $popularDestination['avg_rating'],
                    $popularDestination['avg_rooms'],
                    $popularDestination['avg_occupancy'],
                    $popularDestination['avg_children'],
                    $popularDestination['avg_days'] - 1,
                ],
                'fill' => true,
                'backgroundColor' => "rgb($colors[$index], 0.2)",
                'borderColor' => "rgb($colors[$index])",
                'pointBackgroundColor' => "rgb($colors[$index])",
                'pointBorderColor' => '#fff',
                'pointHoverBackgroundColor' => '#fff',
                'pointHoverBorderColor' => "rgb($colors[$index])"
            ];

            $datasets[] = $dataset;
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return 'radar';
    }
}
