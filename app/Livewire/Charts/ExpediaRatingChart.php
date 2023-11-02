<?php

namespace App\Livewire\Charts;

use App\Models\ExpediaContent;
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
        $keyExpediaRatingChart = 'ExpediaRatingChart';

        if (Cache::has($keyExpediaRatingChart . ':labels') && Cache::has($keyExpediaRatingChart . ':data')) {
            $labels = Cache::get($keyExpediaRatingChart . ':labels');
            $data = Cache::get($keyExpediaRatingChart . ':data');
        } else {
            $model = ExpediaContent::select('rating', DB::raw('count(*) as total'))
                ->groupBy('rating')
                ->orderBy('rating', 'DESC')
                ->get();

            $labels = $model->pluck('rating');
            $data = $model->pluck('total');

            Cache::put($keyExpediaRatingChart . ':labels', $labels, now()->addMinutes(1440));
            Cache::put($keyExpediaRatingChart . ':data', $data, now()->addMinutes(1440));
        }

        $colors = [
            'rgb(0, 0, 255)',
            'rgb(0, 128, 0)',
            'rgb(255, 0, 0)',
            'rgb(255, 165, 0)',
            'rgb(128, 0, 128)',
            'rgb(0, 128, 128)',
            'rgb(255, 255, 0)',
            'rgb(255, 105, 180)',
            'rgb(139, 69, 19)',
            'rgb(0, 255, 255)',
            'rgb(0, 255, 0)',
            'rgb(255, 0, 255)'
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
