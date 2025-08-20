<?php

namespace App\Jobs\AddHotel;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Modules\Enums\SupplierNameEnum;

class AiMergeSuppliers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $giataId, public User $recipient, public array $supplierDataForMerge) {}

    // ...
    public function handle(): void
    {
        logger()->info('LoggerFlowHotel _ AiMergeSuppliers s1', ['supplierDataForMerge' => $this->supplierDataForMerge]);

        $startTime = microtime(true);
        $maxWaitTime = 20;

        $statusFetched = false;

        while (! $statusFetched && (microtime(true) - $startTime) < $maxWaitTime) {
            logger()->info('LoggerFlowHotel _ Waiting for HBSI data for giataId: '.$this->giataId);

            $statusFetched = Cache::get('make_hotel:'.$this->giataId.':hbsi_data_fetched', false);

            if (! $statusFetched) {
                sleep(1);
            }
        }

        if (! $statusFetched) {
            Notification::make()
                ->title('Timeout waiting for HBSI data.')
                ->danger()
                ->broadcast($this->recipient);
        } else {
            $hbsiDataForMerge = Cache::get('hbsi_supplier_data_'.$this->giataId, []);
            if (! empty($hbsiDataForMerge)) {
                $this->supplierDataForMerge[SupplierNameEnum::HBSI->value] = $hbsiDataForMerge;
                logger()->info('LoggerFlowHotel _ AiMergeSuppliers s2', ['hbsiDataForMerge' => $hbsiDataForMerge]);
            } else {
                Notification::make()
                    ->title('HBSI data not found in cache for giataId: '.$this->giataId)
                    ->danger()
                    ->broadcast($this->recipient);
            }
        }

        logger()->info('LoggerFlowHotel _ AiMergeSuppliers s3', ['supplierDataForMerge' => $this->supplierDataForMerge]);

        $supplierDataJson = json_encode($this->supplierDataForMerge, JSON_UNESCAPED_UNICODE);

//        Artisan::call('merge:suppliers:openai-provider', [
//            'supplierData' => $supplierDataJson,
//            'giata_id' => $this->giataId,
//        ]);
        Artisan::call('merge:suppliers:gemini-provider', [
            'supplierData' => $supplierDataJson,
            'giata_id' => $this->giataId,
        ]);

        Cache::put('make_hotel:'.$this->giataId.':data_merged', true, 60 * 60 * 24);

        Notification::make()
            ->title('✅ Suppliers merged successfully. HotelGiataCode = '.$this->giataId)
            ->success()
            ->duration(10000)
            ->broadcast($this->recipient);
    }
}
