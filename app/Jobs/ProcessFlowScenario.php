<?php

namespace App\Jobs;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ProcessFlowScenario implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $data;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, User $user)
    {
        $this->data = $data;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $key_rs_cache = 'flow_scenario_result_'.md5(json_encode($this->data));
        $this->data['key_rs_cache'] = $key_rs_cache;

        Artisan::call('flow:book-diff-scenarios', ['data' => $this->data]);

        $output = Cache::get($key_rs_cache);

        $searchId = Arr::get($output, 'search_id');
        $bookingId = Arr::get($output, 'booking_id');
        $bookingItem = Arr::get($output, 'booking_item');

        Notification::make()
            ->title('Flow scenario processed successfully.')
            ->body('The flow scenario has been processed successfully.'.
                ($searchId ? " Search ID: {$searchId}." : '').
                ($bookingId ? " Booking ID: {$bookingId}." : '').
                ($bookingItem ? " Booking Item: {$bookingItem}." : ''))
            ->success()
            ->sendToDatabase($this->user);
    }
}
