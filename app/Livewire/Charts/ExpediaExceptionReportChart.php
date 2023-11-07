<?php

namespace App\Livewire\Charts;

use App\Models\ApiExceptionReport;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExpediaExceptionReportChart extends ChartWidget
{
    /**
     * @var string|null
     */
    protected static ?string $heading = 'Expedia Exception Report Chart';

    /**
     * @var string|null
     */
    protected static ?string $pollingInterval = '7200s';

    /**

     * @return array
     */
    protected function getData(): array
    {
        self::$options['scales'] = [
            'y' => [
                'max' => 60,
                'ticks' => [
                    'stepSize' => 2
                ]
            ]
        ];

        $keyExceptionsReportChart = 'ExceptionsReportChart';

        if (Cache::has($keyExceptionsReportChart . ':labels') && Cache::has($keyExceptionsReportChart . ':data')) {
            $labels = Cache::get($keyExceptionsReportChart . ':labels');
            $data = Cache::get($keyExceptionsReportChart . ':data');
        } else {
            $model = ApiExceptionReport::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(level = "success") as success_count'),
                DB::raw('SUM(level = "error") as error_count')
            )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->get();

            $labels = $model->pluck('date');
            $data = [
                'successes' => $model->pluck('success_count'),
                'errors' => $model->pluck('error_count')
            ];

            Cache::put($keyExceptionsReportChart . ':labels', $labels, now()->addMinutes(1440));
            Cache::put($keyExceptionsReportChart . ':data', $data, now()->addMinutes(1440));
        }

        return [
            'datasets' => [
                [
                    'label' => "Success",
                    'backgroundColor' => "rgba(75, 192, 192, 0.2)",
                    'borderColor' => "rgba(75, 192, 192, 1)",
                    'borderWidth' => 1,
                    'data' => $data['successes'],
                ],
                [
                    'label' => "Error",
                    'backgroundColor' => "rgba(255, 99, 132, 0.2)",
                    'borderColor' => "rgba(255, 99, 132, 1)",
                    'borderWidth' => 1,
                    'data' => $data['errors'],
                ]
            ],
            'labels' => $labels,
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
