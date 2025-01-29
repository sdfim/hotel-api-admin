<?php

namespace App\Console\Commands\MappingServices;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\ExpediaContent;

class CleanerExpediaMappings extends Command
{
    protected $signature = 'cleaner:expedia-mappings';
    protected $description = 'Process Expedia mappings and remove duplicates';

    public function handle()
    {
        // Step 1: Retrieve giata_ids with supplier_id_count > 1
        $giataIds = DB::table('mappings')
            ->select('giata_id')
            ->where('supplier', 'Expedia')
            ->groupBy('giata_id')
            ->havingRaw('COUNT(DISTINCT supplier_id) > 1')
            ->pluck('giata_id');

        foreach ($giataIds as $giataId) {
            $expediaIds = DB::table('mappings')
                ->select('supplier_id')
                ->where('giata_id', $giataId)
                ->where('supplier', 'Expedia')
                ->pluck('supplier_id');

            $property = Property::where('code', $giataId)->first(['code', 'name', 'address', 'latitude', 'longitude']);
            $expedias = ExpediaContent::whereIn('property_id', $expediaIds)->get(['property_id', 'name', 'address', 'latitude', 'longitude']);

            $standard = $property?->name . ' '
                . Arr::get($property?->address, 'AddressLine', '') . ' '
                . round($property?->latitude, 4) . ' '
                . round($property?->longitude, 4);

            $this->line('standard: ' . $standard);

            $bestExpedia = null;
            $highestSimilarity = 0;

            foreach ($expedias as $expedia) {
                $comparison = $expedia->name . ' '
                    . Arr::get($expedia->address, 'line_1', '') . ' '
                    . round($expedia->latitude, 4) . ' '
                    . round($expedia->longitude, 4);

                similar_text($standard, $comparison, $percent);
                $expedia->similarity = $percent;

                $this->info('comparison: ' . $comparison . ' similarity: ' . $percent);

                if ($percent > $highestSimilarity) {
                    $highestSimilarity = $percent;
                    $bestExpedia = $expedia;
                }
            }

            $this->line('bestExpedia: ' . $bestExpedia->name . ' similarity: ' . $bestExpedia->similarity);

            foreach ($expedias as $expedia) {
                if ($expedia->property_id !== $bestExpedia->property_id) {
                    DB::table('mappings')
                        ->where('supplier_id', $expedia->property_id)
                        ->where('giata_id', $giataId)
                        ->where('supplier', 'Expedia')
                        ->delete();

                    $this->alert('deleted: ' . $expedia->name);
                }
            }
        }

        $this->info('Expedia mappings processed successfully.');
    }
}
