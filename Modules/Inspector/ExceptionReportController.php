<?php

namespace Modules\Inspector;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\ApiExceptionReport;
use Illuminate\Support\Str;
use Modules\Inspector\BaseInspectorController;

class ExceptionReportController extends BaseInspectorController
{
	public function save($task, $content, $supplier_id , $type = 'error') : string|bool
	{
		try {
			$this->current_time = microtime(true);
			
			$name = $task . '_' . Carbon::now()->toDateTimeString();
			$path = 'report_' . $type. '/' . $name;

			Storage::put($path, $content);
			\Log::debug('ExceptionReportController save to Storage: ' . $this->executionTime() . ' seconds');

			$uuid = Str::uuid()->toString();

			$data = [
				'id' => $uuid,
				'supplier_id' => $supplier_id,
				'type' => $type,
				'request' => $task,
				'response_path' => $path
			];

			$inspector = ApiExceptionReport::create($data);
			\Log::debug('ExceptionReportController save to DB: ' . $this->executionTime() . ' seconds');

			return $inspector ? $uuid : false;

		} catch (\Exception $e) {
            \Log::error('Error save ExceptionReportController: ' . $e->getMessage(). ' | ' . $e->getLine() . ' | ' . $e->getFile());
			
			return false;
		}
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