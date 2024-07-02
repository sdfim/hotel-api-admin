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
        'description',
    ];

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    public static function getExpediaId(): ?int
    {
        return Supplier::where('name', 'Expedia')->first()?->id;
    }

    public static function getSupplierId(string $supplierName): ?int
    {
        return Supplier::where('name', $supplierName)->first()?->id;
    }
}
