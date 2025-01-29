<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Enums\ProductFeeTaxApplyTypeEnum;
use Modules\HotelContentRepository\Models\Factories\ProductFeeTaxFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductFeeTax extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return ProductFeeTaxFactory::new();
    }

    protected $table = 'pd_product_fees_and_taxes';

    protected $fillable = [
        'name',
        'product_id',
        'rate_id',
        'net_value',
        'rack_value',
        'type',
        'value_type',
        'commissionable',
        'collected_by',
        'fee_category',
        'apply_type',
    ];

    protected $casts = [
        'net_value' => 'float',
        'rack_value' => 'float',
        'commissionable' => 'boolean',
        'apply_type' => ProductFeeTaxApplyTypeEnum::class,
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'product_id', 'net_value', 'rack_value', 'type', 'value_type', 'commissionable', 'collected_by', 'fee_category'])
            ->logOnlyDirty()
            ->useLogName('product_fee_tax');
    }
}
