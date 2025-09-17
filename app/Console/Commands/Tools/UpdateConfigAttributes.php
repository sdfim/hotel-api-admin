<?php

namespace App\Console\Commands\Tools;

use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigAttributeCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UpdateConfigAttributes extends Command
{
    protected $signature = 'config-attributes:update {--path=}';

    protected $description = 'Update ConfigAttribute and ConfigAttributeCategory from CSV';

    public function handle()
    {

        $path = $this->option('path');

        if ($path) {
            $disk = config('filament.default_filesystem_disk', 'public');
            $csv = Storage::disk($disk)->get($path);
        } else {
            $csv = file_get_contents(__DIR__.'/config_attributes.csv');
        }

        $lines = explode(PHP_EOL, $csv);
        $header = str_getcsv(array_shift($lines));

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $row = str_getcsv($line);
            [$name, $categoryName] = $row;

            $attribute = ConfigAttribute::firstOrCreate([
                'name' => $name,
                'default_value' => '',
            ]);
            $category = ConfigAttributeCategory::firstOrCreate(['name' => $categoryName]);

            $attribute->categories()->syncWithoutDetaching([$category->id]);
        }

        $this->info('Config attributes updated.');

        return 0;
    }
}
