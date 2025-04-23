<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductDepositInformationConditionFactory;

class ProductCancellationPolicyCondition extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ProductDepositInformationConditionFactory::new();
    }

    protected $table = 'pd_product_cancellation_policy_conditions';

    protected $fillable = [
        'product_cancellation_policy_id',
        'field',
        'compare',
        'value',
        'value_from',
        'value_to',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public function productCancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(ProductCancellationPolicy::class, 'product_cancellation_policy_id', 'id');
    }
}
