<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\Enums\ProductFeeTaxTypeEnum;
use Modules\Enums\ProductFeeTaxValueTypeEnum;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductFeeTax;

class ProductFeeTaxFactory extends Factory
{
    protected $model = ProductFeeTax::class;

    public function definition(): array
    {
        $supplier = Supplier::firstOrCreate(
            ['name' => SupplierNameEnum::HBSI->value],
            ['description' => 'HBSI Supplier']
        );

        return [
            'product_id' => Product::factory(),
            'name' => $this->faker->word(),
            'net_value' => $this->faker->randomFloat(2, 10, 1000),
            'rack_value' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement([
                ProductFeeTaxTypeEnum::TAX->value,
                ProductFeeTaxTypeEnum::FEE->value,
            ]),
            'value_type' => $this->faker->randomElement([
                ProductFeeTaxValueTypeEnum::PERCENTAGE->value,
                ProductFeeTaxValueTypeEnum::AMOUNT->value,
            ]),
            'collected_by' => $this->faker->randomElement([
                FeeTaxCollectedByEnum::DIRECT->value,
                FeeTaxCollectedByEnum::VENDOR->value,
            ]),
            'commissionable' => $this->faker->boolean(),
            'fee_category' => $this->faker->randomElement(['optional', 'mandatory']),
            'apply_type' => $this->faker->randomElement([
                ProductApplyTypeEnum::PER_NIGHT->value,
                ProductApplyTypeEnum::PER_PERSON->value,
            ]),
            'supplier_id' => $supplier->id,
            'action_type' => 'create',
            'old_name' => $this->faker->word(),
        ];
    }
}
