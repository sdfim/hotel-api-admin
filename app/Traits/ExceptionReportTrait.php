<?php

namespace App\Traits;

/**
 * Трейт для создания отчетов об успешном выполнении и ошибках
 * в консольных командах загрузки данных.
 */
trait ExceptionReportTrait
{
    /**
     * Создает отчет об успешном выполнении операции
     */
    private function saveSuccessReport(string $action, string $description, string $content): void
    {
        $this->saveReport($action, $description, $content, 'success');
    }

    /**
     * Создает отчет об ошибке выполнения операции
     */
    private function saveErrorReport(string $action, string $description, string $content): void
    {
        $this->saveReport($action, $description, $content);
    }

    /**
     * Базовый метод для создания отчетов
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
     * Получение ID поставщика для отчета
     * Переопределите этот метод в классах, использующих трейт
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
