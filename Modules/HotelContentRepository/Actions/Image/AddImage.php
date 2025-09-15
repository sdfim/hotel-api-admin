<?php

namespace Modules\HotelContentRepository\Actions\Image;

use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\Product;

class AddImage
{
    public function createImage(array $data): Image
    {
        return Image::create($data);
    }

    public function addImageToGallery(array $data, ?Product $product, ?HotelRoom $room, string $galleryName, string $description, array $galleries = []): void
    {
        DB::transaction(function () use ($data, $room, $product, $galleryName, $description, $galleries) {
            $image = Image::create([
                'image_url' => $data['image_url'],
                'tag' => $data['tag'],
                'alt' => $data['alt'],
                'section_id' => $data['section_id'],
                'weight' => $data['weight'] ?? '500',
            ]);

            $galleryModels = ImageGallery::whereIn('id', $galleries)->get();
            foreach ($galleryModels as $gallery) {
                $gallery->images()->attach($image->id);
            }

            if ($galleryName) {
                $gallery = ImageGallery::firstOrCreate(
                    ['gallery_name' => $galleryName],
                    ['description' => $description]
                );
                $gallery->images()->attach($image->id);

                if ($product) {
                    $product->galleries()->syncWithoutDetaching([$gallery->id]);
                }

                if ($room) {
                    $room->galleries()->syncWithoutDetaching([$gallery->id]);
                }
            }
        });
    }
}
