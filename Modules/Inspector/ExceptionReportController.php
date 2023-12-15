<?php

namespace Modules\Inspector;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ApiExceptionReport;

class ExceptionReportController extends BaseInspectorController
{
    /**
     * @param $uuid
     * @param $level
     * @param $supplier_id
     * @param $action
     * @param $description
     * @param $content
     * @return string|bool
     */
    public function save($uuid, $level, $supplier_id, $action, $description, $content): string|bool
    {
        try {
            $this->current_time = microtime(true);
            $hash = md5($description . date("Y-m-d H:i:s"));

            $path = 'exception_report_' . $level . '/' . date("Y-m-d") . '/' . $hash . '.json';

            Storage::put($path, $content);
            Log::debug('ExceptionReportController save to Storage: ' . $this->executionTime() . ' seconds');

            $data = [
                'report_id' => $uuid,
                'level' => $level, // 'error', 'warning', 'info
                'supplier_id' => $supplier_id,
                'action' => $action,
                'description' => $description,
                'response_path' => $path
            ];

            $inspector = ApiExceptionReport::create($data);
            Log::debug('ExceptionReportController save to DB: ' . $this->executionTime() . ' seconds');

            return $inspector ? $uuid : false;

        } catch (\Exception $e) {
            Log::error('Error save ExceptionReportController: ' . $e->getMessage() . ' | ' . $e->getLine() . ' | ' . $e->getFile());

            return false;
        }
    }

    /**
     * @return void
     */
    public function get()
    {
        //
    }

    /**
     * @return void
     */
    public function delete()
    {
        //
    }

    /**
     * @return void
     */
    public function update()
    {
        //
    }
}
