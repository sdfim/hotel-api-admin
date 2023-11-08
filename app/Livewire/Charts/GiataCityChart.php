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
    protected static ?string $pollingInterval = null;

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
