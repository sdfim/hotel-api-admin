<?php

namespace App\Traits;

/**
 * Trait for creating success and error reports
 * in data loading console commands.
 */
trait ExceptionReportTrait
{
    /**
     * Creates a report on the successful completion of an operation
     */
    private function saveSuccessReport(string $action, string $description, string $content): void
    {
        $this->saveReport($action, $description, $content, 'success');
    }

    /**
     * Creates an error report for an operation
     */
    private function saveErrorReport(string $action, string $description, string $content): void
    {
        $this->saveReport($action, $description, $content);
    }

    /**
     * Base method for creating reports
     */
    private function saveReport(string $action, string $description, string $content, string $level = 'error'): void
    {
        if (! isset($this->apiExceptionReport) || ! isset($this->report_id)) {
            return;
        }

        $supplierId = $this->getSupplierIdForReport();

        $this->apiExceptionReport
            ->save(
                $this->report_id,
                $level,
                $supplierId,
                $action,
                $description,
                $content
            );
    }

    /**
     * Get the supplier ID for the report
     * Override this method in classes that use the trait
     */
    protected function getSupplierIdForReport(): int
    {
        if (isset($this->supplier_id)) {
            return $this->supplier_id;
        }

        if (isset($this->expedia_id)) {
            return $this->expedia_id;
        }

        if (isset($this->giata_id)) {
            return $this->giata_id;
        }

        if (isset($this->iceportal_id)) {
            return $this->iceportal_id;
        }

        return 0;
    }
}
