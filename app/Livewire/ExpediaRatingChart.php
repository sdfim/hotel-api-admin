<?php

namespace App\Livewire;

use App\Models\ExpediaContent;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ExpediaRatingChart extends ChartWidget
{
	protected static ?string $heading = 'Expedia Rating Chart';

	protected function getData(): array
	{

		$model = ExpediaContent::select('rating', DB::raw('count(*) as total'))
			->groupBy('rating')
			->orderBy('rating', 'DESC')
			->get();

		$labels = $model->pluck('rating');
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
