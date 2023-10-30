<?php

namespace App\Livewire;

use App\Models\GiataProperty;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class GiataCityChart extends ChartWidget
{
    protected static ?string $heading = 'Giata City Chart';

    protected function getData(): array
	{
		$model = GiataProperty::with('giataGeography')
			->select('city_id', 'city', DB::raw('count(*) as total'))
			->groupBy('city_id', 'city')
			->orderBy('total', 'DESC')
			->take(10)
			->get();

		$labels = $model->pluck('city');
		$data = $model->pluck('total');

		$colors = [];
		for ($i = 0; $i < count($data); $i++) {
			$randomColor = '#' . dechex(mt_rand(0x000000, 0xFFFFFF));
			$colors[] = $randomColor;
		}

		return [
			'datasets' => [
				[
					'data' => $data ,
					'backgroundColor' => $colors,
				],
			],
			'labels' => $labels,
		];
	}

    protected function getType(): string
    {
        return 'doughnut';
    }
}
