<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'description'
    ];

    /**
     * @return HasMany
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * @return int|string|null
     */
    public static function getExpediaId(): int|string|null
    {
        return Supplier::where('name', 'Expedia')->first()->id;
    }

	 /**
     * @return int|string|null
     */
    public static function getSupplierId(string $supplierName): int|string|null
    {
        return Supplier::where('name', $supplierName)->first()->id;
    }

}
