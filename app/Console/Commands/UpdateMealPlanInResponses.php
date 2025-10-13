<?php

namespace App\Console\Commands;

use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\HotelContentRepository\Models\Hotel;

class UpdateMealPlanInResponses extends Command
{
    protected $signature = 'responses:update-meal-plan';

    protected $description = 'Ensure meal_plan key exists in all client_response_path JSON files.';

    public function handle()
    {
        $latestRetrieves = ApiBookingInspectorRepository::getAllLatestRetrieves(); // You may need to implement this method
        $updated = 0;
        $skipped = 0;

        foreach ($latestRetrieves as $latestRetrieve) {
            $path = $latestRetrieve->client_response_path;
            if (empty($path)) {
                $this->warn("Empty client_response_path for retrieve ID: {$latestRetrieve->id}");

                continue;
            }
            if (! Storage::exists($path)) {
                $this->warn("File not found: $path");

                continue;
            }
            $json = Storage::get($path);
            $data = json_decode($json, true);
            if (! is_array($data)) {
                $this->warn("Invalid JSON in: $path");

                continue;
            }

            $hotel = Hotel::whereHas('product', function ($query) use ($data) {
                $query->where('name', Arr::get($data, 'hotel_name', ''));
            })->first();

            if (! $hotel || ! Arr::get($data, 'hotel_name')) {
                $this->warn("Hotel not found in $path");

                continue;
            }

            if (! array_key_exists('meal_plans', $data)) {
                $data['meal_plans'] = $hotel?->hotel_board_basis ?? [];
                Storage::put($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                $updated++;
                $this->info("Updated: $path");
            } else {
                $skipped++;
            }
        }
        $this->info("Done. Updated: $updated, Skipped: $skipped");
    }
}
