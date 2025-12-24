<?php

namespace Modules\HotelContentRepository\Jobs;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Services\ImageService;

class UploadRoomImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Hotel $hotel;

    protected array $allRoomImages;

    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(Hotel $hotel, array $allRoomImages, User $user)
    {
        $this->hotel = $hotel;
        $this->allRoomImages = $allRoomImages;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $startTime = now();
        try {
            /** @var ImageService $imageService */
            $imageService = app(ImageService::class);
            $imageService->uploadEpsImagesRoomLevel($this->hotel, $this->allRoomImages);
        } catch (\Exception $e) {
            Notification::make()
                ->title('UploadRoomImages Task Failed')
                ->body('There was an error uploading room images for product '.$this?->hotel->product->name.': '.$e->getMessage())
                ->danger()
                ->sendToDatabase($this->user);

            return;
        }
        $endTime = now();
        $duration = $startTime->diffInSeconds($endTime);

        Notification::make()
            ->title('UploadRoomImages Task Completed')
            ->body('Room Images for product '.$this->hotel->product->name.' have been successfully uploaded. Duration: '.$duration.' seconds.')
            ->success()
            ->duration(0)
            ->sendToDatabase($this->user);
    }
}
