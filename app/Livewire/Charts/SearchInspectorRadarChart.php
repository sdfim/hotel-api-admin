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

        if (Cache::has($keySearchInspectorRadarChart . ':data')) {
            $theMostPopularDestinations = Cache::get($keySearchInspectorRadarChart . ':data');
        } else {
            $theMostPopularDestinations = ApiSearchInspector::select(
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination')) AS destination"),
                DB::raw("CAST(AVG(JSON_EXTRACT(request, '$.rating')) AS DECIMAL(5,2)) AS avg_rating"),
                DB::raw("CAST(AVG(JSON_LENGTH(JSON_UNQUOTE(JSON_EXTRACT(request, '$.occupancy')))) AS DECIMAL(5,2)) AS avg_rooms"),
                DB::raw("CAST(AVG(JSON_UNQUOTE(JSON_EXTRACT(request, '$.occupancy[0].adults')) + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(request, '$.occupancy[0].children')), 0)) AS DECIMAL(5,2)) AS avg_occupancy"),
                DB::raw("CAST(AVG(IFNULL(JSON_UNQUOTE(JSON_EXTRACT(request, '$.occupancy[0].children')), 0)) AS DECIMAL(5,2)) AS avg_children"),
                DB::raw("CAST(AVG(DATEDIFF(JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkout')), JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkin')))) AS DECIMAL(5,2)) AS avg_days")
            )
                ->groupBy('destination')
                ->orderBy('avg_rating', 'DESC')
                ->limit(5)
                ->get()
                ->toArray();

            Cache::put($keySearchInspectorRadarChart . ':data', $theMostPopularDestinations, now()->addMinutes(1440));
        }

        $datasets = [];

        foreach ($theMostPopularDestinations as $popularDestination) {
            $red = rand(0, 255);
            $green = rand(0, 255);
            $blue = rand(0, 255);

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
                'backgroundColor' => "rgb($red, $green, $blue, 0.2)",
                'borderColor' => "rgb($red, $green, $blue)",
                'pointBackgroundColor' => "rgb($red, $green, $blue)",
                'pointBorderColor' => '#fff',
                'pointHoverBackgroundColor' => '#fff',
                'pointHoverBorderColor' => "rgb($red, $green, $blue)"
            ];

            $datasets[] = $dataset;
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }
}
