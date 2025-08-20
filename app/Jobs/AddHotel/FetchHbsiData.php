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

class FetchHbsiData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $giataId, public User $recipient) {}

    public function handle(): void
    {
        Artisan::call('hbsi:get-data', ['giataId' => $this->giataId]);
        Cache::put('make_hotel:'.$this->giataId.':hbsi_data_fetched', true, 60 * 60 * 24);

        logger()->info('LoggerFlowHotel _ FetchHbsiData', [
            'giataId' => $this->giataId,
            'recipient' => $this->recipient->id,
        ]);

        Notification::make()
            ->title('✅ Data from HBSI received successfully. HotelGiataCode = '.$this->giataId)
            ->success()
            ->duration(10000)
            ->broadcast($this->recipient);
    }
}
