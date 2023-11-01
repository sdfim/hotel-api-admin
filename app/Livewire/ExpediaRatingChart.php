<?php

namespace App\Livewire;

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
    protected static ?string $maxHeight = '300px';

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

    /**
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}
