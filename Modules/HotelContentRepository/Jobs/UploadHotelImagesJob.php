<?php

namespace Modules\HotelContentRepository\Jobs;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Services\ImageService;

class UploadHotelImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Product $product;

    protected array $imagesData;

    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product, array $imagesData, User $user)
    {
        $this->product = $product;
        $this->imagesData = $imagesData;
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
            $imageService->uploadEpsImagesHotelLevel($this->product, $this->imagesData);
        } catch (\Exception $e) {
            Notification::make()
                ->title('UploadHotelImages Task Failed')
                ->body('There was an error uploading images for product '.$this?->product->name.': '.$e->getMessage())
                ->danger()
                ->sendToDatabase($this->user);

            return;
        }
        $endTime = now();
        $duration = $startTime->diffInSeconds($endTime);

        Notification::make()
            ->title('UploadHotelImages Task Completed')
            ->body('Images for product '.$this->product->name.' have been successfully uploaded. Duration: '.$duration.' seconds.')
            ->success()
            ->duration(0)
            ->sendToDatabase($this->user);

    }
}
