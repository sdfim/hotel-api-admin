<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductFeeTaxFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ProductFeeTax extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductFeeTaxFactory::new();
    }

    protected $table = 'pd_product_fees_and_taxes';

    protected $fillable = [
        'name',
        'product_id',
        'net_value',
        'rack_value',
        'type',
        'value_type',
        'commissionable',
        'collected_by',
        'fee_category',
    ];

    protected $casts = [
        'net_value' => 'float',
        'rack_value' => 'float',
        'commissionable' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
