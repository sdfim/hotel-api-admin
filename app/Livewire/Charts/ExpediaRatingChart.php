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
