<?php

namespace Modules\Inspector;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\ApiExceptionReport;
use Illuminate\Support\Str;
use Modules\Inspector\BaseInspectorController;

class ExceptionReportController extends BaseInspectorController
{
	public function save($uuid, $level, $supplier_id, $action, $description, $content) : string|bool
	{
		try {
			$this->current_time = microtime(true);
			$hash = md5($description);
			$content = json_encode($content);

			$path = 'exception_report_' . $level. '/' . date("Y-m-d") . '/' . $hash.'.json';

			Storage::put($path, $content);
			\Log::debug('ExceptionReportController save to Storage: ' . $this->executionTime() . ' seconds');

			$uuid = Str::uuid()->toString();

			$data = [
				'report_id' => $uuid,
				'level' => $level, // 'error', 'warning', 'info
				'supplier_id' => $supplier_id,
				'action' => $action,
				'description' => $description,
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