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

    public function handle(): void
    {
        logger()->info('LoggerFlowHotel _ AiMergeSuppliers s1', ['supplierDataForMerge' => $this->supplierDataForMerge]);

        $maxWait = 20;
        $statusFetched = false;
        for ($i = 0; $i < $maxWait; $i++) {

            logger()->info('LoggerFlowHotel _ Waiting for HBSI data for giataId: '.$this->giataId.' (iteration: '.$i.')');

            $statusFetched = Cache::get('hbsi_data_fetched_'.$this->giataId, false);
            if ($statusFetched) {
                break;
            }
            sleep(1);
        }

        if (! $statusFetched) {
            Notification::make()
                ->title('Timeout waiting for HBSI data.')
                ->danger()
                ->broadcast($this->recipient);

            return;
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

        Notification::make()
            ->title('Suppliers merged successfully.')
            ->success()
            ->broadcast($this->recipient);
    }
}
