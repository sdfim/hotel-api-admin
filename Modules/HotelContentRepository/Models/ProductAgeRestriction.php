<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductAgeRestrictionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ProductAgeRestriction extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductAgeRestrictionFactory::new();
    }

    protected $table = 'pd_product_age_restrictions';

    protected $fillable = [
        'product_id',
        'restriction_type',
        'value',
        'active',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
