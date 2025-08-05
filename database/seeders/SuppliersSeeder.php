<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Modules\Enums\SupplierNameEnum;
use Modules\Enums\TypeRequestEnum;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //        Supplier::updateOrCreate(
        //            ['name' => SupplierNameEnum::EXPEDIA->value],
        //            [
        //                'product_type' => [TypeRequestEnum::HOTEL->value],
        //                'description' => 'Expedia Supplier',
        //            ]
        //        );

        Supplier::updateOrCreate(
            ['name' => SupplierNameEnum::HBSI->value],
            [
                'product_type' => [TypeRequestEnum::HOTEL->value],
                'description' => 'HBSI Supplier',
            ]
        );

        //        Supplier::updateOrCreate(
        //            ['name' => SupplierNameEnum::HILTON->value],
        //            [
        //                'product_type' => [TypeRequestEnum::HOTEL->value],
        //                'description' => 'Hilton Supplier',
        //            ]
        //        );

        Supplier::updateOrCreate(
            ['name' => SupplierNameEnum::GIATA->value],
            [
                'product_type' => [TypeRequestEnum::HOTEL->value],
                'description' => 'Giata Supplier',
            ]
        );

        Supplier::updateOrCreate(
            ['name' => SupplierNameEnum::ICE_PORTAL->value],
            [
                'product_type' => [TypeRequestEnum::HOTEL->value],
                'description' => 'IcePortal Supplier',
            ]
        );
    }
}
