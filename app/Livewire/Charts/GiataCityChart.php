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

        Cache::delete($keyGiataCityChart . ':labels');
        Cache::delete($keyGiataCityChart . ':data');

        if (Cache::has($keyGiataCityChart . ':labels') && Cache::has($keyGiataCityChart . ':data')) {
            $labels = Cache::get($keyGiataCityChart . ':labels');
            $data = Cache::get($keyGiataCityChart . ':data');
        } else {
            $model = GiataProperty::select('city_id', 'city', DB::raw('count(*) as total'))
                ->groupBy('city_id', 'city')
                ->orderBy('total', 'DESC')
                ->take(10)
                ->get();

            $labels = $model->pluck('city');
            $data = $model->pluck('total');

            Cache::put($keyGiataCityChart . ':labels', $labels, now()->addMinutes(1440));
            Cache::put($keyGiataCityChart . ':data', $data, now()->addMinutes(1440));
        }

        $colors = [
            'rgb(0, 0, 255, 0.8)',
            'rgb(0, 128, 0, 0.8)',
            'rgb(255, 0, 0, 0.8)',
            'rgb(255, 165, 0, 0.8)',
            'rgb(128, 0, 128, 0.8)',
            'rgb(0, 128, 128, 0.8)',
            'rgb(255, 255, 0, 0.8)',
            'rgb(255, 105, 180, 0.8)',
            'rgb(139, 69, 19, 0.8)',
            'rgb(0, 255, 255, 0.8)',
            'rgb(0, 255, 0, 0.8)',
            'rgb(255, 0, 255, 0.8)'
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
