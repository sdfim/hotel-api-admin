<?php

namespace Modules\HotelContentRepository\Services;

use App\Models\Configurations\ConfigImageCategory;
use Illuminate\Support\Facades\Storage;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesTable;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageSection;
use Modules\HotelContentRepository\Models\Product;

class ImageService
{
    private array $categoriesBySize = [
        1000 => 'big',
        500 => 'medium',
        314 => 'thumbnail',
        70 => 'icon',
    ];

    public function saveImageToStorage(string $url, ?string $directory = null): ?string
    {
        $disk = config('filament.default_filesystem_disk', 'public');
        try {
            $contents = file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 5]]));
        } catch (\Exception $e) {
            logger('Failed to fetch image: '.$e->getMessage());

            return null;
        }

        $fileName = basename($url);
        $path = Storage::disk($disk)->put($directory.'/'.$fileName, $contents);

        return $path ? $directory.'/'.$fileName : '';
    }

    public function uploadEpsImagesHotelLevel(Product $product, array $imagesData)
    {
        ['filePath' => $filePath, 'galleryName' => $galleryName, 'description' => $description] = HotelImagesTable::generateGalleryDetails($product);
        $description = 'Product Image Gallery: '.$galleryName;
        $imageSection = ImageSection::where('name', 'hotel')->first();

        $this->processImagesForGallery($imagesData, $product, $galleryName, $description, $imageSection);
    }

    public function uploadEpsImagesRoomLevel(Hotel $hotel, array $allRoomImages)
    {
        $rooms = $hotel->rooms; // Assuming $product->rooms gives the list of rooms

        foreach ($rooms as $room) {
            $roomId = $room->id;

            if (! isset($allRoomImages[$roomId])) {
                continue; // Skip if no images are provided for this room
            }

            $imagesData = $allRoomImages[$roomId];

            ['filePath' => $filePath, 'galleryName' => $galleryName, 'description' => $description] = HotelImagesTable::generateGalleryDetails($hotel->product);
            $galleryName = $galleryName." - Room {$room->id}";
            $description = 'Room Image Gallery: '.$galleryName;
            $imageSection = ImageSection::where('name', 'room')->first();

            $this->processImagesForGallery($imagesData, $room, $galleryName, $description, $imageSection);
        }
    }

    private function processImagesForGallery($imagesData, $productOrRoom, $galleryName, $description, $imageSection)
    {
        foreach ($imagesData as $w => $image) {
            $links = $image['links'] ?? [];
            $caption = $image['caption'] ?? null;

            // Check if the gallery already exists
            $gallery = $productOrRoom->galleries()->where('gallery_name', $galleryName)->first();

            if (! $gallery) {
                $gallery = $productOrRoom->galleries()->make();
                $gallery->forceFill([
                    'gallery_name' => $galleryName,
                    'description' => $description,
                ])->save();
            }

            $this->checkAndSaveImage($links, $gallery, $galleryName, $imageSection, $caption, $w);

            // Attach the gallery to the product or room if not already attached
            if (! $productOrRoom->galleries()->where('id', $gallery->id)->exists()) {
                $productOrRoom->galleries()->attach($gallery->id);
            }
        }
    }

    private function checkAndSaveImage($links, $gallery, $galleryName, $imageSection, $caption, $w)
    {
        $directory = null;
        foreach ($links as $size => $link) {
            $galleryGallery = strstr($galleryName, ' - Room', true) ?: $galleryName;

            $directory = 'images/'.$galleryGallery;
            $fileName = basename($link['href']);
            $filePath = $directory.'/'.$fileName;

            $tag = $size;
            $candidateSize = str_replace('px', '', $size);

            if (array_key_exists($candidateSize, $this->categoriesBySize)) {
                $tag .= ';'.$this->categoriesBySize[$candidateSize];
            }

            $existingImage = Image::where('image_url', $filePath)->first();

            if ($existingImage) {
                $existingImage->update([
                    'tag' => $tag,
                    'weight' => $w,
                    'section_id' => $imageSection->id,
                    'alt' => $caption,
                ]);
                if (! $gallery->images()->where('id', $existingImage->id)->exists()) {
                    $gallery->images()->attach($existingImage->id);
                }
            } else {
                $p = $this->saveImageToStorage($link['href'], $directory);

                if (! $p) {
                    continue;
                }
                $gallery->images()->create([
                    'image_url' => $filePath,
                    'tag' => $tag,
                    'weight' => $w,
                    'section_id' => $imageSection->id,
                    'alt' => $caption,
                    'source' => 'own',
                ]);
            }
        }

        // Check and create thumbnail if not exists
        $oldThumbnail = $gallery->images()->where('image_url', 'like', '%thumbnail_%');
        $existThumbnails = $gallery->images()->where('tag', 'like', '%thumbnail%')->whereNotIn('id', $oldThumbnail->pluck('id'))->exists();
        $oldThumbnail->delete();

        if (! $existThumbnails and $directory) {
            $largestImage = collect($links)->sortByDesc(fn ($link, $size) => (int) str_replace('px', '', $size))->first();
            if ($largestImage) {
                $minSizeThumbnail = array_flip($this->categoriesBySize)['thumbnail'] ?? 350;

                $existingThumbnail = Image::where('image_url', 'like', '%thumbnail_'.basename($largestImage['href']))->first();

                if (! $existingThumbnail) {
                    $p = $thumbnailPath = $this->createThumbnail($largestImage['href'], $directory, $minSizeThumbnail);
                    if (! $p) {
                        return;
                    }
                    $gallery->images()->create([
                        'image_url' => $thumbnailPath,
                        'tag' => $minSizeThumbnail.'px;thumbnail',
                        'weight' => $w,
                        'section_id' => $imageSection->id,
                        'alt' => $caption,
                        'source' => 'own',
                    ]);
                } else {
                    $gallery->images()->attach($existingThumbnail->id);
                }
            }
        }
    }

    private function createThumbnail(string $imageUrl, string $directory, int $minSizeThumbnail): string
    {
        $disk = config('filament.default_filesystem_disk', 'public');
        try {
            $contents = file_get_contents($imageUrl, false, stream_context_create(['http' => ['timeout' => 5]]));
        } catch (\Exception $e) {
            logger('Failed to fetch image for thumbnail creation: '.$e->getMessage());

            return '';
        }
        $image = imagecreatefromstring($contents);

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        $minOriginalSide = min($originalWidth, $originalHeight);
        $scale = $minSizeThumbnail / $minOriginalSide;
        $thumbnailWidth = (int) ($originalWidth * $scale);
        $thumbnailHeight = (int) ($originalHeight * $scale);

        $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

        imagecopyresampled(
            $thumbnail,
            $image,
            0,
            0,
            0,
            0,
            $thumbnailWidth,
            $thumbnailHeight,
            $originalWidth,
            $originalHeight
        );

        $thumbnailPath = $directory.'/thumbnail_'.basename($imageUrl);
        ob_start();
        imagejpeg($thumbnail);
        $thumbnailContents = ob_get_clean();
        Storage::disk($disk)->put($thumbnailPath, $thumbnailContents);

        imagedestroy($image);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }

    public function createThumbnailFromStorage(string $imagePath, int $minSizeThumbnail): string
    {
        $disk = config('filament.default_filesystem_disk', 'public');

        if (! Storage::disk($disk)->exists($imagePath)) {
            logger('Image not found in storage: '.$imagePath);

            return '';
        }

        try {
            $contents = Storage::disk($disk)->get($imagePath);
        } catch (\Exception $e) {
            logger('Failed to fetch image from storage: '.$e->getMessage());

            return '';
        }

        $image = imagecreatefromstring($contents);

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        $minOriginalSide = min($originalWidth, $originalHeight);
        $scale = $minSizeThumbnail / $minOriginalSide;
        $thumbnailWidth = (int) ($originalWidth * $scale);
        $thumbnailHeight = (int) ($originalHeight * $scale);

        $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

        imagecopyresampled(
            $thumbnail,
            $image,
            0,
            0,
            0,
            0,
            $thumbnailWidth,
            $thumbnailHeight,
            $originalWidth,
            $originalHeight
        );

        $thumbnailPath = pathinfo($imagePath, PATHINFO_DIRNAME).'/thumbnail_'.pathinfo($imagePath, PATHINFO_BASENAME);
        ob_start();
        imagejpeg($thumbnail);
        $thumbnailContents = ob_get_clean();
        Storage::disk($disk)->put($thumbnailPath, $thumbnailContents);

        imagedestroy($image);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }
}
