<?php

namespace App\Livewire\Charts;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExpediaRatingChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Expedia Rating Chart';

    /**
     * @var string|null
     */
    protected static ?string $pollingInterval = '86400s';

    /**
     * @var string|null
     */
    protected static ?string $maxHeight = '500px';

    /**
     * @return array
     */
    protected function getData(): array
    {
        $keyExpediaRatingChart = 'ExpediaRatingChart';

        if (Cache::has($keyExpediaRatingChart . ':labels') && Cache::has($keyExpediaRatingChart . ':data')) {
            $labels = Cache::get($keyExpediaRatingChart . ':labels');
            $data = Cache::get($keyExpediaRatingChart . ':data');
        } else {
            $queryResult = DB::connection('mysql2')->select("
                SELECT rating, COUNT(*) AS total FROM expedia_content_main GROUP BY rating ORDER BY rating DESC
            ");

            $queryResult = json_decode(json_encode($queryResult), true);

            $labels = array_column($queryResult, 'rating');
            $data = array_column($queryResult, 'total');

            Cache::put($keyExpediaRatingChart . ':labels', $labels, now()->addMinutes(1440));
            Cache::put($keyExpediaRatingChart . ':data', $data, now()->addMinutes(1440));
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
