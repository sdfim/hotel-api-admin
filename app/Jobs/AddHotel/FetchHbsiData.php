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
        Notification::make()
            ->title('Started receiving data from HBSI...')
            ->info()
            ->broadcast($this->recipient);

        Artisan::call('hbsi:get-data', ['giataId' => $this->giataId]);

        Cache::put('hbsi_data_fetched_'.$this->giataId, true);

        Notification::make()
            ->title('Data from HBSI received successfully.')
            ->success()
            ->broadcast($this->recipient);
    }
}
