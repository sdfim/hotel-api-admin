<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfGeneratorService
{
    /**
     * Генерация PDF из Blade-шаблона и сохранение на диск
     *
     * @param  string  $view  Путь к Blade-шаблону (например, 'pdf.booking-confirmation')
     * @param  array  $data  Данные для подстановки в шаблон
     * @param  string  $fileName  Имя файла (например, 'booking-181447.pdf')
     * @param  string  $disk  Диск (по умолчанию 'local')
     * @param  string  $path  Папка для сохранения (по умолчанию 'confirmations')
     * @return string Полный путь к сохранённому файлу
     */
    public function generateAndSave(string $view, array $data, string $fileName, string $disk = 'local', string $path = 'confirmations'): string
    {
        $pdf = Pdf::loadView($view, $data);

        $filePath = $path.'/'.$fileName;
        Storage::put($filePath, $pdf->output());

        return Storage::path($filePath);
    }

    /**
     * Генерация PDF без сохранения (например, для attachData в письме)
     *
     * @return string PDF-контент
     */
    public function generateRaw(string $view, array $data): string
    {
        return Pdf::loadView($view, $data)->output();
    }
}
