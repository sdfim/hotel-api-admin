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
    protected static ?string $maxHeight = '500px';

    /**
     * @return array
     */
    protected function getData(): array
    {
        $keySearchInspectorRadarChart = 'SearchInspectorRadarChart';


        // if (Cache::has($keySearchInspectorRadarChart . ':data')) {
        //     $data = Cache::get($keySearchInspectorRadarChart . ':data');
        // } else {
            $data = DB::select("
				SELECT 
					COALESCE(gg.city_name, JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))) AS destination,
					CAST(AVG(JSON_EXTRACT(request, '$.rating')) AS DECIMAL(5,2)) AS avg_rating,
					CAST(AVG(JSON_LENGTH(JSON_UNQUOTE(JSON_EXTRACT(request, '$.occupancy')))) AS DECIMAL(5,2)) AS avg_rooms,
					CAST(AVG(oc.adults + oc.children) AS DECIMAL(5,2)) AS avg_occupancy,
					CAST(AVG(oc.children) AS DECIMAL(5,2)) AS avg_children,
					CAST(AVG(DATEDIFF(JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkout')), JSON_UNQUOTE(JSON_EXTRACT(request, '$.checkin')))) AS DECIMAL(5,2)) AS avg_days
				FROM 
					api_search_inspector
				CROSS JOIN 
					JSON_TABLE(request, '$.occupancy[*]' COLUMNS (adults INT PATH '$.adults' DEFAULT '0' ON EMPTY, children INT PATH '$.children' DEFAULT '0' ON EMPTY)) oc
				LEFT JOIN 
					ujv_api.giata_geographies gg ON gg.city_id = JSON_UNQUOTE(JSON_EXTRACT(request, '$.destination'))
				GROUP BY 
					destination
				ORDER BY 
					avg_rating DESC
				LIMIT 5");

        //     Cache::put($keySearchInspectorRadarChart . ':data', $data, now()->addMinutes(60));
        // }

        $labels = [
            'Rating',
            'Rooms',
            'Occupancy',
            'Children',
            'Nights'
        ];

        $colors = [
            '0, 0, 255',
            '0, 128, 0',
            '255, 0, 0',
            '255, 165, 0',
            '128, 0, 128'
        ];

        $datasets = [];

        foreach ($data as $index => $popularDestination) {

			$popularDestination = (array)$popularDestination;
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
