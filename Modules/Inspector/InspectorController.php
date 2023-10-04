<?php

namespace Modules\Inspector;


use Illuminate\Support\Facades\Cache;

class InspectorController
{
	public function save()
	{
		$value = Cache::get('dataPriceAll');
		$data = json_decode($value);
		return view('inspector::index', ['data' => $data]);
	}

	public function get()
	{
		//
	}

	public function delete()
	{
		//
	}

	public function update()
	{
		//
	}
}