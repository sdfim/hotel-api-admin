<?php

namespace App\Livewire\Charts;

use App\Models\GiataProperty;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GiataCityChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Giata City Chart';

    /**
     * @var string|null
     */
    protected static ?string $pollingInterval = '86400s';

    /**
     * @var string|null
     */
    protected static ?string $maxHeight = '400px';

    /**
     * @return array
     */
    protected function getData(): array
    {
        $keyGiataCityChart = 'GiataCityChart';

        if (Cache::has($keyGiataCityChart . ':labels') && Cache::has($keyGiataCityChart . ':data')) {
            $labels = Cache::get($keyGiataCityChart . ':labels');
            $data = Cache::get($keyGiataCityChart . ':data');
        } else {
            $model = GiataProperty::select(
                'city_id', 'city', 'locale',
                DB::raw("CONCAT(city, ', ', locale) AS city_country"),
                DB::raw('COUNT(*) AS total')
            )
                ->groupBy('city_id', 'city')
                ->orderBy('total', 'DESC')
                ->take(10)
                ->get();

            $labels = $model->pluck('city_country');
            $data = $model->pluck('total');

            Cache::put($keyGiataCityChart . ':labels', $labels, now()->addMinutes(1440));
            Cache::put($keyGiataCityChart . ':data', $data, now()->addMinutes(1440));
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
