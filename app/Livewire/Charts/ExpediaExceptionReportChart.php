<?php

namespace App\Livewire\Charts;

use App\Models\ApiExceptionReport;
use Filament\Widgets\ChartWidget;

class ExpediaExceptionReportChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Expedia Exception Report Chart';

    /**
     * @return array
     */
    protected function getData(): array
    {
        $model = ApiExceptionReport::select()
            ->orderBy('created_at', 'DESC')
            ->get();

        $labels = $model->pluck('created_at');
        $data = $model->pluck('total');

        $colors = [];
        for ($i = 0; $i < count($data); $i++) {
            $randomColor = '#' . dechex(mt_rand(0x000000, 0xFFFFFF));
            $colors[] = $randomColor;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Blog posts created',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return 'bar';
    }
}
