<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use App\Models\Enums\ProductPromotionWebsiteVisibilityEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ProductPromotion;

class LstepImportPromotions extends Command
{
    protected $signature = 'move-db:promotions';

    protected $description = 'Import Promotions from donor database';

    public function handle()
    {
        $this->warn('-> L step Import Promotions');

        $donorPromotions = DB::connection('donor')->select('
            select hotel_id, hotel_picture_id, code, name, description, booking_window_from, booking_window_to,
                   travel_window_from, travel_window_to, black_out_dates_policy, terms_and_conditions, featured, website_visibility
            from hotel_promotions
        ');

        $promotionPictureMap = [];

        $this->newLine();

        $this->withProgressBar($donorPromotions, function ($donorPromotion) use (&$promotionPictureMap) {
            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($donorPromotion) {
                $query->where('crm_hotel_id', $donorPromotion->hotel_id);
            })->first();

            if (!$hotel || !$hotel->product) {
                return;
            }

            $promotion = ProductPromotion::updateOrCreate(
                [
                    'product_id' => $hotel->product->id,
                    'rate_code' => $donorPromotion->code,
                ],
                [
                    'promotion_name' => $donorPromotion->name,
                    'description' => $donorPromotion->description,
                    'booking_start' => ($donorPromotion->booking_window_from && $donorPromotion->booking_window_from !== '0000-00-00') ? $donorPromotion->booking_window_from : now(),
                    'booking_end' => ($donorPromotion->booking_window_to && $donorPromotion->booking_window_to !== '0000-00-00') ? $donorPromotion->booking_window_to : now()->addYear(),
                    'validity_start' => ($donorPromotion->travel_window_from && $donorPromotion->travel_window_from !== '0000-00-00') ? $donorPromotion->travel_window_from : now(),
                    'validity_end' => ($donorPromotion->travel_window_to && $donorPromotion->travel_window_to !== '0000-00-00') ? $donorPromotion->travel_window_to : now()->addYear(),
                    'terms_conditions' => $donorPromotion->terms_and_conditions,
                    'exclusions' => $donorPromotion->black_out_dates_policy,
                    'package' => $donorPromotion->featured,
                    'website_visibility' => match ($donorPromotion->website_visibility) {
                        1 => ProductPromotionWebsiteVisibilityEnum::VISIBLE_ALL->value,
                        2 => ProductPromotionWebsiteVisibilityEnum::VISIBLE_UJV->value,
                        3 => ProductPromotionWebsiteVisibilityEnum::VISIBLE_LUXURIA->value,
                        default => ProductPromotionWebsiteVisibilityEnum::NO_VISIBLE->value,
                    },                ]
            );

            $imageUrl = DB::connection('donor')->table('hotel_pictures')
                ->where('id', $donorPromotion->hotel_picture_id)
                ->pluck('path')
                ->first();

            if ($imageUrl) {
                $image = Image::where('image_url', $imageUrl)->first();

                if ($image && $image->galleries()->exists()) {
                    $firstGallery = $image->galleries()->first();

                    $promotion->galleries()->attach($firstGallery->id);
                    $this->output->write("\033[1A\r\033[KAttached gallery ID {$firstGallery->id} to promotion for hotel ID {$promotion->id}.\n");
                }
            }

            $this->output->write("\033[1A\r\033[KPromotion imported: {$donorPromotion->code} | {$donorPromotion->name}\n");
        });


        $this->info("\nPromotions imported and galleries attached successfully.");
    }
}
