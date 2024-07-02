<?php

namespace App\Livewire\Charts;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExpediaExceptionReportChart extends ChartWidget
{
    protected static ?string $heading = 'Expedia Exception Report Chart';

    protected static ?string $pollingInterval = '7200s';

    protected function getData(): array
    {
        self::$options['scales'] = [
            'y' => [
                'max' => 60,
                'ticks' => [
                    'stepSize' => 2,
                ],
            ],
        ];

        $keyExceptionsReportChart = 'ExceptionsReportChart';

        if (Cache::has($keyExceptionsReportChart.':labels') && Cache::has($keyExceptionsReportChart.':data')) {
            $labels = Cache::get($keyExceptionsReportChart.':labels');
            $data = Cache::get($keyExceptionsReportChart.':data');
        } else {
            $queryResult = DB::select("
                SELECT
                    DATE(created_at) AS date,
                    SUM(level = 'success') AS success_count,
                    SUM(level = 'error') AS error_count
				FROM
				    api_exception_reports
				WHERE
				    created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
				GROUP BY
				    date");

            $queryResult = json_decode(json_encode($queryResult), true);

            $labels = array_column($queryResult, 'date');
            $data = [
                'successes' => array_column($queryResult, 'success_count'),
                'errors' => array_column($queryResult, 'error_count'),
            ];

            Cache::put($keyExceptionsReportChart.':labels', $labels, now()->addMinutes(1440));
            Cache::put($keyExceptionsReportChart.':data', $data, now()->addMinutes(1440));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Success',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                    'data' => $data['successes'],
                ],
                [
                    'label' => 'Error',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                    'data' => $data['errors'],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
