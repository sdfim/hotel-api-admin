<?php

namespace Modules\HotelContentRepository\Actions\Gallery;

use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\Product;

class AddGallery
{
    public function execute(array $data, ImageGallery $gallery, array &$imageIds): void
    {
        $image = Image::create([
            'image_url' => $data['image_url'],
            'tag' => $data['tag'],
            'alt' => $data['alt'],
            'section_id' => $data['section_id'],
            'weight' => $data['weight'] ?? '500px',
        ]);

        if ($gallery->exists) {
            $gallery->images()->attach($image->id);
        } else {
            $imageIds[] = $image->id;
        }
    }

    public function attachImages(array $data, ImageGallery $gallery, array &$imageIds): void
    {
        if ($gallery->exists) {
            $gallery->images()->attach($data['image_ids']);
        } else {
            $imageIds = array_merge($imageIds, $data['image_ids']);
        }
    }

    public function addImageToGallery(array $data, ?Product $product, string $galleryName, string $description): void
    {
        DB::transaction(function () use ($data, $product, $galleryName, $description) {
            $image = Image::create([
                'image_url' => $data['image_url'],
                'tag' => $data['tag'],
                'alt' => $data['alt'],
                'section_id' => $data['section_id'],
                'weight' => $data['weight'] ?? '500px',
            ]);

            if ($product) {
                $gallery = ImageGallery::firstOrCreate(
                    ['gallery_name' => $galleryName],
                    ['description' => $description]
                );
                $gallery->images()->attach($image->id);
                $product->galleries()->syncWithoutDetaching([$gallery->id]);
            }
        });
    }
}
