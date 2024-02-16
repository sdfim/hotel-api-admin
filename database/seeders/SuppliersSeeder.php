<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expediaSupplier = Supplier::firstOrNew([
            'name' => 'Expedia',
            'description' => 'Expedia Supplier']);

        $expediaSupplier->save();

        $hbsiSupplier = Supplier::firstOrNew([
            'name' => 'HBSI',
            'description' => 'HBSI Supplier']);

        $hbsiSupplier->save();
    }
}
