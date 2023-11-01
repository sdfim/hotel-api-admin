<?php

namespace App\Livewire;

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
            $model = GiataProperty::with('giataGeography')
                ->select('city_id', 'city', DB::raw('count(*) as total'))
                ->groupBy('city_id', 'city')
                ->orderBy('total', 'DESC')
                ->take(10)
                ->get();

            $labels = $model->pluck('city');
            $data = $model->pluck('total');

            Cache::put($keyGiataCityChart . ':labels', $labels, now()->addMinutes(1440));
            Cache::put($keyGiataCityChart . ':data', $data, now()->addMinutes(1440));
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
