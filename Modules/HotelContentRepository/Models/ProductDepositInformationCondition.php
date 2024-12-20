<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductDepositInformationConditionFactory;

class ProductDepositInformationCondition extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ProductDepositInformationConditionFactory::new();
    }

    protected $table = 'pd_product_deposit_information_conditions';

    protected $fillable = [
        'product_deposit_information_id',
        'field',
        'compare',
        'value',
        'value_from',
        'value_to',
    ];

    public function productDepositInformation(): BelongsTo
    {
        return $this->belongsTo(ProductDepositInformation::class, 'product_deposit_information_id', 'id');
    }
}
