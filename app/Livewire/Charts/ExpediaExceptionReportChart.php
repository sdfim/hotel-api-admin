<?php

namespace App\Livewire\Charts;

use App\Models\ApiExceptionReport;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ExpediaExceptionReportChart extends ChartWidget
{
    protected static ?string $heading = 'Expedia Exception Report Chart';

    protected function getData(): array
    {
        $currentDate = Carbon::now();
        $dateArray = [];
        $successCount = [];
        $errorsCount = [];

        $daysCount = 30; // How many days to display in the chart

        for ($i = 0; $i < $daysCount; $i++) {
            $dateArray[] = $currentDate->subDay()->toDateString();
        }
        
        for($i = 0; $i < count($dateArray); $i++){
            $successCount[$i] = ApiExceptionReport::whereDate('created_at', $dateArray[$i])->where('level','success')->count();
            $errorsCount[$i] = ApiExceptionReport::whereDate('created_at', $dateArray[$i])->where('level','error')->count();
        }

        return [
            'datasets' => [
                [
                    'label'=> "Success",
                    'backgroundColor'=> "rgba(75, 192, 192, 0.2)",
                    'borderColor'=> "rgba(75, 192, 192, 1)",
                    'borderWidth'=> 1,
                    'data' => $successCount // Значение первого бара
                ],
                [
                    'label'=> "Error",
                    'backgroundColor'=> "rgba(255, 99, 132, 0.2)",
                    'borderColor'=> "rgba(255, 99, 132, 1)",
                    'borderWidth'=> 1,
                    'data' => $errorsCount // Значение первого бара
                ]
            ],
            'labels' => $dateArray,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
