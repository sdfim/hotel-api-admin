<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Modules\HotelContentRepository\Models\Hotel;

class HstepProcessHotelImages extends Command
{
    protected $signature = 'process:hotel-images-thumbnails {operation?}';

    protected $description = 'Process hotel images and upload or update hero images';

    public function handle()
    {
        $this->warn('-> H step Process Hotel Images');

        $operation = $this->argument('operation') ?? 'update';
        $hotels = Hotel::all();

        $this->newLine();

        $this->withProgressBar($hotels, function ($hotel) use ($operation) {
            $galleryImage = $hotel->product->galleries()
                ->with(['images' => function ($query) {
                    $query->where('tag', 'Gallery')->whereIn('weight', [0, 1]);
                }])
                ->get()
                ->pluck('images')
                ->flatten()
                ->sortBy('weight')
                ->firstWhere('tag', 'Gallery');

            if ($galleryImage) {
                $imageUrl = env('CRM_PATH_IMAGES', '').$galleryImage->image_url;
                $imageContent = Http::get($imageUrl)->body();

                $uuid = Str::uuid();
                $hotelNameSlug = Str::slug($hotel->product->name);
                $filamentPath = env('FILAMENT_FILESYSTEM_DISK', '') === 's3' ? '' : 'public/';
                $heroImagePath = "products/{$hotelNameSlug}-{$uuid}.jpg";
                $heroThumbnailPath = "products/thumbnails/{$hotelNameSlug}-{$uuid}.jpg";

                // Store the original image
                Storage::put($filamentPath.$heroImagePath, $imageContent);

                // Create and store the thumbnail
                try {
                    $image = Image::read(Storage::get($filamentPath.$heroImagePath));
                } catch (\Exception $e) {
                    $this->error("Failed to process image for hotel ID {$hotel->id}: {$e->getMessage()}");
                    return;
                }

                $image->resize(150, 150);
                Storage::put($filamentPath.$heroThumbnailPath, (string) $image->encode());

                if ($operation === 'upload') {
                    $hotel->product->hero_image = $heroImagePath;
                    $hotel->product->hero_image_thumbnails = $heroThumbnailPath;
                } elseif ($operation === 'update') {
                    $hotel->product->update([
                        'hero_image' => $heroImagePath,
                        'hero_image_thumbnails' => $heroThumbnailPath,
                    ]);
                }

                $hotel->product->save();

                $this->output->write("\033[1A\r\033[KHero image for hotel ID {$hotel->id} processed.\n");
            } else {
                $this->output->write("\033[1A\r\033[KNo gallery image found for hotel ID {$hotel->id}.\n");
            }
        });

        $this->info("\nHotel images processed successfully.");

        return 0;
    }
}
